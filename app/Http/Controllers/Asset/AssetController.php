<?php

namespace App\Http\Controllers\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\Branch;
use App\Models\ShippingOption;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AssetController extends Controller
{
    /**
     * Asset list page (modal-based)
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Get filter inputs
        $assetName = $request->input('asset_name');
        $assetNo = $request->input('asset_no');
        $assetBranch = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $userId = $request->input('user_id');
        $status = $request->input('status');

        // Get user's accessible branches
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

        // Build query - exclude in-transit assets and pending/rejected approval assets
        $query = Asset::where('is_deleted', false)
            ->where('in_transit', false)
            ->whereIn('branch_id', $userBranchIds)
            ->where(function ($q) {
                $q->where('approval_status', 'approved')
                    ->orWhereNull('approval_status')
                    ->orWhere('approval_status', '');
            });

        // Status filter
        if ($status === 'active') {
            $query->where('is_disposed', false);
        } elseif ($status === 'disposed') {
            $query->where('is_disposed', true);
        } elseif ($status === 'open') {
            $query->where('is_disposed', false)
                ->whereNull('user_id')
                ->whereNull('department_id');
        } else {
            // Default: show active assets
            $query->where('is_disposed', false);
        }

        // Asset name filter (partial match)
        if ($assetName) {
            $query->where('asset_name', 'like', "%{$assetName}%");
        }

        // Asset number filter (partial match)
        if ($assetNo) {
            $query->where('asset_no', 'like', "%{$assetNo}%");
        }

        // Branch filter
        if ($assetBranch) {
            $query->where('branch_id', $assetBranch);
        }

        // Department filter
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // User filter
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Fetch results with relationships for disposal workflow
        $assets = $query->with(['group', 'branch', 'disposeRequester', 'user', 'department'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('asset.masterList', [
            'assets' => $assets,
            'groups' => AssetGroup::where('is_deleted', false)->get(),
            'branches' => Branch::where('is_active', true)
                ->whereIn('id', $userBranchIds)
                ->get(),
            'allBranches' => Branch::where('is_active', true)->get(),
            'suppliers' => Supplier::where('is_deleted', false)->get(),
            'departments' => \App\Models\Department::where('is_active', true)->get(),
            'users' => User::where('is_active', true)->with('branches')->get(),
            'filters' => $request->all(),
        ]);
    }

    public function exportCsv()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

        $assets = Asset::where('is_deleted', false)
            ->whereIn('branch_id', $userBranchIds)
            ->where(function ($q) {
                $q->where('approval_status', 'approved')
                    ->orWhereNull('approval_status')
                    ->orWhere('approval_status', '');
            })
            ->with(['group', 'branch', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'asset_no',
            'old_asset_no',
            'asset_name',
            'asset_uom',
            'asset_purchase_date',
            'asset_cost',
            'group_name',
            'asset_lifespan',
            'asset_service_interval',
            'venue',
            'branch_code',
            'user_name',
            'department_name',
            'supplier_name',
            'inv_no',
            'has_warranty',
            'warranty_period',
        ];

        $callback = function () use ($assets, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($assets as $asset) {
                fputcsv($file, [
                    $asset->asset_no,
                    $asset->old_asset_no ?? '',
                    $asset->asset_name ?? '',
                    $asset->asset_uom ?? '',
                    $asset->asset_purchase_date ? Carbon::parse($asset->asset_purchase_date)->format('Y-m-d') : '',
                    $asset->asset_cost ?? '0.00',
                    $asset->group->name ?? '',
                    $asset->asset_lifespan ?? '',
                    $asset->asset_service_interval ?? '',
                    $asset->venue ?? '',
                    $asset->branch->code ?? '',
                    $asset->user->name ?? '',
                    $asset->department->name ?? '',
                    $asset->supplier->name ?? '',
                    $asset->inv_no ?? '',
                    $asset->has_warranty ? '1' : '0',
                    $asset->warranty_period ?? '',
                ]);
            }

            fclose($file);
        };

        $filename = 'asset_export_' . now()->format('Y-m-d') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Store asset (Add modal)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'asset_no' => 'required|string|max:255',
                'old_asset_no' => 'nullable|string|max:255',
                'asset_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'asset_name' => 'nullable|string|max:255',
                'asset_uom' => 'nullable|string|max:50',
                'asset_purchase_date' => 'nullable|date',
                'asset_cost' => 'nullable|numeric',
                'group_id' => 'nullable|exists:inventory_asset_groups,id',
                'asset_lifespan' => 'nullable|string|max:255',
                'asset_service_interval' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7',
                'venue' => 'nullable|string|max:255',
                'branch_id' => 'nullable|exists:branches,id',
                'user_id' => 'nullable|exists:users,id',
                'department_id' => 'nullable|exists:departments,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'inv_no' => 'nullable|string|max:255',
                'has_warranty' => 'nullable|boolean',
                'warranty_period' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        // Duplicate check
        $exists = Asset::where('asset_no', $data['asset_no'])
            ->where('is_deleted', false)
            ->exists();

        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['asset_no' => ['Asset no is already taken']]
                ], 422);
            }
            return redirect()->back()->with('error', 'Asset no is already taken');
        }

        // Handle image upload
        if ($request->hasFile('asset_image')) {
            $imagePath = $request->file('asset_image')->store('assets', 'public');
            $data['asset_image'] = $imagePath;
        }

        $data['asset_purchase_date'] = $request->input('asset_purchase_date') ?? now()->toDateString();
        $data['asset_cost'] = $request->input('asset_cost') ?? 0;
        $data['asset_lifespan'] = $request->input('asset_lifespan') ?? null;
        $data['asset_service_interval'] = $request->input('asset_service_interval') ?? null;
        $data['branch_id'] = $request->input('branch_id') ?? 1;
        $data['user_id'] = $request->input('user_id') ?? null;
        $data['department_id'] = $request->input('department_id') ?? null;
        $data['has_warranty'] = $request->input('has_warranty') ?? false;
        $data['warranty_period'] = $request->input('warranty_period') ?? null;
        $data['created_by'] = Auth::id();

        // Auto-approve all assets (approval workflow bypassed)
        $data['approval_status'] = 'approved';
        $data['approved_by'] = Auth::id();
        $data['approved_at'] = now();
        $data['asset_log'] = 'Asset created by ' . (Auth::user()->name ?? 'Unknown')
            . ' at ' . now()->format('Y-m-d H:i:s');
        $message = 'Asset added successfully';

        $asset = Asset::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'asset' => $asset,
                'pending_approval' => false
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update asset (Edit modal)
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        try {
            $data = $request->validate([
                'asset_no' => 'required|string|max:255',
                'asset_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'asset_name' => 'nullable|string|max:255',
                'asset_uom' => 'nullable|string|max:50',
                'asset_purchase_date' => 'nullable|date',
                'asset_cost' => 'nullable|numeric',
                'group_id' => 'nullable|exists:inventory_asset_groups,id',
                'asset_lifespan' => 'nullable|string|max:255',
                'asset_service_interval' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7',
                'venue' => 'nullable|string|max:255',
                'branch_id' => 'nullable|exists:branches,id',
                'user_id' => 'nullable|exists:users,id',
                'department_id' => 'nullable|exists:departments,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'inv_no' => 'nullable|string|max:255',
                'has_warranty' => 'nullable|boolean',
                'warranty_period' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $exists = Asset::where('asset_no', $data['asset_no'])
            ->where('is_disposed', false)
            ->where('is_deleted', false)
            ->where('id', '!=', $asset->id)
            ->exists();

        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['asset_no' => ['Asset no is already taken by another active asset']]
                ], 422);
            }
            return redirect()->back()->with('error', 'Asset no is already taken by another active asset');
        }

        // Handle image upload
        if ($request->hasFile('asset_image')) {
            // Delete old image if exists
            if ($asset->asset_image && Storage::disk('public')->exists($asset->asset_image)) {
                Storage::disk('public')->delete($asset->asset_image);
            }

            $imagePath = $request->file('asset_image')->store('assets', 'public');
            $data['asset_image'] = $imagePath;
        }

        $data['updated_by'] = Auth::id();
        $data['asset_log'] = trim(
            ($asset->asset_log ? $asset->asset_log . PHP_EOL : '') .
                'Asset updated by ' . (Auth::user()->name ?? 'Unknown') .
                ' at ' . now()->format('Y-m-d H:i:s')
        );

        $asset->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'asset' => $asset->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Asset updated successfully');
    }

    /**
     * Delete asset image
     */
    public function deleteImage($id)
    {
        $asset = Asset::findOrFail($id);

        if ($asset->asset_image && Storage::disk('public')->exists($asset->asset_image)) {
            Storage::disk('public')->delete($asset->asset_image);

            $asset->update([
                'asset_image' => null,
                'asset_log' => trim(
                    ($asset->asset_log ? $asset->asset_log . PHP_EOL : '') .
                        'Asset image deleted by ' . (Auth::user()->name ?? 'Unknown') .
                        ' at ' . now()->format('Y-m-d H:i:s')
                ),
            ]);

            return response()->json(['success' => true, 'message' => 'Asset image deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'No image found'], 404);
    }

    /**
     * Request asset disposal (with reason and attachment)
     */
    public function requestDisposal(Request $request, $id)
    {
        $request->validate([
            'dispose_remark' => 'required|string|max:1000',
            'dispose_attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:5120',
        ]);

        $asset = Asset::findOrFail($id);

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('dispose_attachment')) {
            $attachmentPath = $request->file('dispose_attachment')->store('disposals', 'public');
        }

        $newLog = 'Disposal requested by ' . (Auth::user()->name ?? 'Unknown')
            . ' at ' . now()->format('Y-m-d H:i:s')
            . '. Reason: ' . $request->input('dispose_remark');

        $asset->update([
            'dispose_remark' => $request->input('dispose_remark'),
            'dispose_attachment' => $attachmentPath,
            'dispose_status' => 'pending',
            'dispose_requested_by' => Auth::id(),
            'dispose_date' => now(),
            'asset_log' => trim(
                ($asset->asset_log ? $asset->asset_log . PHP_EOL : '') . $newLog
            ),
        ]);

        return redirect()->back()->with('success', 'Disposal request submitted successfully');
    }

    /**
     * Approve asset disposal
     */
    public function approveDisposal(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        if ($asset->dispose_status !== 'pending') {
            return redirect()->back()->with('error', 'This disposal request is not pending');
        }

        $approvalRemark = $request->input('approval_remark');

        $newLog = 'Disposal approved by ' . (Auth::user()->name ?? 'Unknown')
            . ' at ' . now()->format('Y-m-d H:i:s')
            . ($approvalRemark ? '. Remark: ' . $approvalRemark : '');

        $asset->update([
            'is_disposed' => true,
            'dispose_status' => 'approved',
            'dispose_approval_remark' => $approvalRemark,
            'dispose_approved_by' => Auth::id(),
            'dispose_approved_at' => now(),
            'asset_log' => trim(
                ($asset->asset_log ? $asset->asset_log . PHP_EOL : '') . $newLog
            ),
        ]);

        return redirect()->back()->with('success', 'Disposal approved successfully');
    }

    /**
     * Reject asset disposal
     */
    public function rejectDisposal(Request $request, $id)
    {
        $request->validate([
            'approval_remark' => 'required|string|max:1000',
        ]);

        $asset = Asset::findOrFail($id);

        if ($asset->dispose_status !== 'pending') {
            return redirect()->back()->with('error', 'This disposal request is not pending');
        }

        $newLog = 'Disposal rejected by ' . (Auth::user()->name ?? 'Unknown')
            . ' at ' . now()->format('Y-m-d H:i:s')
            . '. Reason: ' . $request->input('approval_remark');

        $asset->update([
            'dispose_status' => 'rejected',
            'dispose_approval_remark' => $request->input('approval_remark'),
            'dispose_approved_by' => Auth::id(),
            'dispose_approved_at' => now(),
            'asset_log' => trim(
                ($asset->asset_log ? $asset->asset_log . PHP_EOL : '') . $newLog
            ),
        ]);

        return redirect()->back()->with('success', 'Disposal rejected');
    }

    /**
     * Recycle disposed asset
     */
    public function recycleAsset(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $otherActiveAssetExists = Asset::where('asset_no', $asset->asset_no)
            ->where('is_disposed', false)
            ->where('is_deleted', false)
            ->where('id', '!=', $asset->id)
            ->exists();

        if ($otherActiveAssetExists) {
            return redirect()->back()->with(
                'error',
                'Another active asset with the same asset number already exists. Recycle is not allowed.'
            );
        }

        $newLog = 'Asset recycled by ' . (Auth::user()->name ?? 'Unknown')
            . ' at ' . now()->format('Y-m-d H:i:s');

        $asset->update([
            'is_disposed' => false,
            'dispose_status' => null,
            'dispose_remark' => null,
            'dispose_attachment' => null,
            'dispose_approval_remark' => null,
            'dispose_requested_by' => null,
            'dispose_approved_by' => null,
            'dispose_approved_at' => null,
            'dispose_date' => null,
            'asset_log' => trim(
                ($asset->asset_log ? $asset->asset_log . PHP_EOL : '') . $newLog
            ),
        ]);

        return redirect()->back()->with('success', 'Asset recycled successfully');
    }

    /**
     * Disposal listing page
     */
    public function disposalList(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $status = $request->input('status');

        $query = Asset::with(['branch', 'group', 'disposeRequester', 'disposeApprover'])
            ->where('is_deleted', false)
            ->whereNotNull('dispose_status');

        if ($status) {
            $query->where('dispose_status', $status);
        }

        $assets = $query->orderBy('dispose_date', 'desc')->get();

        return view('asset.disposalList', [
            'assets' => $assets,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Soft delete asset
     */
    public function destroy(Request $request, $id)
    {
        $deleteRemark = $request->input('delete_remark');
        $asset = Asset::findOrFail($id);

        $newLog = 'Asset deleted by '
            . (Auth::user()->name ?? 'Unknown')
            . ' at ' . now()->format('Y-m-d H:i:s');

        $asset->update([
            'is_deleted' => true,
            'delete_date' => now(),
            'delete_remark' => $deleteRemark,
            'asset_log' => trim(
                ($asset->asset_log ? $asset->asset_log . PHP_EOL : '') . $newLog
            ),
        ]);

        return redirect()->back()->with('success', 'Asset deleted successfully');
    }

    /**
     * Show asset details page
     */
    public function show($id)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $asset = Asset::with(['branch', 'group', 'supplier'])
            ->findOrFail($id);

        return view('asset.view', [
            'asset' => $asset
        ]);
    }

    /**
     * Get asset details with supplier information (API endpoint)
     */
    public function getDetails($id)
    {
        $asset = Asset::with(['branch', 'group', 'supplier'])
            ->findOrFail($id);

        return response()->json([
            'asset' => $asset,
            'supplier' => $asset->supplier
        ]);
    }

    /**
     * Search assets for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $field = $request->input('field', 'both');

        Log::info('Asset search called', ['query' => $query, 'field' => $field]);

        $assets = Asset::with(['branch', 'group'])
            ->where('is_disposed', 0)
            ->where('in_transit', 0)
            ->where(function ($q) use ($query, $field) {
                if ($field === 'asset_no') {
                    $q->where('asset_no', 'LIKE', "%{$query}%");
                } elseif ($field === 'asset_name') {
                    $q->where('asset_name', 'LIKE', "%{$query}%");
                } else {
                    $q->where('asset_no', 'LIKE', "%{$query}%")
                        ->orWhere('asset_name', 'LIKE', "%{$query}%");
                }
            })
            ->limit(20)
            ->get();

        Log::info('Assets found', ['count' => $assets->count()]);

        return response()->json($assets);
    }

    /**
     * Serve files from storage (bypass symlink requirement)
     */
    public function serveFile($path)
    {
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        $mimeType = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    public function bulkChangeColor(Request $request)
    {
        $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:inventory_assets,id',
            'color' => 'nullable|string|max:7',
        ]);

        if (!Auth::user()->accessLevel?->change_color) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        Asset::whereIn('id', $request->asset_ids)->update([
            'color' => $request->color ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color updated for ' . count($request->asset_ids) . ' asset(s)',
        ]);
    }

    public function bulkChangeUserDept(Request $request)
    {
        $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:inventory_assets,id',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if (!Auth::user()->accessLevel?->change_user_dept) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Clear both user and department
        if ($request->boolean('clear')) {
            Asset::whereIn('id', $request->asset_ids)->update([
                'user_id' => null,
                'department_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cleared user/department for ' . count($request->asset_ids) . ' asset(s)',
            ]);
        }

        $updateData = [];
        if ($request->has('user_id') && $request->user_id !== null) {
            $updateData['user_id'] = $request->user_id;
        }
        if ($request->has('department_id') && $request->department_id !== null) {
            $updateData['department_id'] = $request->department_id;
        }

        if (empty($updateData)) {
            return response()->json(['success' => false, 'message' => 'No changes provided'], 422);
        }

        Asset::whereIn('id', $request->asset_ids)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Updated ' . count($request->asset_ids) . ' asset(s)',
        ]);
    }
}
