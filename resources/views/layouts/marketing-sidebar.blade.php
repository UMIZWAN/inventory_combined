<div
    class="w-64 bg-gradient-to-b from-slate-50 to-white p-3 shadow-lg h-full overflow-y-auto text-sm border-r border-gray-200">

    {{-- Marketing Section --}}
    <div class="border-b border-gray-300 mt-4 mb-3 pb-2">
        <label class="text-gray-800 font-bold text-xs uppercase tracking-wider px-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Marketing Inventory
        </label>
    </div>

    @php
        $marketingPerms = auth()->user()?->marketingAccessLevel;
    @endphp

    <ul class="space-y-1 mb-4">

        {{-- Items --}}
        @if ($marketingPerms?->view_asset || $marketingPerms?->view_asset_masterlist)
            <li>
                <a href="/marketing"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
           {{ request()->is('marketing') || request()->is('marketing/items*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                    <svg class="w-5 h-5 {{ request()->is('marketing') || request()->is('marketing/items*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="font-medium">Items</span>
                </a>
            </li>
        @endif

        {{-- Purchase Orders --}}
        @if ($marketingPerms?->view_purchase_order)
            <li>
                <a href="/marketing/purchase-orders"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
           {{ request()->is('marketing/purchase-orders*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                    <svg class="w-5 h-5 {{ request()->is('marketing/purchase-orders*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-medium">Purchase Orders</span>
                </a>
            </li>
        @endif

        {{-- Transactions --}}
        @if ($marketingPerms?->view_transaction)
            <li>
                <a href="/marketing/transactions"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
           {{ request()->is('marketing/transactions*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                    <svg class="w-5 h-5 {{ request()->is('marketing/transactions*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="font-medium">Transactions</span>
                </a>
            </li>
        @endif
    </ul>

    {{-- Manage Report Section --}}
    @if ($marketingPerms?->view_reports || $marketingPerms?->download_reports)
        <div class="border-b border-gray-300 mt-6 mb-3 pb-2">
            <label class="text-gray-800 font-bold text-xs uppercase tracking-wider px-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-7 0h8m-9 4h10a2 2 0 002-2V7a2 2 0 00-2-2h-3l-2-2H8l-2 2H3v12a2 2 0 002 2z" />
                </svg>
                Manage Report
            </label>
        </div>

        <ul class="space-y-1 mb-4">
            <li>
                <a href="/marketing/reports/in-out-history"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
           {{ request()->is('marketing/reports/in-out-history*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                    <svg class="w-5 h-5 {{ request()->is('marketing/reports/in-out-history*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span class="font-medium">IN-OUT History</span>
                </a>
            </li>

            <li>
                <a href="/marketing/reports/invoice"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
           {{ request()->is('marketing/reports/invoice*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                    <svg class="w-5 h-5 {{ request()->is('marketing/reports/invoice*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-medium">Invoice</span>
                </a>
            </li>
        </ul>
    @endif

    {{-- Settings Section --}}
    @if ($marketingPerms?->settings)
        <div class="border-b border-gray-300 mt-6 mb-3 pb-2">
            <label class="text-gray-800 font-bold text-xs uppercase tracking-wider px-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </label>
        </div>

        <ul class="space-y-1">
            @if ($marketingPerms?->view_user)
                <li>
                    <a href="/marketing/staff"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
               {{ request()->is('marketing/staff*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                        <svg class="w-5 h-5 {{ request()->is('marketing/staff*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="font-medium">Users</span>
                    </a>
                </li>
            @endif

            @if ($marketingPerms?->view_role)
                <li>
                    <a href="/marketing/access-levels"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
               {{ request()->is('marketing/access-levels*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                        <svg class="w-5 h-5 {{ request()->is('marketing/access-levels*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span class="font-medium">Access Levels</span>
                    </a>
                </li>
            @endif

            @if ($marketingPerms?->view_branch)
                <li>
                    <a href="/marketing/branches"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
               {{ request()->is('marketing/branches*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                        <svg class="w-5 h-5 {{ request()->is('marketing/branches*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="font-medium">Branches</span>
                    </a>
                </li>
            @endif

            @if ($marketingPerms?->view_asset || $marketingPerms?->add_edit_asset)
                <li>
                    <a href="/marketing/categories"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
               {{ request()->is('marketing/categories*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                        <svg class="w-5 h-5 {{ request()->is('marketing/categories*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span class="font-medium">Categories</span>
                    </a>
                </li>
            @endif

            @if ($marketingPerms?->view_transaction || $marketingPerms?->add_edit_transaction)
                <li>
                    <a href="/marketing/transaction-purposes"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
               {{ request()->is('marketing/transaction-purposes*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                        <svg class="w-5 h-5 {{ request()->is('marketing/transaction-purposes*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span class="font-medium">Transaction Purposes</span>
                    </a>
                </li>
            @endif

            @if ($marketingPerms?->view_transaction || $marketingPerms?->add_edit_transaction)
                <li>
                    <a href="/marketing/shipping-options"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
               {{ request()->is('marketing/shipping-options*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                        <svg class="w-5 h-5 {{ request()->is('marketing/shipping-options*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1" />
                        </svg>
                        <span class="font-medium">Shipping Options</span>
                    </a>
                </li>
            @endif

            @if ($marketingPerms?->view_supplier)
                <li>
                    <a href="/supplier"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
               {{ request()->is('supplier*') ? 'bg-emerald-100 text-emerald-700' : 'text-gray-700 hover:bg-emerald-50' }}">
                        <svg class="w-5 h-5 {{ request()->is('supplier*') ? 'text-emerald-600' : 'text-gray-500 group-hover:text-emerald-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="font-medium">Suppliers</span>
                    </a>
                </li>
            @endif
        </ul>
    @endif
</div>
