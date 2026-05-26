@extends('layouts.app')

@section('content')
    <div class="px-4">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="ml-2 text-xl font-semibold text-gray-800">
                    Asset Transfer History
                </h3>
                <p class="ml-2 mt-1 text-sm text-gray-500">Manage asset transfers.</p>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- Action Notifications --}}
        @if($totalActionCount > 0)
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">

                @if($pendingApprovalCount > 0)
                    <div class="flex items-center gap-3 px-4 py-3 bg-yellow-50 border border-yellow-300 rounded-lg">
                        <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse flex-shrink-0"></div>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">Pending Approval</p>
                            <p class="text-xs text-yellow-700">{{ $pendingApprovalCount }} transfer(s) waiting for your approval</p>
                        </div>
                    </div>
                @endif

                @if($toSendCount > 0)
                    <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-300 rounded-lg">
                        <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse flex-shrink-0"></div>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Ready to Send</p>
                            <p class="text-xs text-blue-700">{{ $toSendCount }} approved transfer(s) waiting to be sent</p>
                        </div>
                    </div>
                @endif

                @if($toReceiveCount > 0)
                    <div class="flex items-center gap-3 px-4 py-3 bg-purple-50 border border-purple-300 rounded-lg">
                        <div class="w-2 h-2 bg-purple-400 rounded-full animate-pulse flex-shrink-0"></div>
                        <div>
                            <p class="text-sm font-semibold text-purple-800">Pending Receipt</p>
                            <p class="text-xs text-purple-700">{{ $toReceiveCount }} transfer(s) in transit waiting to be received</p>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        {{-- Filter Form --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-4">
            <form method="GET">
                <div class="flex flex-wrap gap-4 items-end mb-4">
                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                        <select name="status"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="partial_received" {{ request('status') == 'partial_received' ? 'selected' : '' }}>Partial Received</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    {{-- From Branch --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">From Branch</label>
                        <select name="from_branch"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('from_branch') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- To Branch --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">To Branch</label>
                        <select name="to_branch"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">All</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('to_branch') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- From Date --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    {{-- To Date --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                {{-- Buttons Row --}}
                <div class="flex gap-2 pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('assetTransfer.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Transfers Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4">
                <table id="transfersTable" class="w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Reference No</th>
                            <th class="px-4 py-3 text-left">Branch</th>
                            <th class="px-4 py-3 text-left">Assets</th>
                            <th class="px-4 py-3 text-left">Purpose</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($transfers as $transfer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-blue-600">
                                    {{ $transfer->transfer_running_no }}
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <span class="font-medium">{{ $transfer->fromBranch->branch_name ?? '-' }}</span>
                                        <span class="mx-1 text-gray-400">&rarr;</span>
                                        <span class="font-medium">{{ $transfer->toBranch->branch_name ?? '-' }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ $transfer->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <ul class="list-disc list-inside text-gray-700">
                                        @forelse($transfer->assets as $asset)
                                            <li>{{ $asset->asset_name }}</li>
                                        @empty
                                            <li class="text-gray-400">No assets</li>
                                        @endforelse
                                    </ul>
                                </td>
                                <td class="px-4 py-3">{{ $transfer->transfer_purpose }}</td>
                                <td class="px-4 py-3">
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
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $color }}">
                                        {{ ucfirst(str_replace('_', ' ', $transfer->transfer_status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('assetTransfer.show', $transfer->id) }}"
                                        class="text-blue-600 hover:underline text-sm">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#transfersTable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']], // Sort by Reference No descending (newest first)
                language: {
                    search: "Search Transfers:",
                    lengthMenu: "Show _MENU_ entries",
                    emptyTable: "No transfer records found."
                },
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on Action column
                ]
            });
        });

    </script>

    <style>
        /* DataTables Length (Show entries) Styling */
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 0rem;
        }

        .dataTables_wrapper .dataTables_length select {
            margin: 0 0.5rem;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem;
            appearance: none;
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
        }

        /* DataTables Search Styling */
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_filter label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #818cf8;
            ring: 2px;
            ring-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
        }

        /* DataTables Info and Pagination Styling */
        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem;
            color: #6b7280;
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4f46e5 !important;
            color: white !important;
            border: 1px solid #4f46e5 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eef2ff !important;
            color: #4f46e5 !important;
            border: 1px solid #c7d2fe !important;
        }
    </style>
@endsection