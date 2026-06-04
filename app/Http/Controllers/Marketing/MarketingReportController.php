<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MarketingCategory;
use App\Models\MarketingItem;
use App\Models\MarketingTransaction;
use App\Models\MarketingTransactionItem;
use App\Models\MarketingTransactionPurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingReportController extends Controller
{
    public function inOutHistory(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $branches   = Branch::where('is_active', true)->orderBy('branch_name')->get();
        $categories = MarketingCategory::orderBy('name')->get();

        $branchId   = $request->input('branch_id') ?: session('marketing_branch_id');
        $codeName   = $request->input('code_name');
        $categoryId = $request->input('category_id');
        $fromDate   = $request->input('date_from');
        $toDate     = $request->input('date_to');

        $lines = MarketingTransactionItem::with([
                'item.category',
                'transaction.fromBranch',
                'transaction.toBranch',
                'transaction.purpose',
            ])
            ->whereHas('transaction')
            ->whereHas('item', function ($q) use ($codeName, $categoryId) {
                if ($codeName) {
                    $q->where(function ($s) use ($codeName) {
                        $s->where('item_running_number', 'LIKE', '%' . $codeName . '%')
                          ->orWhere('name', 'LIKE', '%' . $codeName . '%');
                    });
                }
                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
            })
            ->when($branchId, function ($q) use ($branchId) {
                $q->whereHas('transaction', fn ($t) =>
                    $t->where('from_branch_id', $branchId)
                      ->orWhere('to_branch_id', $branchId));
            })
            ->when($fromDate, function ($q) use ($fromDate) {
                $q->whereHas('transaction', fn ($t) => $t->whereDate('created_at', '>=', $fromDate));
            })
            ->when($toDate, function ($q) use ($toDate) {
                $q->whereHas('transaction', fn ($t) => $t->whereDate('created_at', '<=', $toDate));
            })
            ->get();

        // Decide IN vs OUT per row based on the active branch's perspective
        $rows = $lines->map(function ($line) use ($branchId) {
            $txn  = $line->transaction;
            $item = $line->item;

            $isIn  = false;
            $isOut = false;

            if ($txn->transaction_type === 'GRN') {
                $isIn = true;
            } elseif ($txn->transaction_type === 'STOCK OUT') {
                $isOut = true;
            } elseif ($txn->transaction_type === 'TRANSFER') {
                if ($branchId) {
                    $isIn  = (int) $txn->to_branch_id === (int) $branchId;
                    $isOut = (int) $txn->from_branch_id === (int) $branchId;
                } else {
                    // No branch filter: count transfer as both sides for visibility
                    $isIn  = true;
                    $isOut = true;
                }
            }

            return (object) [
                'code'         => $item?->item_running_number,
                'name'         => $item?->name,
                'category'     => $item?->category?->name,
                'isIn'         => $isIn,
                'isOut'        => $isOut,
                'date'         => $txn->created_at,
                'type'         => $txn->transaction_type,
                'from'         => $txn->fromBranch ? 'MKT-' . $txn->fromBranch->code : '—',
                'purpose'      => $txn->purpose?->transaction_purpose_name ?? '—',
                'qty'          => $line->item_unit,
                'item_id'      => $item?->id,
            ];
        });

        // Current unit per item at the filter branch (or sum across branches if no branch filter)
        $itemIds = $rows->pluck('item_id')->filter()->unique()->values();

        $currentUnits = MarketingItem::with('values')
            ->whereIn('id', $itemIds)
            ->get()
            ->mapWithKeys(function ($item) use ($branchId) {
                $qty = $branchId
                    ? $item->values->firstWhere('branch_id', (int) $branchId)?->current_unit ?? 0
                    : $item->values->sum('current_unit');
                return [$item->id => $qty];
            });

        return view('marketingModule.report.in-out-history', [
            'branches'     => $branches,
            'categories'   => $categories,
            'rows'         => $rows->sortByDesc('date')->values(),
            'currentUnits' => $currentUnits,
            'filters'      => [
                'branch_id'   => $branchId,
                'code_name'   => $codeName,
                'category_id' => $categoryId,
                'date_from'   => $fromDate,
                'date_to'     => $toDate,
            ],
        ]);
    }

    public function invoice(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $purposes = MarketingTransactionPurpose::orderBy('transaction_purpose_name')->get();

        $branchId    = session('marketing_branch_id');
        $referenceNo = $request->input('reference_no');
        $itemName    = $request->input('item_name');
        $purposeId   = $request->input('invoice_purpose');
        $fromDate    = $request->input('from_date');
        $toDate      = $request->input('to_date');

        $invoices = MarketingTransaction::with(['fromBranch', 'purpose', 'items.item'])
            ->where('transaction_type', 'STOCK OUT')
            ->when($branchId, fn ($q) => $q->where('from_branch_id', $branchId))
            ->when($referenceNo, fn ($q) =>
                $q->where('running_number', 'LIKE', '%' . $referenceNo . '%'))
            ->when($itemName, fn ($q) =>
                $q->whereHas('items.item', fn ($i) =>
                    $i->where('name', 'LIKE', '%' . $itemName . '%')))
            ->when($purposeId, fn ($q) => $q->where('transaction_purpose_id', $purposeId))
            ->when($fromDate, fn ($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('created_at', '<=', $toDate))
            ->latest('created_at')
            ->get();

        return view('marketingModule.report.invoice', [
            'invoices' => $invoices,
            'purposes' => $purposes,
            'filters'  => [
                'reference_no'    => $referenceNo,
                'item_name'       => $itemName,
                'invoice_purpose' => $purposeId,
                'from_date'       => $fromDate,
                'to_date'         => $toDate,
            ],
        ]);
    }
}
