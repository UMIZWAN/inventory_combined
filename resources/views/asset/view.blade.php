@extends('layouts.app')

@section('content')
    <div class="px-4">

        {{-- Back Button and Header --}}
        <div class="flex items-center mb-6">
            <a href="{{ route('master.list') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Assets
            </a>
            <h3 class="ml-4 text-xl font-semibold text-gray-800">
                Asset Details
            </h3>
        </div>

        {{-- Asset Image Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            @if ($asset->asset_image)
                <img src="{{ asset('storage/' . $asset->asset_image) }}" alt="{{ $asset->asset_name }}"
                    class="w-full max-w-md mx-auto rounded-lg border shadow-sm object-cover" style="max-height: 300px;">
            @else
                <div class="w-full max-w-md mx-auto h-64 bg-gray-100 rounded-lg border flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>

        {{-- Asset Information Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Asset Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Asset Code</label>
                    <p class="text-gray-900 font-medium">{{ $asset->asset_no ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Asset Name</label>
                    <p class="text-gray-900">{{ $asset->asset_name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Branch</label>
                    <p class="text-gray-900">{{ $asset->branch->branch_name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Asset Group</label>
                    <p class="text-gray-900">{{ $asset->group->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">User</label>
                    <p class="text-gray-900">{{ $asset->user->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Unit of Measurement</label>
                    <p class="text-gray-900">{{ $asset->asset_uom ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Cost</label>
                    <p class="text-gray-900">RM {{ number_format($asset->asset_cost ?? 0, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Purchase Date</label>
                    <p class="text-gray-900">
                        {{ $asset->asset_purchase_date ? \Carbon\Carbon::parse($asset->asset_purchase_date)->format('d M Y') : '-' }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Invoice Number</label>
                    <p class="text-gray-900">{{ $asset->inv_no ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Venue</label>
                    <p class="text-gray-900">{{ $asset->venue ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Department</label>
                    <p class="text-gray-900">{{ $asset->department->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                    <p>
                        @if ($asset->is_disposed)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Disposed</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Active</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Warranty Information Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Warranty Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Has Warranty</label>
                    <p class="text-gray-900">{{ $asset->has_warranty ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Warranty Period (Months)</label>
                    <p class="text-gray-900">{{ $asset->warranty_period ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Asset Lifespan (Years)</label>
                    <p class="text-gray-900">{{ $asset->asset_lifespan ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Service Interval (Months)</label>
                    <p class="text-gray-900">{{ $asset->asset_service_interval ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Supplier Information Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Supplier Information</h4>
            @if ($asset->supplier)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Supplier Name</label>
                        <p class="text-gray-900">{{ $asset->supplier->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Contact Person</label>
                        <p class="text-gray-900">{{ $asset->supplier->contact_person ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                        <p class="text-gray-900">{{ $asset->supplier->email ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Phone Number</label>
                        <p class="text-gray-900">{{ $asset->supplier->phone_number ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">Address</label>
                        <p class="text-gray-900">{{ $asset->supplier->address ?? '-' }}</p>
                    </div>
                </div>
            @else
                <p class="text-gray-500 italic">No supplier information available</p>
            @endif
        </div>

        {{-- Disposal Information Section --}}
        @if ($asset->is_disposed || $asset->dispose_status)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    Disposal Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Disposal Status</label>
                        <p>
                            @if ($asset->dispose_status === 'pending')
                                <span class="px-3 py-1 text-sm font-medium bg-orange-100 text-orange-700 rounded-full">Pending Approval</span>
                            @elseif ($asset->dispose_status === 'rejected')
                                <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-full">Rejected</span>
                            @elseif ($asset->is_disposed)
                                <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-700 rounded-full">Disposed</span>
                            @endif
                        </p>
                    </div>
                    @if ($asset->dispose_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                {{ $asset->is_disposed ? 'Disposal Date' : 'Request Date' }}
                            </label>
                            <p class="text-gray-900">
                                {{ \Carbon\Carbon::parse($asset->dispose_date)->format('d M Y') }}
                            </p>
                        </div>
                    @endif
                    @if ($asset->dispose_requester)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Requested By</label>
                            <p class="text-gray-900">{{ $asset->dispose_requester->name ?? '-' }}</p>
                        </div>
                    @endif
                    @if ($asset->dispose_approver && $asset->is_disposed)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Approved By</label>
                            <p class="text-gray-900">{{ $asset->dispose_approver->name ?? '-' }}</p>
                        </div>
                    @endif
                    @if ($asset->dispose_remark)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                {{ $asset->is_disposed ? 'Disposal Reason' : 'Request Reason' }}
                            </label>
                            <p class="text-gray-900">{{ $asset->dispose_remark }}</p>
                        </div>
                    @endif
                    @if ($asset->approval_remark)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Approval Remark</label>
                            <p class="text-gray-900">{{ $asset->approval_remark }}</p>
                        </div>
                    @endif
                    @if ($asset->dispose_attachment)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Attachment</label>
                            <a href="{{ asset('storage/' . $asset->dispose_attachment) }}" target="_blank"
                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                    </path>
                                </svg>
                                View Attachment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- History Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                History
            </h4>
            @if ($asset->asset_log)
                <div class="space-y-3">
                    @php
                        $logs = explode("\n", $asset->asset_log);
                        $logs = array_reverse(array_filter($logs)); // Reverse to show latest first
                    @endphp

                    @foreach ($logs as $index => $log)
                        <div class="flex gap-3 pb-3 {{ $index !== count($logs) - 1 ? 'border-b border-gray-100' : '' }}">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-2 h-2 bg-indigo-600 rounded-full"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-700">{{ trim($log) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">No history available</p>
            @endif
        </div>

    </div>
@endsection
