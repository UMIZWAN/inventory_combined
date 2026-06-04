@extends('layouts.app', ['module' => 'marketing'])

@section('content')
    <div class="px-4">

        {{-- Title --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-4">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Inventory Report</h2>

            {{-- Filter Card --}}
            <div class="border border-gray-200 rounded p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Filter</h3>

                <form method="GET" action="{{ url('/marketing/reports/in-out-history') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Branch</label>
                            <select name="branch_id"
                                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                                <option value="">Branches</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}" {{ (int) ($filters['branch_id'] ?? 0) === (int) $b->id ? 'selected' : '' }}>
                                        {{ $b->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Code/Name</label>
                            <input type="text" name="code_name" value="{{ $filters['code_name'] ?? '' }}"
                                placeholder="Search by code or name"
                                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Category</label>
                            <select name="category_id"
                                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                                <option value="">All Categories</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" {{ (int) ($filters['category_id'] ?? 0) === (int) $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                            Apply
                        </button>
                        <a href="{{ url('/marketing/reports/in-out-history') }}"
                            class="px-4 py-1.5 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Download Buttons --}}
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

        {{-- Report Table --}}
        <div class="bg-white rounded shadow-sm border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider align-middle" rowspan="2">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider align-middle" rowspan="2">Name</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-l border-gray-200" colspan="4">Stock In</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-l border-gray-200" colspan="4">Stock Out</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider align-middle border-l border-gray-200" rowspan="2">Current Unit</th>
                    </tr>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase border-l border-gray-200">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">From</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase border-l border-gray-200">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Purpose</th>
                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $row->code ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row->name ?? '—' }}</td>

                            {{-- Stock In --}}
                            <td class="px-4 py-3 text-gray-600 border-l border-gray-100 whitespace-nowrap">
                                {{ $row->isIn ? optional($row->date)->format('d/m/Y') : '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $row->isIn ? $row->type : '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $row->isIn ? $row->from : '' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ $row->isIn ? $row->qty : '' }}
                            </td>

                            {{-- Stock Out --}}
                            <td class="px-4 py-3 text-gray-600 border-l border-gray-100 whitespace-nowrap">
                                {{ $row->isOut ? optional($row->date)->format('d/m/Y') : '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $row->isOut ? $row->type : '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $row->isOut ? $row->purpose : '' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ $row->isOut ? $row->qty : '' }}
                            </td>

                            {{-- Current Unit --}}
                            <td class="px-4 py-3 text-right font-medium text-gray-800 border-l border-gray-100">
                                {{ $currentUnits[$row->item_id] ?? 0 }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-10 text-center text-gray-400">
                                No records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
