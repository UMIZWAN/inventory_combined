<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MarketingItem;
use App\Models\MarketingTransaction;
use App\Models\MarketingTransactionItem;
use App\Models\MarketingTransactionPurpose;
use App\Models\ShippingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketingTransactionController extends Controller
{
    public const TABS = [
        'marketing-in'  => 'Marketing In',
        'transfer-list' => 'Transfer List',
        'request'       => 'Request',
        'transfer'      => 'Transfer',
        'invoice'       => 'Invoice',
    ];

    public function index(Request $request, ?string $tab = 'marketing-in')
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (!array_key_exists($tab, self::TABS)) {
            abort(404);
        }

        $activeBranchId = session('marketing_branch_id');
        $transactions   = collect();
        $branches       = collect();
        $purposes       = collect();
        $items          = collect();
        $activeBranch   = null;
        $statusOptions  = ['REQUESTED', 'REJECTED', 'APPROVED', 'IN-TRANSIT', 'RECEIVED', 'COMPLETED'];

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
            'shippingOptions' => $shippingOptions ?? collect(),
            'activeBranch'    => $activeBranch,
            'statusOptions'   => $statusOptions,
            'filters'         => $request->only([
                'reference_no', 'item_name', 'status',
                'from_branch', 'to_branch', 'from_date', 'to_date',
            ]),
        ]);
    }

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
                'running_number'         => $this->generateRunningNumber(),
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
                'running_number'         => $this->generateRunningNumber(),
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
                'running_number'         => $this->generateRunningNumber(),
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
            }
        });

        return redirect('/marketing/transactions/marketing-in')
            ->with('success', 'Invoice submitted successfully.');
    }

    private function generateRunningNumber(): string
    {
        $prefix = 'MKT-' . now()->format('ym');
        $last   = MarketingTransaction::where('running_number', 'LIKE', $prefix . '%')
            ->orderByDesc('id')->value('running_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
