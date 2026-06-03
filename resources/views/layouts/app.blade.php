@php
    $module ??= 'asset';
    $moduleConfig = [
        'asset' => [
            'title'         => 'AMS - Asset Management System',
            'brand'         => 'ASSET MANAGEMENT SYSTEM',
            'sidebar'       => 'layouts.asset-sidebar',
            'accent'        => 'indigo',
            'home_url'      => '/asset',
        ],
        'marketing' => [
            'title'         => 'MIS - Marketing Inventory System',
            'brand'         => 'MARKETING INVENTORY SYSTEM',
            'sidebar'       => 'layouts.marketing-sidebar',
            'accent'        => 'emerald',
            'home_url'      => '/marketing',
        ],
    ][$module];

    $accent = $moduleConfig['accent'];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $moduleConfig['title'] }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        /* DataTables Tailwind-like styling */
        .dataTables_wrapper .dataTables_length select {
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: white;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.375rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin-left: 0.5rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            margin: 0 0.125rem;
            background: white;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: {{ $accent === 'emerald' ? '#059669' : '#4f46e5' }} !important;
            color: white !important;
            border-color: {{ $accent === 'emerald' ? '#059669' : '#4f46e5' }};
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background: #f3f4f6 !important;
            border-color: #d1d5db;
            color: #1f2937 !important;
        }
        .dataTables_wrapper .dataTables_info {
            padding-top: 0.75rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        table.dataTable thead th {
            border-bottom: 2px solid #e5e7eb;
            padding: 0.75rem 1rem;
        }
        table.dataTable tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        table.dataTable tbody tr:hover {
            background-color: #f9fafb !important;
        }
    </style>

    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
</head>

<body class="h-screen overflow-hidden bg-gray-100">

    <div class="flex flex-col h-screen">

        {{-- ================= TOPBAR ================= --}}
        <header
            class="bg-gradient-to-r from-slate-50 to-white px-6 py-3.5 shadow-lg flex justify-between items-center border-b border-gray-200">
            <div class="flex items-center gap-3">
                <a href="/" title="Back to Home"
                    class="text-gray-500 hover:text-{{ $accent }}-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </a>
                <a href="{{ $moduleConfig['home_url'] }}"
                    class="text-l font-bold text-gray-800 hover:text-{{ $accent }}-600 transition-colors">
                    {{ $moduleConfig['brand'] }}
                </a>
            </div>

            <div id="current-time" class="text-sm text-gray-600 font-medium">
                <span id="time-display"></span>
            </div>

            @auth
                <div class="flex items-center gap-3">

                    {{-- Profile Trigger --}}
                    <button id="profile-trigger"
                        class="text-sm text-gray-700 hover:text-{{ $accent }}-600 font-medium transition-colors">
                        {{ auth()->user()->name }}
                    </button>


                    {{-- Profile Modal --}}
                    <div id="profile-modal"
                        class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h2 class="text-2xl font-bold text-gray-800">Edit Profile</h2>
                                    <button type="button" id="close-modal"
                                        class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                                </div>

                                <form method="POST" action="{{ route('profile.update') }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="space-y-5">

                                        {{-- Name --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                            <input type="text" name="name"
                                                value="{{ old('name', auth()->user()->name) }}"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500"
                                                required>
                                        </div>

                                        {{-- Username --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                            <input type="text" value="{{ auth()->user()->username }}"
                                                class="w-full px-4 py-2 bg-gray-100 rounded-md" disabled>
                                        </div>

                                        {{-- Asset Access Level --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Asset Access Level</label>
                                            <input type="text" value="{{ auth()->user()->accessLevel->name ?? 'None' }}"
                                                class="w-full px-4 py-2 bg-gray-100 rounded-md" disabled>
                                        </div>

                                        {{-- Marketing Access Level --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Marketing Access Level</label>
                                            <input type="text" value="{{ auth()->user()->marketingAccessLevel->name ?? 'None' }}"
                                                class="w-full px-4 py-2 bg-gray-100 rounded-md" disabled>
                                        </div>

                                        {{-- New Password --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                New Password (leave blank to keep current)
                                            </label>
                                            <input type="password" name="password"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                        </div>

                                        {{-- Confirm Password --}}
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Confirm Password
                                            </label>
                                            <input type="password" name="password_confirmation"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                        </div>

                                        <div class="pt-4 flex justify-end gap-3">
                                            <button type="button" id="close-modal-bottom"
                                                class="px-5 py-2 border rounded-md text-gray-700 hover:bg-gray-100">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-5 py-2 bg-{{ $accent }}-600 text-white rounded-md hover:bg-{{ $accent }}-700">
                                                Update Profile
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 transition-colors" title="Logout">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                            </svg>
                        </button>
                    </form>

                </div>
            @endauth
        </header>

        {{-- ================= MAIN LAYOUT ================= --}}
        <div class="flex flex-1 overflow-hidden">

            {{-- Sidebar --}}
            <aside class="w-64 bg-gray-100">
                <div class="px-0 py-0">
                    @include($moduleConfig['sidebar'])
                </div>
            </aside>

            {{-- Content --}}
            <main class="flex-1 p-6 overflow-y-auto bg-gray-100">

                {{-- ===== FLASH MESSAGES ===== --}}
                <div id="flash-messages">
                    @if (session('success'))
                        <div
                            class="mb-4 px-4 py-3 rounded-md bg-green-100 border border-green-400 text-green-800 fade-out">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 px-4 py-3 rounded-md bg-red-100 border border-red-400 text-red-800 fade-out">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>

                @if ($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-md bg-red-100 border border-red-400 text-red-800">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    {{-- ================= MODAL SCRIPT ================= --}}
    <script>
        const modal = document.getElementById('profile-modal');

        document.getElementById('profile-trigger').addEventListener('click', e => {
            e.preventDefault();
            modal.classList.remove('hidden');
        });

        document.querySelectorAll('.fade-out').forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }, 2000);
        });

        document.getElementById('close-modal').addEventListener('click', closeModal);
        document.getElementById('close-modal-bottom').addEventListener('click', closeModal);

        modal.addEventListener('click', e => {
            if (e.target === modal) closeModal();
        });

        function closeModal() {
            modal.classList.add('hidden');
        }

        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateStr = now.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            document.getElementById('time-display').textContent = `${dateStr} • ${timeStr}`;
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>

</body>

</html>
