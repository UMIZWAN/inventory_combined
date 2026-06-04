@php
    $canEdit = auth()->user()?->marketingAccessLevel?->add_edit_transaction;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Receive List</h2>
        @if ($canEdit)
            <button type="button" id="receive-items-btn"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-full hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Receive Items
            </button>
        @endif
    </div>

    {{-- Filter row --}}
    <form method="GET" action="{{ url('/marketing/transactions/marketing-in') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div>
            <label class="block text-xs text-gray-600 mb-1">Reference No</label>
            <input type="text" name="reference_no" value="{{ $filters['reference_no'] ?? '' }}"
                placeholder="e.g. TXN0012"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Item Name</label>
            <input type="text" name="item_name" value="{{ $filters['item_name'] ?? '' }}"
                placeholder="e.g. Printer"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">From Date</label>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">To Date</label>
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
        </div>

        <div class="md:col-span-4 flex gap-2">
            <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white text-sm rounded hover:bg-emerald-700">
                Apply Filters
            </button>
            <a href="{{ url('/marketing/transactions/marketing-in') }}"
                class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50">
                Clear
            </a>
            <div class="ml-auto flex gap-2">
                <button type="button" id="download-excel"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Excel
                </button>
                <button type="button" id="download-csv"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download CSV
                </button>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table id="receive-table" class="w-full text-sm">
            <thead>
                <tr class="border-y border-gray-200">
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Reference No</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Branch</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Items</th>
                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Total Cost</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date Received</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $txn)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $txn->running_number }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $txn->toBranch?->branch_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $txn->items->count() }} {{ Str::plural('item', $txn->items->count()) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">
                            {{ $txn->transaction_total_cost !== null
                                ? 'RM ' . number_format($txn->transaction_total_cost, 2)
                                : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $txn->received_at ? $txn->received_at->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusStyle = match ($txn->transaction_status) {
                                    'RECEIVED'   => 'bg-green-100 text-green-700',
                                    'IN-TRANSIT' => 'bg-amber-100 text-amber-700',
                                    'APPROVED'   => 'bg-blue-100 text-blue-700',
                                    'REJECTED'   => 'bg-red-100 text-red-700',
                                    'COMPLETED'  => 'bg-emerald-100 text-emerald-700',
                                    default      => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusStyle }}">
                                {{ $txn->transaction_status ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button type="button" class="text-gray-400 hover:text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            No receive records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
