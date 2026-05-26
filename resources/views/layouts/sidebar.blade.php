<div
    class="w-64 bg-gradient-to-b from-slate-50 to-white p-3 shadow-lg h-full overflow-y-auto text-sm border-r border-gray-200">

    {{-- Manage Assets Section --}}
    <div class="border-b border-gray-300 mt-4 mb-3 pb-2">
        <label class="text-gray-800 font-bold text-xs uppercase tracking-wider px-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Manage Assets
        </label>
    </div>

    <ul class="space-y-1 mb-4">

        <li>
            <a href="/asset"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
       {{ request()->is('asset') || request()->is('asset/*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-indigo-50' }}">

                {{-- Asset / All Listing Icon --}}
                <svg class="w-5 h-5
            {{ request()->is('asset') || request()->is('asset/*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>

                <span class="font-medium">Asset Listing</span>
            </a>
        </li>


        {{-- <li>
            <a href="/asset"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
   {{ request()->is('asset') && !request()->is('asset/pending-approval*')
       ? 'bg-indigo-100 text-indigo-700'
       : 'text-gray-700 hover:bg-indigo-50' }}">

                <svg class="w-5 h-5
        {{ request()->is('asset') && !request()->is('asset/pending-approval*')
            ? 'text-indigo-600'
            : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 21h16M6 21V5a2 2 0 012-2h8a2 2 0 012 2v16M9 9h.01M9 13h.01M15 9h.01M15 13h.01" />
                </svg>

                <span class="font-medium">Branch Listing</span>
            </a>
        </li> --}}

        {{-- Pending Approvals - hidden (approval workflow bypassed) --}}

        <li>
            @php
                $sidebarUser = auth()->user();
                $sidebarBranchIds = $sidebarUser?->branches()->pluck('branches.id')->toArray() ?? [];
                $sidebarPending = $sidebarUser?->accessLevel?->approve_disaprove_transfer
                    ? \App\Models\AssetTransfer::where('transfer_status', 'pending')
                        ->where(function ($q) use ($sidebarBranchIds) {
                            $q->whereIn('transfer_from', $sidebarBranchIds)
                              ->orWhereIn('transfer_to', $sidebarBranchIds);
                        })->count()
                    : 0;
                $sidebarToSend = \App\Models\AssetTransfer::where('transfer_status', 'approved')
                    ->whereIn('transfer_from', $sidebarBranchIds)->count();
                $sidebarToReceive = \App\Models\AssetTransfer::whereIn('transfer_status', ['in_transit', 'partial_received'])
                    ->whereIn('transfer_to', $sidebarBranchIds)->count();
                $sidebarTotal = $sidebarPending + $sidebarToSend + $sidebarToReceive;
            @endphp
            <a href="/asset-transfer"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group relative {{ request()->is('asset-transfer*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('asset-transfer*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span class="font-medium">Asset Transfer</span>
                @if($sidebarTotal > 0)
                    <span class="absolute right-3 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">
                        {{ $sidebarTotal > 99 ? '99+' : $sidebarTotal }}
                    </span>
                @endif
            </a>
        </li>

        <li>
            <a href="/ams-forms"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('ams-forms*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('ams-forms*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <span class="font-medium">Form Download</span>
            </a>
        </li>
    </ul>


    {{-- Reports Section --}}
    <div class="border-b border-gray-300 mt-4 mb-3 pb-2">
        <label class="text-gray-800 font-bold text-xs uppercase tracking-wider px-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Reports
        </label>
    </div>

    <ul class="space-y-1 mb-4">
        <li>
            <a href="/report"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('report*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('report*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="font-medium">Report</span>
            </a>
        </li>
    </ul>


    {{-- Settings Section --}}
    <div class="border-b border-gray-300 mt-2 mb-3 pb-2">
        <label class="text-gray-800 font-bold text-xs uppercase tracking-wider px-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                </path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Settings
        </label>
    </div>

    <ul class="space-y-1 mb-4">

        <li>
            <a href="/access-levels"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('access-levels*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('access-levels*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                    </path>
                </svg>
                <span class="font-medium">Access Levels</span>
            </a>
        </li>



        <li>
            <a href="/staff"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('staff*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('staff*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                <span class="font-medium">Users</span>
            </a>
        </li>



        {{-- PIC link hidden - PIC is now a simple string field --}}



        <li>
            <a href="/branches"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('branches*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('branches*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                <span class="font-medium">Branches</span>
            </a>
        </li>

        <li>
            <a href="/departments"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('departments*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('departments*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                    </path>
                </svg>
                <span class="font-medium">Departments</span>
            </a>
        </li>

        <li>
            <a href="/assetgroup"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('assetgroup*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('assetgroup*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                    </path>
                </svg>
                <span class="font-medium">Asset Grouping</span>
            </a>
        </li>



        <li>
            <a href="/supplier"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group {{ request()->is('supplier*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700' }}">
                <svg class="w-5 h-5 {{ request()->is('supplier*') ? 'text-indigo-600' : 'text-gray-500 group-hover:text-indigo-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                    </path>
                </svg>
                <span class="font-medium">Suppliers</span>
            </a>
        </li>



        <li>
            <a href="{{ route('import.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-indigo-50 transition-all duration-200 group text-gray-700">
                <svg class="w-5 h-5 text-gray-500 group-hover:text-indigo-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                    </path>
                </svg>
                <span class="font-medium">Import CSV</span>
            </a>
        </li>

    </ul>
</div>
