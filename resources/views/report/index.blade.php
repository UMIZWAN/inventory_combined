@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Report</h2>

    {{-- Date Range Filter --}}
    <form method="GET" action="{{ route('report.index') }}" class="mb-6">
        <div class="flex items-end gap-4 flex-wrap">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select name="branch_id" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->branch_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                Filter
            </button>
            <a href="{{ route('report.export', ['from_date' => $fromDate, 'to_date' => $toDate, 'branch_id' => $branchId]) }}"
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            Showing data from <strong>{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</strong>
            to <strong>{{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</strong>
            @if($branchId)
                — Branch: <strong>{{ $branches->firstWhere('id', $branchId)->branch_name ?? '' }}</strong>
            @endif
        </p>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        {{-- Assets Added --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 rounded-full p-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-600 font-medium">Assets Added</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $assetsAdded }}</p>
                </div>
            </div>
        </div>

        {{-- Total Value --}}
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-emerald-100 rounded-full p-2">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-emerald-600 font-medium">Total Value Added</p>
                    <p class="text-2xl font-bold text-emerald-800">RM {{ number_format($totalAssetValue, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Transfers --}}
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-purple-100 rounded-full p-2">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-purple-600 font-medium">Transfers</p>
                    <p class="text-2xl font-bold text-purple-800">{{ $transfersCount }}</p>
                </div>
            </div>
        </div>

        {{-- Disposed --}}
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <div class="bg-red-100 rounded-full p-2">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-red-600 font-medium">Assets Disposed</p>
                    <p class="text-2xl font-bold text-red-800">{{ $disposedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Assets Added Table --}}
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
            Assets Added ({{ $assetsAdded }})
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 border text-left">#</th>
                        <th class="px-4 py-2 border text-left">Asset No</th>
                        <th class="px-4 py-2 border text-left">Asset Name</th>
                        <th class="px-4 py-2 border text-left">Branch</th>
                        <th class="px-4 py-2 border text-left">Group</th>
                        <th class="px-4 py-2 border text-right">Cost (RM)</th>
                        <th class="px-4 py-2 border text-center">Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assetsAddedList as $index => $asset)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border font-medium">{{ $asset->asset_no }}</td>
                            <td class="px-4 py-2 border">{{ $asset->asset_name ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $asset->branch->branch_name ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $asset->group->name ?? '-' }}</td>
                            <td class="px-4 py-2 border text-right">{{ number_format($asset->asset_cost ?? 0, 2) }}</td>
                            <td class="px-4 py-2 border text-center">{{ $asset->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500 border">No assets added in this period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Transfers Table --}}
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
            Transfers ({{ $transfersCount }})
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 border text-left">#</th>
                        <th class="px-4 py-2 border text-left">Reference</th>
                        <th class="px-4 py-2 border text-left">From</th>
                        <th class="px-4 py-2 border text-left">To</th>
                        <th class="px-4 py-2 border text-left">Purpose</th>
                        <th class="px-4 py-2 border text-center">Status</th>
                        <th class="px-4 py-2 border text-right">Cost (RM)</th>
                        <th class="px-4 py-2 border text-left">Created By</th>
                        <th class="px-4 py-2 border text-center">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfersList as $index => $transfer)
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-blue-100 text-blue-800',
                                'in_transit' => 'bg-purple-100 text-purple-800',
                                'partial_received' => 'bg-orange-100 text-orange-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                            ];
                            $color = $statusColors[$transfer->transfer_status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border font-medium">{{ $transfer->transfer_running_no }}</td>
                            <td class="px-4 py-2 border">{{ $transfer->fromBranch->branch_name ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $transfer->toBranch->branch_name ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $transfer->transfer_purpose ?? '-' }}</td>
                            <td class="px-4 py-2 border text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $transfer->transfer_status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border text-right">{{ number_format($transfer->transfer_cost ?? 0, 2) }}</td>
                            <td class="px-4 py-2 border">{{ $transfer->creator->name ?? '-' }}</td>
                            <td class="px-4 py-2 border text-center">{{ $transfer->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-4 text-center text-gray-500 border">No transfers in this period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Disposed Assets Table --}}
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <span class="w-3 h-3 bg-red-500 rounded-full"></span>
            Assets Disposed ({{ $disposedCount }})
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 border text-left">#</th>
                        <th class="px-4 py-2 border text-left">Asset No</th>
                        <th class="px-4 py-2 border text-left">Asset Name</th>
                        <th class="px-4 py-2 border text-left">Branch</th>
                        <th class="px-4 py-2 border text-left">Group</th>
                        <th class="px-4 py-2 border text-right">Cost (RM)</th>
                        <th class="px-4 py-2 border text-center">Date Disposed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disposedList as $index => $asset)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border font-medium">{{ $asset->asset_no }}</td>
                            <td class="px-4 py-2 border">{{ $asset->asset_name ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $asset->branch->branch_name ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $asset->group->name ?? '-' }}</td>
                            <td class="px-4 py-2 border text-right">{{ number_format($asset->asset_cost ?? 0, 2) }}</td>
                            <td class="px-4 py-2 border text-center">{{ $asset->updated_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500 border">No disposed assets in this period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
