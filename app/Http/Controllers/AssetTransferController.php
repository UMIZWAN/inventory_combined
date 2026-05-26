<?php

namespace App\Http\Controllers;

use App\Models\AssetTransfer;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\TransferList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetTransferController extends Controller
{
    /**
     * List asset transfers
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Get IDs of branches the user belongs to
        $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

        // Only get transfers where user's branch is fromBranch or toBranch
        $query = AssetTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'assets'
        ])
            ->where(function ($q) use ($userBranchIds) {
                $q->whereIn('transfer_from', $userBranchIds)
                    ->orWhereIn('transfer_to', $userBranchIds);
            });

        if ($request->filled('status')) {
            $query->where('transfer_status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('from_branch')) {
            $query->where('transfer_from', $request->from_branch);
        }
        if ($request->filled('to_branch')) {
            $query->where('transfer_to', $request->to_branch);
        }

        $transfers = $query->latest()->paginate(15);

        $branches = Branch::where('is_active', true)->get();

        // Notification counts per action type
        $pendingApprovalCount = 0;
        if ($user->accessLevel?->approve_disaprove_transfer) {
            $pendingApprovalCount = AssetTransfer::where('transfer_status', 'pending')
                ->where(function ($q) use ($userBranchIds) {
                    $q->whereIn('transfer_from', $userBranchIds)
                        ->orWhereIn('transfer_to', $userBranchIds);
                })
                ->count();
        }

        $toSendCount = AssetTransfer::where('transfer_status', 'approved')
            ->whereIn('transfer_from', $userBranchIds)
            ->count();

        $toReceiveCount = AssetTransfer::whereIn('transfer_status', ['in_transit', 'partial_received'])
            ->whereIn('transfer_to', $userBranchIds)
            ->count();

        $totalActionCount = $pendingApprovalCount + $toSendCount + $toReceiveCount;

        return view('asset_transfer.index', compact(
            'transfers',
            'branches',
            'pendingApprovalCount',
            'toSendCount',
            'toReceiveCount',
            'totalActionCount'
        ));
    }


    /**
     * Show transfer details (for modal)
     */
    public function show($id)
    {
        $transfer = AssetTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'assets' => function ($query) {
                $query->withPivot('asset_transfer_status', 'received_by', 'returned_by');
            }
        ])->findOrFail($id);

        $shippingOptions = \App\Models\ShippingOption::where('is_active', true)->get();

        // Get users from destination branch for full receive
        $toBranchUsers = \App\Models\User::where('is_active', true)
            ->whereHas('branches', function ($q) use ($transfer) {
                $q->where('branches.id', $transfer->transfer_to);
            })->get();
        $departments = \App\Models\Department::where('is_active', true)->get();

        return view('asset_transfer.show', compact('transfer', 'shippingOptions', 'toBranchUsers', 'departments'));
    }


    /**
     * Store new transfer
     */
    public function store(Request $request)
    {
        $request->validate([
            'transfer_purpose' => 'required|string',
            'transfer_from' => 'required|exists:branches,id',
            'transfer_to' => 'required|exists:branches,id|different:transfer_from',
            'shipping_id' => 'nullable|exists:shipping_options,id',
            'assets' => 'required|array|min:1',
            'assets.*.asset_id' => 'nullable|exists:inventory_assets,id',
            'assets.*.asset_no' => 'required|string',
            'assets.*.asset_name' => 'required|string',
            'assets.*.asset_cost' => 'required|numeric|min:0',
            'transfer_log' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {

            // Calculate total cost from assets
            $totalCost = collect($request->assets)->sum(function ($asset) {
                return floatval($asset['asset_cost'] ?? 0);
            });

            $transfer = AssetTransfer::create([
                'transfer_running_no' => $this->generateRunningNo(),
                'transfer_purpose' => $request->transfer_purpose,
                'transfer_status' => 'pending',
                'transfer_cost' => $totalCost,
                'transfer_from' => $request->transfer_from,
                'transfer_to' => $request->transfer_to,
                'shipping_id' => $request->shipping_id,
                'transfer_attachment' => null,
                'created_by' => Auth::id(),
                'transfer_remark' => $request->transfer_log,
                'transfer_log' => 'Transfer created by '
                    . (Auth::user()->name ?? 'System')
                    . ' at ' . now()->format('Y-m-d H:i:s'),
            ]);

            // Create transfer list entries for each asset
            foreach ($request->assets as $assetData) {
                TransferList::create([
                    'transfer_id' => $transfer->id,
                    'asset_id' => $assetData['asset_id'] ?? null,
                    'asset_transfer_status' => 'pending',
                ]);

                if (!empty($assetData['asset_id'])) {
                    $asset = Asset::find($assetData['asset_id']);
                    if ($asset) {
                        $asset->update([
                            'asset_log' => $this->appendLog(
                                $asset->asset_log,
                                'Added to transfer ' . $transfer->transfer_running_no . ' by ' . (Auth::user()->name ?? 'Unknown')
                            ),
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Asset transfer created successfully');
    }

    /**
     * Generate running number
     */
    private function generateRunningNo()
    {
        $date = now()->format('Ymd');
        $count = AssetTransfer::whereDate('created_at', now()->toDateString())->count() + 1;

        return 'TRF-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'transfer_status' => 'required|string',
            'asset_id' => 'nullable|exists:inventory_assets,id',
            'remark' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {

            $transfer = AssetTransfer::with('assets')->findOrFail($id);
            $status = $request->transfer_status;

            switch ($status) {

                /* ================= APPROVE ================= */
                case 'approved':

                    if ($transfer->transfer_status !== 'pending') {
                        abort(400, 'Only pending transfer can be approved');
                    }

                    $transfer->update([
                        'transfer_status' => 'approved',
                        'approved_by' => Auth::id(),
                        'transfer_log' => $this->appendLog(
                            $transfer->transfer_log,
                            'Approved by ' . Auth::user()->name
                        ),
                    ]);
                    break;

                /* ================= SEND ================= */
                case 'in_transit':

                    if ($transfer->transfer_status !== 'approved') {
                        abort(400, 'Only approved transfer can be sent');
                    }

                    // Check if user has access to FROM branch
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

                    if (!in_array($transfer->transfer_from, $userBranchIds)) {
                        abort(403, 'You do not have access to the FROM branch to send this transfer');
                    }

                    // Validate shipping option is provided
                    $request->validate([
                        'shipping_id' => 'required|exists:shipping_options,id',
                    ]);

                    // Get all assets in the transfer (pending or approved)
                    $transferLists = TransferList::where('transfer_id', $transfer->id)
                        ->whereIn('asset_transfer_status', ['pending', 'approved'])
                        ->get();

                    // Update transfer list items to in_transit
                    foreach ($transferLists as $list) {
                        $list->update(['asset_transfer_status' => 'in_transit']);

                        // Mark assets as in_transit
                        if ($list->asset_id) {
                            $asset = Asset::find($list->asset_id);
                            if ($asset) {
                                $asset->update([
                                    'in_transit' => true,
                                    'asset_log' => $this->appendLog(
                                        $asset->asset_log,
                                        'Sent in transit for transfer ' . $transfer->transfer_running_no . ' by ' . (Auth::user()->name ?? 'Unknown')
                                    ),
                                ]);
                            }
                        }
                    }

                    $transfer->update([
                        'transfer_status' => 'in_transit',
                        'shipping_id' => $request->shipping_id,
                        'transfer_log' => $this->appendLog(
                            $transfer->transfer_log,
                            'Sent in transit by ' . Auth::user()->name
                        ),
                    ]);
                    break;

                /* ================= REJECT ================= */
                case 'rejected':

                    if ($transfer->transfer_status !== 'pending') {
                        abort(400, 'Only pending transfer can be rejected');
                    }

                    $transfer->update([
                        'transfer_status' => 'rejected',
                        'rejected_by' => Auth::id(),
                        'transfer_log' => $this->appendLog(
                            $transfer->transfer_log,
                            'Rejected by ' . Auth::user()->name .
                                ' | Remark: ' . $request->remark
                        ),
                    ]);

                    $rejectedLists = TransferList::where('transfer_id', $transfer->id)->get();
                    foreach ($rejectedLists as $list) {
                        $list->update(['asset_transfer_status' => 'rejected']);
                        if ($list->asset_id) {
                            $asset = Asset::find($list->asset_id);
                            if ($asset) {
                                $asset->update([
                                    'asset_log' => $this->appendLog(
                                        $asset->asset_log,
                                        'Transfer ' . $transfer->transfer_running_no . ' rejected by ' . (Auth::user()->name ?? 'Unknown')
                                    ),
                                ]);
                            }
                        }
                    }
                    break;

                /* ================= RECEIVE ASSET ================= */
                case 'received':

                    // Check if user has access to TO branch
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

                    if (!in_array($transfer->transfer_to, $userBranchIds)) {
                        abort(403, 'You do not have access to the TO branch to receive this transfer');
                    }

                    $assetId = $request->asset_id;

                    $line = TransferList::where('transfer_id', $transfer->id)
                        ->where('asset_id', $assetId)
                        ->first();

                    if (!$line || !in_array($line->asset_transfer_status, ['in_transit', 'approved'])) {
                        abort(400, 'Asset not available for receiving');
                    }

                    // Move asset to destination branch and mark as not in transit
                    $assetUpdate = [
                        'branch_id' => $transfer->transfer_to,
                        'in_transit' => false,
                    ];

                    if ($request->receive_mode === 'full') {
                        $assetUpdate['user_id'] = $request->user_id;
                        $assetUpdate['department_id'] = $request->department_id;
                    } else {
                        // Half receive: clear user and department
                        $assetUpdate['user_id'] = null;
                        $assetUpdate['department_id'] = null;
                    }

                    $receivedAsset = Asset::find($assetId);
                    if ($receivedAsset) {
                        $assetUpdate['asset_log'] = $this->appendLog(
                            $receivedAsset->asset_log,
                            'Received at ' . ($transfer->toBranch->name ?? 'branch') . ' via transfer ' . $transfer->transfer_running_no . ' by ' . (Auth::user()->name ?? 'Unknown')
                        );
                        $receivedAsset->update($assetUpdate);
                    } else {
                        Asset::where('id', $assetId)->update($assetUpdate);
                    }

                    $line->update([
                        'asset_transfer_status' => 'received',
                        'received_by' => Auth::id(),
                    ]);

                    $this->refreshHeaderStatus($transfer->id);
                    break;

                /* ================= RETURN ASSET ================= */
                case 'returned':

                    $assetId = $request->asset_id;

                    TransferList::where('transfer_id', $transfer->id)
                        ->where('asset_id', $assetId)
                        ->update([
                            'asset_transfer_status' => 'returned',
                            'returned_by' => Auth::id(),
                        ]);

                    $returnedAsset = Asset::find($assetId);
                    if ($returnedAsset) {
                        $returnedAsset->update([
                            'asset_log' => $this->appendLog(
                                $returnedAsset->asset_log,
                                'Returned via transfer ' . $transfer->transfer_running_no . ' by ' . (Auth::user()->name ?? 'Unknown')
                            ),
                        ]);
                    }

                    $this->refreshHeaderStatus($transfer->id);
                    break;

                default:
                    abort(400, 'Invalid transfer status');
            }
        });

        return back()->with('success', 'Transfer status updated');
    }

    private function refreshHeaderStatus($transferId)
    {
        $statuses = TransferList::where('transfer_id', $transferId)
            ->pluck('asset_transfer_status');

        // Filter out rejected items - they don't count for completion
        $activeStatuses = $statuses->filter(fn($s) => !in_array($s, ['rejected']));

        // All active items are received or returned = completed
        if ($activeStatuses->isNotEmpty() && $activeStatuses->every(fn($s) => in_array($s, ['received', 'returned']))) {
            AssetTransfer::where('id', $transferId)->update([
                'transfer_status' => 'completed',
                'received_by' => Auth::id(),
            ]);
            return;
        }

        // Some items received but some still in transit = partial_received
        if ($activeStatuses->contains('received') && $activeStatuses->contains('in_transit')) {
            AssetTransfer::where('id', $transferId)->update([
                'transfer_status' => 'partial_received',
            ]);
            return;
        }

        // Still has items in transit
        if ($activeStatuses->contains('in_transit')) {
            AssetTransfer::where('id', $transferId)->update([
                'transfer_status' => 'in_transit',
            ]);
            return;
        }
    }

    private function appendLog($oldLog, $message)
    {
        return trim(
            ($oldLog ? $oldLog . PHP_EOL : '') .
                $message . ' at ' . now()->format('Y-m-d H:i:s')
        );
    }

    /**
     * Download transfer as PDF
     */
    public function downloadPdf($id)
    {
        $transfer = AssetTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'approver',
            'rejector',
            'shipping',
            'assets' => function ($query) {
                $query->with('group')->withPivot('asset_transfer_status', 'received_by', 'returned_by');
            }
        ])->findOrFail($id);

        $pdf = Pdf::loadView('asset_transfer.pdf', compact('transfer'));

        return $pdf->download('TransferNote_' . $transfer->transfer_running_no . '.pdf');
    }

    /**
     * Approve selected items (unselected items are rejected)
     */
    public function approveItems(Request $request, $id)
    {
        $request->validate([
            'approved_asset_ids' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $transfer = AssetTransfer::with('assets')->findOrFail($id);

            if ($transfer->transfer_status !== 'pending') {
                abort(400, 'Only pending transfer can be approved');
            }

            $approvedIds = array_filter(explode(',', $request->approved_asset_ids));

            // Update approved items
            TransferList::where('transfer_id', $transfer->id)
                ->whereIn('asset_id', $approvedIds)
                ->update(['asset_transfer_status' => 'approved']);

            // Update rejected items (not in approved list) and log to each asset
            $itemsToReject = TransferList::where('transfer_id', $transfer->id)
                ->whereNotIn('asset_id', $approvedIds)
                ->get();
            foreach ($itemsToReject as $rejectedItem) {
                $rejectedItem->update(['asset_transfer_status' => 'rejected']);
                if ($rejectedItem->asset_id) {
                    $asset = Asset::find($rejectedItem->asset_id);
                    if ($asset) {
                        $asset->update([
                            'asset_log' => $this->appendLog(
                                $asset->asset_log,
                                'Excluded from transfer ' . $transfer->transfer_running_no . ' by ' . (Auth::user()->name ?? 'Unknown')
                            ),
                        ]);
                    }
                }
            }

            // Check if any items are approved
            $approvedCount = TransferList::where('transfer_id', $transfer->id)
                ->where('asset_transfer_status', 'approved')
                ->count();

            if ($approvedCount > 0) {
                $transfer->update([
                    'transfer_status' => 'approved',
                    'approved_by' => Auth::id(),
                    'transfer_log' => $this->appendLog(
                        $transfer->transfer_log,
                        'Approved ' . $approvedCount . ' item(s) by ' . Auth::user()->name
                    ),
                ]);
            } else {
                // All items rejected
                $transfer->update([
                    'transfer_status' => 'rejected',
                    'rejected_by' => Auth::id(),
                    'transfer_log' => $this->appendLog(
                        $transfer->transfer_log,
                        'All items rejected by ' . Auth::user()->name
                    ),
                ]);
            }
        });

        return back()->with('success', 'Transfer items processed successfully');
    }

    /**
     * Receive selected items (per-item user, department, photo)
     */
    public function receiveSelected(Request $request, $id)
    {
        $request->validate([
            'asset_ids' => 'required|string',
        ]);

        $assetIds = array_filter(explode(',', $request->asset_ids));

        // Validate that every selected asset has user, department, and photo
        $errors = [];
        foreach ($assetIds as $assetId) {
            $item = $request->input("items.{$assetId}", []);
            if (empty($item['user_id']))       $errors[] = "Asset #{$assetId}: User is required.";
            if (empty($item['department_id'])) $errors[] = "Asset #{$assetId}: Department is required.";
            if (!$request->hasFile("items.{$assetId}.photo")) $errors[] = "Asset #{$assetId}: Photo is required.";
        }
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        // Upload photos before transaction (file ops don't roll back)
        $photoPaths = [];
        foreach ($assetIds as $assetId) {
            if ($request->hasFile("items.{$assetId}.photo")) {
                $photoPaths[$assetId] = $request->file("items.{$assetId}.photo")
                    ->store('asset_images', 'public');
            }
        }

        DB::transaction(function () use ($request, $id, $assetIds, $photoPaths) {
            $transfer = AssetTransfer::findOrFail($id);

            if (!in_array($transfer->transfer_status, ['in_transit', 'partial_received'])) {
                abort(400, 'Transfer must be in transit or partial received');
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

            if (!in_array($transfer->transfer_to, $userBranchIds)) {
                abort(403, 'You do not have access to the TO branch to receive this transfer');
            }

            $receivedCount = 0;

            foreach ($assetIds as $assetId) {
                $line = TransferList::where('transfer_id', $transfer->id)
                    ->where('asset_id', $assetId)
                    ->first();

                if ($line && in_array($line->asset_transfer_status, ['in_transit', 'approved'])) {
                    $itemData = $request->input("items.{$assetId}", []);
                    $userId = $itemData['user_id'] ?? null;
                    $departmentId = $itemData['department_id'] ?? null;
                    $photoPath = $photoPaths[$assetId] ?? null;

                    $bulkAsset = Asset::find($assetId);
                    if ($bulkAsset) {
                        $updateData = [
                            'branch_id' => $transfer->transfer_to,
                            'in_transit' => false,
                            'user_id' => $userId ?: null,
                            'department_id' => $departmentId ?: null,
                            'asset_log' => $this->appendLog(
                                $bulkAsset->asset_log,
                                'Received via transfer ' . $transfer->transfer_running_no . ' by ' . (Auth::user()->name ?? 'Unknown')
                            ),
                        ];
                        if ($photoPath) {
                            $updateData['asset_image'] = $photoPath;
                        }
                        $bulkAsset->update($updateData);
                    }

                    $line->update([
                        'asset_transfer_status' => 'received',
                        'received_by' => Auth::id(),
                    ]);

                    $receivedCount++;
                }
            }

            $this->refreshHeaderStatus($transfer->id);

            $transfer->refresh();
            $transfer->update([
                'transfer_log' => $this->appendLog(
                    $transfer->transfer_log,
                    'Received ' . $receivedCount . ' item(s) by ' . Auth::user()->name
                ),
            ]);
        });

        return back()->with('success', 'Selected items received successfully');
    }
}
