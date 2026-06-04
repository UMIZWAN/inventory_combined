<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Transfer List</h2>
    </div>

    {{-- Filter row --}}
    <form method="GET" action="{{ url('/marketing/transactions/transfer-list') }}"
        class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">

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
            <label class="block text-xs text-gray-600 mb-1">Status</label>
            <select name="status"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                <option value="">-</option>
                @foreach ($statusOptions as $opt)
                    <option value="{{ $opt }}" {{ ($filters['status'] ?? '') === $opt ? 'selected' : '' }}>
                        {{ $opt }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">From Branch</label>
            <select name="from_branch"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                <option value="">-</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ (string) ($filters['from_branch'] ?? '') === (string) $b->id ? 'selected' : '' }}>
                        {{ $b->branch_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">To Branch</label>
            <select name="to_branch"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                <option value="">-</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" {{ (string) ($filters['to_branch'] ?? '') === (string) $b->id ? 'selected' : '' }}>
                        {{ $b->branch_name }}
                    </option>
                @endforeach
            </select>
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
            <a href="{{ url('/marketing/transactions/transfer-list') }}"
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
        <table id="transfer-table" class="w-full text-sm">
            <thead>
                <tr class="border-y border-gray-200">
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Reference No</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">From → To Branch</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Items</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $txn)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $txn->running_number }}</td>
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            <span class="font-medium">{{ $txn->fromBranch?->code ?? '—' }}</span>
                            <span class="text-gray-400 mx-1">➔</span>
                            <span class="font-medium">{{ $txn->toBranch ? 'MKT-' . $txn->toBranch->code : '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            @php
                                $itemSummary = $txn->items
                                    ->map(fn ($line) => ($line->item?->name ?? '—') . ' — ' . $line->item_unit)
                                    ->take(2)
                                    ->join('; ');
                                $extra = max(0, $txn->items->count() - 2);
                            @endphp
                            <span>{{ $itemSummary ?: '—' }}</span>
                            @if ($extra > 0)
                                <span class="text-xs text-gray-500">+{{ $extra }} more</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $txn->created_at ? $txn->created_at->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusStyle = match ($txn->transaction_status) {
                                    'RECEIVED'   => 'bg-green-100 text-green-700',
                                    'IN-TRANSIT' => 'bg-amber-100 text-amber-700',
                                    'APPROVED'   => 'bg-blue-100 text-blue-700',
                                    'REJECTED'   => 'bg-red-100 text-red-700',
                                    'COMPLETED'  => 'bg-emerald-100 text-emerald-700',
                                    'REQUESTED'  => 'bg-slate-100 text-slate-700',
                                    default      => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusStyle }}">
                                    {{ $txn->transaction_status ?? '—' }}
                                </span>
                                @if ($txn->receiver)
                                    <div class="text-xs text-gray-500 mt-0.5">By: {{ $txn->receiver->name }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <button type="button"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-600 border border-blue-200 rounded hover:bg-blue-50">
                                View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            No transfer records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
