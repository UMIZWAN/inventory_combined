<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-200">
    <div class="min-h-screen flex flex-col">
        {{-- Top bar --}}
        <header class="flex justify-between items-center px-6 py-4">
            <div class="text-sm text-gray-600">
                Welcome, <span class="font-semibold text-gray-900">{{ auth()->user()->name ?? 'Guest' }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Logout
                </button>
            </form>
        </header>

        {{-- Centered content --}}
        <main class="flex-1 flex flex-col items-center justify-center px-6">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Inventory System</h1>
            <p class="text-gray-500 mb-12">Select a system to continue</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-3xl">
                {{-- AIS Card --}}
                @if (auth()->user()->asset_access_level_id)
                    <a href="{{ url('/asset') }}"
                        class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-8 hover:shadow-lg hover:border-indigo-300 transition-all">
                        <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-indigo-600 transition-colors">
                            <svg class="w-7 h-7 text-indigo-600 group-hover:text-white transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-1">AIS</h2>
                        <p class="text-sm text-gray-500 mb-3">Asset Inventory System</p>
                        <p class="text-xs text-gray-400">Manage assets, transfers, and disposals.</p>
                    </a>
                @else
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 opacity-50 cursor-not-allowed">
                        <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-400 mb-1">AIS</h2>
                        <p class="text-sm text-gray-400 mb-3">Asset Inventory System</p>
                        <p class="text-xs text-gray-400">No access assigned.</p>
                    </div>
                @endif

                {{-- MIS Card --}}
                @if (auth()->user()->marketing_access_level_id)
                    <a href="{{ url('/marketing') }}"
                        class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-8 hover:shadow-lg hover:border-emerald-300 transition-all">
                        <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-600 transition-colors">
                            <svg class="w-7 h-7 text-emerald-600 group-hover:text-white transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-1">MIS</h2>
                        <p class="text-sm text-gray-500 mb-3">Marketing Inventory System</p>
                        <p class="text-xs text-gray-400">Manage marketing items, POs, and transactions.</p>
                    </a>
                @else
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 opacity-50 cursor-not-allowed">
                        <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-400 mb-1">MIS</h2>
                        <p class="text-sm text-gray-400 mb-3">Marketing Inventory System</p>
                        <p class="text-xs text-gray-400">No access assigned.</p>
                    </div>
                @endif
            </div>
        </main>
    </div>
</body>

</html>
