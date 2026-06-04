<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MarketingItem;
use App\Models\MarketingItemValue;
use App\Models\MarketingTransaction;
use App\Models\MarketingTransactionItem;
use App\Models\MarketingTransactionPurpose;
use App\Models\ShippingOption;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MarketingTransactionController extends Controller
{
    public const TABS = [
        'marketing-in'  => 'Marketing In',
        'transfer-list' => 'Transfer List',
        'request'       => 'Request',
        'transfer'      => 'Transfer',
        'invoice'       => 'Invoice',
    ];

    /* =========================================================
     |  TAB-RENDERING INDEX (view)
     ========================================================= */
    public function index(Request $request, ?string $tab = 'marketing-in')
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (!array_key_exists($tab, self::TABS)) {
            abort(404);
        }

        $activeBranchId  = session('marketing_branch_id');
        $transactions    = collect();
        $branches        = collect();
        $purposes        = collect();
        $items           = collect();
        $shippingOptions = collect();
        $activeBranch    = null;
        $statusOptions   = ['REQUESTED', 'REJECTED', 'APPROVED', 'IN-TRANSIT', 'RECEIVED', 'COMPLETED'];

        if ($tab === 'marketing-in') {
            $transactions = MarketingTransaction::with(['toBranch', 'items.item'])
                ->where('transaction_type', 'GRN')
                ->when($activeBranchId, fn ($q) => $q->where('to_branch_id', $activeBranchId))
                ->when($request->filled('reference_no'), fn ($q) =>
                    $q->where('running_number', 'LIKE', '%' . $request->reference_no . '%'))
                ->when($request->filled('item_name'), fn ($q) =>
                    $q->whereHas('items.item', fn ($i) =>
                        $i->where('name', 'LIKE', '%' . $request->item_name . '%')))
                ->when($request->filled('from_date'), fn ($q) =>
                    $q->whereDate('received_at', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn ($q) =>
                    $q->whereDate('received_at', '<=', $request->to_date))
                ->latest('received_at')
                ->get();
        }

        if ($tab === 'transfer-list') {
            $branches = Branch::where('is_active', true)->orderBy('branch_name')->get();

            $transactions = MarketingTransaction::with(['fromBranch', 'toBranch', 'items.item', 'receiver'])
                ->where('transaction_type', 'TRANSFER')
                ->when($activeBranchId, fn ($q) =>
                    $q->where(fn ($s) => $s->where('from_branch_id', $activeBranchId)
                                           ->orWhere('to_branch_id', $activeBranchId)))
                ->when($request->filled('reference_no'), fn ($q) =>
                    $q->where('running_number', 'LIKE', '%' . $request->reference_no . '%'))
                ->when($request->filled('item_name'), fn ($q) =>
                    $q->whereHas('items.item', fn ($i) =>
                        $i->where('name', 'LIKE', '%' . $request->item_name . '%')))
                ->when($request->filled('status'), fn ($q) =>
                    $q->where('transaction_status', $request->status))
                ->when($request->filled('from_branch'), fn ($q) =>
                    $q->where('from_branch_id', $request->from_branch))
                ->when($request->filled('to_branch'), fn ($q) =>
                    $q->where('to_branch_id', $request->to_branch))
                ->when($request->filled('from_date'), fn ($q) =>
                    $q->whereDate('created_at', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn ($q) =>
                    $q->whereDate('created_at', '<=', $request->to_date))
                ->latest('created_at')
                ->get();
        }

        if ($tab === 'request') {
            $branches     = Branch::where('is_active', true)->orderBy('branch_name')->get();
            $purposes     = MarketingTransactionPurpose::orderBy('transaction_purpose_name')->get();
            $items        = MarketingItem::with('category')->orderBy('name')->get();
            $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;
        }

        if ($tab === 'transfer') {
            $branches        = Branch::where('is_active', true)->orderBy('branch_name')->get();
            $purposes        = MarketingTransactionPurpose::orderBy('transaction_purpose_name')->get();
            $items           = MarketingItem::with('category')->orderBy('name')->get();
            $shippingOptions = ShippingOption::where('is_active', true)->orderBy('name')->get();
            $activeBranch    = $activeBranchId ? Branch::find($activeBranchId) : null;
        }

        if ($tab === 'invoice') {
            $purposes     = MarketingTransactionPurpose::orderBy('transaction_purpose_name')->get();
            $items        = MarketingItem::with('category')->orderBy('name')->get();
            $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;
        }

        return view('marketingModule.transaction.index', [
            'tab'             => $tab,
            'tabs'            => self::TABS,
            'transactions'    => $transactions,
            'branches'        => $branches,
            'purposes'        => $purposes,
            'items'           => $items,
            'shippingOptions' => $shippingOptions,
            'activeBranch'    => $activeBranch,
            'statusOptions'   => $statusOptions,
            'filters'         => $request->only([
                'reference_no', 'item_name', 'status',
                'from_branch', 'to_branch', 'from_date', 'to_date',
            ]),
        ]);
    }

    /* =========================================================
     |  SHOW (JSON detail — useful for "View" buttons via AJAX)
     ========================================================= */
    public function show($id)
    {
        try {
            $txn = MarketingTransaction::with([
                'items.item',
                'fromBranch',
                'toBranch',
                'creator',
                'purpose',
                'shippingOption',
            ])->find($id);

            if (!$txn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction detail',
                'data'    => $txn,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  STORE REQUEST (form handler — Stock Request)
     ========================================================= */
    public function storeRequest(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'from_branch_id'         => 'required|exists:branches,id|different:to_branch_id',
            'to_branch_id'           => 'required|exists:branches,id',
            'transaction_purpose_id' => 'nullable|exists:inventory_marketing_transaction_purpose,id',
            'remark'                 => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.item_id'        => 'required|exists:inventory_marketing_items,id',
            'items.*.item_unit'      => 'required|integer|min:1',
            'items.*.unit_price'     => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $total = collect($validated['items'])->sum(fn ($l) =>
                ($l['unit_price'] ?? 0) * $l['item_unit']);

            $txn = MarketingTransaction::create([
                'running_number'         => $this->generateRunningNumber('TRANSFER'),
                'transaction_type'       => 'TRANSFER',
                'from_branch_id'         => $validated['from_branch_id'],
                'to_branch_id'           => $validated['to_branch_id'],
                'transaction_purpose_id' => $validated['transaction_purpose_id'] ?? null,
                'transaction_status'     => 'REQUESTED',
                'transaction_remark'     => $validated['remark'] ?? null,
                'transaction_total_cost' => $total,
                'created_by'             => Auth::id(),
            ]);

            foreach ($validated['items'] as $line) {
                MarketingTransactionItem::create([
                    'transaction_id' => $txn->id,
                    'item_id'        => $line['item_id'],
                    'item_unit'      => $line['item_unit'],
                    'status'         => 'ON HOLD',
                ]);
            }
        });

        return redirect('/marketing/transactions/transfer-list')
            ->with('success', 'Stock request submitted successfully.');
    }

    /* =========================================================
     |  STORE TRANSFER (form handler — Stock Transfer)
     ========================================================= */
    public function storeTransfer(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'from_branch_id'         => 'required|exists:branches,id|different:to_branch_id',
            'to_branch_id'           => 'required|exists:branches,id',
            'transaction_purpose_id' => 'nullable|exists:inventory_marketing_transaction_purpose,id',
            'shipping_option_id'     => 'nullable|exists:shipping_options,id',
            'remark'                 => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.item_id'        => 'required|exists:inventory_marketing_items,id',
            'items.*.item_unit'      => 'required|integer|min:1',
            'items.*.unit_price'     => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $total = collect($validated['items'])->sum(fn ($l) =>
                ($l['unit_price'] ?? 0) * $l['item_unit']);

            $txn = MarketingTransaction::create([
                'running_number'         => $this->generateRunningNumber('TRANSFER'),
                'transaction_type'       => 'TRANSFER',
                'from_branch_id'         => $validated['from_branch_id'],
                'to_branch_id'           => $validated['to_branch_id'],
                'transaction_purpose_id' => $validated['transaction_purpose_id'] ?? null,
                'shipping_option_id'     => $validated['shipping_option_id'] ?? null,
                'transaction_status'     => 'APPROVED',
                'transaction_remark'     => $validated['remark'] ?? null,
                'transaction_total_cost' => $total,
                'created_by'             => Auth::id(),
                'approved_by'            => Auth::id(),
                'approved_at'            => now(),
            ]);

            foreach ($validated['items'] as $line) {
                MarketingTransactionItem::create([
                    'transaction_id' => $txn->id,
                    'item_id'        => $line['item_id'],
                    'item_unit'      => $line['item_unit'],
                    'status'         => 'ON HOLD',
                ]);
            }
        });

        return redirect('/marketing/transactions/transfer-list')
            ->with('success', 'Stock transfer submitted successfully.');
    }

    /* =========================================================
     |  STORE INVOICE (form handler — Stock Out)
     ========================================================= */
    public function storeInvoice(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'from_branch_id'         => 'required|exists:branches,id',
            'transaction_purpose_id' => 'nullable|exists:inventory_marketing_transaction_purpose,id',
            'remark'                 => 'nullable|string',
            'attachment'             => 'nullable|file|mimes:pdf,xls,xlsx,doc,docx|max:10240',
            'items'                  => 'required|array|min:1',
            'items.*.item_id'        => 'required|exists:inventory_marketing_items,id',
            'items.*.item_unit'      => 'required|integer|min:1',
            'items.*.unit_price'     => 'nullable|numeric|min:0',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('marketing/invoices', 'public');
        }

        DB::transaction(function () use ($validated, $attachmentPath) {
            $total = collect($validated['items'])->sum(fn ($l) =>
                ($l['unit_price'] ?? 0) * $l['item_unit']);

            $txn = MarketingTransaction::create([
                'running_number'         => $this->generateRunningNumber('STOCK OUT'),
                'transaction_type'       => 'STOCK OUT',
                'from_branch_id'         => $validated['from_branch_id'],
                'transaction_purpose_id' => $validated['transaction_purpose_id'] ?? null,
                'transaction_status'     => 'COMPLETED',
                'transaction_remark'     => $validated['remark'] ?? null,
                'transaction_total_cost' => $total,
                'attachment'             => $attachmentPath,
                'created_by'             => Auth::id(),
            ]);

            foreach ($validated['items'] as $line) {
                MarketingTransactionItem::create([
                    'transaction_id' => $txn->id,
                    'item_id'        => $line['item_id'],
                    'item_unit'      => $line['item_unit'],
                    'status'         => 'DELIVERED',
                ]);

                // Deduct from source branch stock
                $bv = MarketingItemValue::where('branch_id', $validated['from_branch_id'])
                    ->where('item_id', $line['item_id'])
                    ->first();
                if ($bv) {
                    $bv->decrement('current_unit', $line['item_unit']);
                }
            }
        });

        return redirect('/marketing/transactions/marketing-in')
            ->with('success', 'Invoice submitted successfully.');
    }

    /* =========================================================
     |  UPDATE — TRANSFER workflow + GRN revert
     |
     |  Adapted from the legacy API controller. Handles:
     |    REQUESTED → REJECTED   (selected lines REJECTED, rest APPROVED)
     |    REQUESTED → APPROVED   (selected lines APPROVED, rest REJECTED)
     |    APPROVED  → IN-TRANSIT (decrement from_branch stock for selected)
     |    IN-TRANSIT → RECEIVED  (increment to_branch stock for selected)
     |    GRN → REVERTED         (rollback stock, delete transaction)
     |
     |  Body: { transaction_status, selected_items[], shipping_option_id?, remark? }
     ========================================================= */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'transaction_status' => 'required|in:REQUESTED,REJECTED,APPROVED,IN-TRANSIT,RECEIVED,IN PROGRESS,COMPLETED,REVERTED',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $txn = MarketingTransaction::findOrFail($id);

            // -----------------------------------------------------------------
            //  TRANSFER workflow
            // -----------------------------------------------------------------
            if ($txn->transaction_type === 'TRANSFER') {

                if ($request->transaction_status === $txn->transaction_status) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Transfer already ' . $txn->transaction_status,
                    ], 400);
                }

                $selectedItems = $request->input('selected_items', []);
                if (!is_array($selectedItems)) {
                    $selectedItems = [];
                }

                // ----- REJECTED ----------------------------------------------
                if ($request->transaction_status === 'REJECTED' && $txn->transaction_status === 'REQUESTED') {
                    DB::beginTransaction();
                    try {
                        MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->whereIn('id', $selectedItems)
                            ->update(['status' => 'REJECTED']);

                        MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->where(function ($q) use ($selectedItems) {
                                $q->whereNotIn('id', $selectedItems)
                                  ->orWhereNull('status');
                            })
                            ->update(['status' => 'ON HOLD']);

                        $txn->update([
                            'transaction_status' => 'REJECTED',
                            'transaction_remark' => $request->remark ?? $txn->transaction_remark,
                            'rejected_by'        => Auth::id(),
                            'rejected_at'        => now(),
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Selected items rejected successfully',
                            'data'    => $txn->fresh(['items.item', 'fromBranch', 'toBranch']),
                        ]);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Failed to reject.', 'error' => $e->getMessage()], 500);
                    }
                }

                // ----- APPROVED ----------------------------------------------
                if ($request->transaction_status === 'APPROVED' && $txn->transaction_status === 'REQUESTED') {
                    DB::beginTransaction();
                    try {
                        // Check stock availability at the from branch
                        $itemsToApprove = MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->whereIn('id', $selectedItems)
                            ->get();

                        $insufficient = [];
                        foreach ($itemsToApprove as $line) {
                            $bv = MarketingItemValue::where('branch_id', $txn->from_branch_id)
                                ->where('item_id', $line->item_id)
                                ->first();
                            $current = $bv ? $bv->current_unit : 0;
                            if ($current < $line->item_unit) {
                                $item = MarketingItem::find($line->item_id);
                                $insufficient[] = ($item->name ?? "ID {$line->item_id}") . " (Available: {$current}, Requested: {$line->item_unit})";
                            }
                        }

                        if (!empty($insufficient)) {
                            throw new Exception('Insufficient stock for: ' . implode(', ', $insufficient));
                        }

                        MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->whereIn('id', $selectedItems)
                            ->update(['status' => 'APPROVED']);

                        MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->where(function ($q) use ($selectedItems) {
                                $q->whereNotIn('id', $selectedItems)
                                  ->orWhereNull('status');
                            })
                            ->update(['status' => 'REJECTED']);

                        $txn->update([
                            'transaction_status' => 'APPROVED',
                            'transaction_remark' => $request->remark ?? $txn->transaction_remark,
                            'approved_by'        => Auth::id(),
                            'approved_at'        => now(),
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Selected items approved successfully',
                            'data'    => $txn->fresh(['items.item', 'fromBranch', 'toBranch']),
                        ]);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Failed to approve.', 'error' => $e->getMessage()], 500);
                    }
                }

                // ----- IN-TRANSIT (Send) --------------------------------------
                if ($request->transaction_status === 'IN-TRANSIT' && $txn->transaction_status === 'APPROVED') {
                    DB::beginTransaction();
                    try {
                        if (!$request->shipping_option_id) {
                            throw new Exception('Shipping option ID cannot be empty.');
                        }

                        $shippingId = (int) $request->shipping_option_id;

                        $lines = MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->whereIn('id', $selectedItems)
                            ->where(function ($q) {
                                $q->where('status', 'APPROVED')
                                  ->orWhereNull('status')
                                  ->orWhere('status', '');
                            })
                            ->get();

                        if ($lines->isEmpty()) {
                            throw new Exception('No valid items selected to send.');
                        }

                        foreach ($lines as $line) {
                            $bv = MarketingItemValue::where('branch_id', $txn->from_branch_id)
                                ->where('item_id', $line->item_id)
                                ->first();

                            if (!$bv || $bv->current_unit < $line->item_unit) {
                                throw new Exception("Insufficient units for item ID {$line->item_id}.");
                            }

                            $bv->decrement('current_unit', $line->item_unit);
                            $line->update(['status' => 'IN-TRANSIT']);
                        }

                        $txn->update([
                            'transaction_status'  => 'IN-TRANSIT',
                            'shipping_option_id'  => $shippingId,
                            'transaction_remark'  => $request->remark ?? $txn->transaction_remark,
                            'updated_by'          => Auth::id(),
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Selected items sent successfully',
                            'data'    => $txn->fresh(['items.item', 'fromBranch', 'toBranch']),
                        ]);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Failed to send items.', 'error' => $e->getMessage()], 500);
                    }
                }

                // ----- RECEIVED ----------------------------------------------
                if ($request->transaction_status === 'RECEIVED' && in_array($txn->transaction_status, ['IN-TRANSIT', 'RECEIVED'])) {
                    DB::beginTransaction();
                    try {
                        // Pull receivable lines (IN-TRANSIT / null / empty)
                        $lines = MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->whereIn('id', $selectedItems)
                            ->where(function ($q) {
                                $q->where('status', 'IN-TRANSIT')
                                  ->orWhereNull('status')
                                  ->orWhere('status', '');
                            })
                            ->get();

                        // Fallback: if no specific selection, take all receivable lines
                        if ($lines->isEmpty() && empty($selectedItems)) {
                            $lines = MarketingTransactionItem::where('transaction_id', $txn->id)
                                ->where(function ($q) {
                                    $q->where('status', 'IN-TRANSIT')
                                      ->orWhereNull('status')
                                      ->orWhere('status', '');
                                })
                                ->get();
                        }

                        if ($lines->isEmpty()) {
                            throw new Exception('No valid items available for receiving.');
                        }

                        foreach ($lines as $line) {
                            $line->update(['status' => 'RECEIVED']);

                            $bv = MarketingItemValue::where('branch_id', $txn->to_branch_id)
                                ->where('item_id', $line->item_id)
                                ->first();

                            if ($bv) {
                                $bv->increment('current_unit', $line->item_unit);
                            } else {
                                MarketingItemValue::create([
                                    'branch_id'    => $txn->to_branch_id,
                                    'item_id'      => $line->item_id,
                                    'current_unit' => $line->item_unit,
                                ]);
                            }
                        }

                        // If nothing is still IN-TRANSIT, mark the whole transaction RECEIVED
                        $stillInTransit = MarketingTransactionItem::where('transaction_id', $txn->id)
                            ->where(function ($q) {
                                $q->where('status', 'IN-TRANSIT')
                                  ->orWhereNull('status')
                                  ->orWhere('status', '');
                            })
                            ->exists();

                        if (!$stillInTransit) {
                            $txn->update([
                                'transaction_status' => 'RECEIVED',
                                'transaction_remark' => $request->remark ?? $txn->transaction_remark,
                                'received_by'        => Auth::id(),
                                'received_at'        => now(),
                            ]);
                        }

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Selected items received successfully',
                            'data'    => $txn->fresh(['items.item', 'fromBranch', 'toBranch']),
                        ]);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Failed to receive items.', 'error' => $e->getMessage()], 500);
                    }
                }
            }

            // -----------------------------------------------------------------
            //  GRN — REVERT (deduct stock that was added, then delete txn)
            // -----------------------------------------------------------------
            if ($txn->transaction_type === 'GRN' && $request->transaction_status === 'REVERTED') {
                DB::beginTransaction();
                try {
                    $lines = MarketingTransactionItem::where('transaction_id', $txn->id)->get();

                    foreach ($lines as $line) {
                        $bv = MarketingItemValue::where('branch_id', $txn->to_branch_id)
                            ->where('item_id', $line->item_id)
                            ->first();

                        if (!$bv) {
                            throw new Exception("Branch value not found for item ID {$line->item_id}. Revert cancelled.");
                        }

                        if ($bv->current_unit < $line->item_unit) {
                            throw new Exception("Insufficient stock for item ID {$line->item_id}. Current: {$bv->current_unit}, Required: {$line->item_unit}. Revert cancelled.");
                        }

                        $bv->decrement('current_unit', $line->item_unit);
                    }

                    MarketingTransactionItem::where('transaction_id', $txn->id)->delete();
                    $txn->delete();

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Transaction reverted and deleted. Stock has been deducted.',
                    ]);
                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error('Revert transaction failed: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to revert: ' . $e->getMessage(),
                    ], 500);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Unsupported transition for this transaction type/status.',
                'type'    => $txn->transaction_type,
                'status'  => $txn->transaction_status,
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  Running number helper
     |    TRANSFER  → MKT-NNNNN
     |    STOCK OUT → INV-NNNNN
     |    GRN       → GRN-NNNNN
     ========================================================= */
    private function generateRunningNumber(string $type = 'TRANSFER'): string
    {
        $prefix = match ($type) {
            'STOCK OUT' => 'INV-',
            'GRN'       => 'GRN-',
            default     => 'MKT-',
        };

        $last = MarketingTransaction::where('transaction_type', $type)
            ->where('running_number', 'LIKE', $prefix . '%')
            ->orderByDesc('id')
            ->value('running_number');

        $next = $last ? ((int) str_replace($prefix, '', $last)) + 1 : 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
