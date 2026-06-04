@extends('layouts.app', ['module' => 'marketing'])

@section('content')
    <div class="px-4">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Invoice List</h2>

            {{-- Filters --}}
            <form method="GET" action="{{ url('/marketing/reports/invoice') }}"
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
                    <label class="block text-xs text-gray-600 mb-1">Invoice Purpose</label>
                    <select name="invoice_purpose"
                        class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                        <option value="">[select]</option>
                        @foreach ($purposes as $p)
                            <option value="{{ $p->id }}" {{ (int) ($filters['invoice_purpose'] ?? 0) === (int) $p->id ? 'selected' : '' }}>
                                {{ $p->transaction_purpose_name }}
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

                <div class="md:col-span-4 flex gap-2 mt-2">
                    <button type="submit"
                        class="px-4 py-1.5 bg-emerald-600 text-white text-sm rounded hover:bg-emerald-700">
                        Apply
                    </button>
                    <a href="{{ url('/marketing/reports/invoice') }}"
                        class="px-4 py-1.5 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                        Clear
                    </a>
                </div>
            </form>

            {{-- Download buttons --}}
            <div class="flex gap-2 mb-4">
                <button type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Excel
                </button>
                <button type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download CSV
                </button>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-y border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Reference No</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Branch</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Items</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Purpose</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date Issued</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">
                                    {{ $inv->running_number }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                    {{ $inv->fromBranch ? 'MKT-' . $inv->fromBranch->code : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <ul class="list-disc list-inside text-gray-700 space-y-0.5">
                                        @foreach ($inv->items as $line)
                                            <li>{{ ($line->item?->name ?? '—') . ' — ' . $line->item_unit }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $inv->purpose?->transaction_purpose_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ optional($inv->created_at)->format('d/m/Y') }}
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
                                    No invoices found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
