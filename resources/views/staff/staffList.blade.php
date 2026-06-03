<!-- resources/views/staff/staffList.blade.php -->

@php
    $module ??= 'asset';

    if ($module === 'marketing') {
        $accent             = 'emerald';
        $accentHex          = '#059669';
        $accentBgLight      = '#ecfdf5';
        $accentHexLight     = '#a7f3d0';
        $accentRing         = 'rgba(16, 185, 129, 0.1)';
        $canEdit            = auth()->user()?->marketingAccessLevel?->add_edit_user;
        $accessColumn       = 'marketing_access_level_id';
        $accessRelation     = 'marketingAccessLevel';
        $routes = [
            'register'        => 'marketingStaff.register',
            'registerNoLogin' => 'marketingStaff.registerNoLogin',
            'toggleStatus'    => 'marketingStaff.toggleStatus',
        ];
        $basePath           = '/marketing/staff';
        $profileLinkUrl     = null; // no per-user listing on marketing side yet
    } else {
        $accent             = 'indigo';
        $accentHex          = '#4f46e5';
        $accentBgLight      = '#eef2ff';
        $accentHexLight     = '#c7d2fe';
        $accentRing         = 'rgba(129, 140, 248, 0.1)';
        $canEdit            = auth()->user()?->accessLevel?->add_edit_user;
        $accessColumn       = 'asset_access_level_id';
        $accessRelation     = 'accessLevel';
        $routes = [
            'register'        => 'staff.register',
            'registerNoLogin' => 'staff.registerNoLogin',
            'toggleStatus'    => 'staff.toggleStatus',
        ];
        $basePath           = '/staff';
        $profileLinkUrl     = url('/asset');
    }
@endphp

@extends('layouts.app', ['module' => $module])

@section('content')
    <div class="px-4">

        {{-- Header Section --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="ml-2 text-xl font-semibold text-gray-800">User</h3>
                    <p class="ml-2 mt-1 text-sm text-gray-500">Manage staff accounts and access.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button id="export-csv-btn"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export CSV
                    </button>
                    @if ($canEdit)
                        <button id="add-nologin-trigger"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Staff (No Login)
                        </button>
                        <button id="add-staff-trigger"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-{{ $accent }}-600 text-white text-sm font-medium rounded-lg hover:bg-{{ $accent }}-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Staff
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3">
                <table id="staff-table" class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Branches</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Access</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    @if ($profileLinkUrl)
                                        <a href="{{ $profileLinkUrl }}?user_id={{ $user->id }}"
                                            class="font-medium text-{{ $accent }}-600 hover:text-{{ $accent }}-800 hover:underline">{{ $user->name }}</a>
                                    @else
                                        <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-gray-600">{{ $user->username }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->branches->isNotEmpty())
                                        @php
                                            $branchNames = $user->branches->pluck('branch_name')->join(', ');
                                        @endphp
                                        <div class="truncate max-w-[200px]" title="{{ $branchNames }}">
                                            <span class="text-gray-600">{{ $branchNames }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No Branch</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-gray-700">{{ $user->{$accessRelation}?->name ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($canEdit)
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                class="edit-staff-btn inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-{{ $accent }}-700 bg-{{ $accent }}-50 rounded-md hover:bg-{{ $accent }}-100 transition-colors"
                                                data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                data-username="{{ $user->username }}"
                                                data-branches="{{ $user->branches->pluck('id')->join(',') }}"
                                                data-access-level="{{ $user->{$accessColumn} ?? '' }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>

                                            <form method="POST" action="{{ route($routes['toggleStatus'], $user->id) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $user->is_active ? 'text-red-700 bg-red-50 hover:bg-red-100' : 'text-green-700 bg-green-50 hover:bg-green-100' }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        @if($user->is_active)
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        @endif
                                                    </svg>
                                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No access</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-400">No staff found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Staff (No Login) Modal -->
    <div id="nologin-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 id="nologin-modal-title" class="text-2xl font-bold text-gray-800">Add Staff (No Login)</h2>
                        <p class="text-sm text-gray-500 mt-1">Staff record without system login credentials.</p>
                    </div>
                    <button type="button" id="close-nologin-modal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form id="nologin-form" method="POST" action="{{ route($routes['registerNoLogin']) }}">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="nologin-name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branches</label>
                            <input type="text" id="nologin-branch-search" placeholder="Search branches..."
                                class="w-full mb-3 px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500 text-sm">
                            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                                @foreach ($branches as $branch)
                                    <label class="nologin-branch-item flex items-center hover:bg-gray-50 px-2 py-1 rounded cursor-pointer">
                                        <input type="checkbox" name="branch_id[]" value="{{ $branch->id }}"
                                            class="mr-3 text-{{ $accent }}-600 focus:ring-{{ $accent }}-500 rounded">
                                        <span class="text-sm">{{ $branch->branch_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" id="cancel-nologin-modal"
                            class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" id="nologin-submit-btn"
                            class="px-6 py-2 bg-gray-600 text-white font-medium rounded-md hover:bg-gray-700">
                            Add Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Staff Modal -->
    <div id="staff-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="staff-modal-title" class="text-2xl font-bold text-gray-800">Add New Staff</h2>
                    <button type="button" id="close-staff-modal"
                        class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form id="staff-form" method="POST" action="{{ route($routes['register']) }}">
                    @csrf
                    <input type="hidden" name="staff_id" id="staff-id">

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" id="staff-name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" name="username" id="staff-username" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" id="staff-password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500">
                            <p class="text-xs text-gray-400 mt-1">Leave blank if you don't want to change password.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Access Level</label>
                            <select name="{{ $accessColumn }}" id="staff-access-level" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500">
                                <option value="">Select Access Level</option>
                                @foreach ($accessLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branches</label>
                            <input type="text" id="branch-search" placeholder="Search branches..."
                                class="w-full mb-3 px-4 py-2 border border-gray-300 rounded-md focus:ring-{{ $accent }}-500 focus:border-{{ $accent }}-500 text-sm">
                            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                                @foreach ($branches as $branch)
                                    <label
                                        class="branch-item flex items-center hover:bg-gray-50 px-2 py-1 rounded cursor-pointer">
                                        <input type="checkbox" name="branch_id[]" value="{{ $branch->id }}"
                                            class="mr-3 text-{{ $accent }}-600 focus:ring-{{ $accent }}-500 rounded">
                                        <span class="text-sm">{{ $branch->branch_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Select multiple branches.</p>

                            <!-- Selected branches display -->
                            <div id="selected-branches" class="mt-3 text-sm text-{{ $accent }}-600 hidden">
                                Selected: <span id="selected-branches-list"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" id="cancel-staff-modal"
                            class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" id="staff-modal-submit"
                            class="px-6 py-2 bg-{{ $accent }}-600 text-white font-medium rounded-md hover:bg-{{ $accent }}-700">
                            Add Staff
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <style>
        .dataTables_wrapper .dataTables_length { margin-bottom: 1rem; }
        .dataTables_wrapper .dataTables_length label {
            font-size: 0.875rem; font-weight: 600; color: #4b5563;
            display: flex; align-items: center; gap: 0rem;
        }
        .dataTables_wrapper .dataTables_length select {
            margin: 0 0.5rem; padding: 0.5rem 2rem 0.5rem 0.75rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem;
            font-size: 0.875rem; transition: all 0.2s; outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat; background-size: 1.25rem; appearance: none;
        }
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: {{ $accentHexLight }};
            box-shadow: 0 0 0 3px {{ $accentRing }};
        }
        .dataTables_wrapper .dataTables_filter { margin-bottom: 1rem; }
        .dataTables_wrapper .dataTables_filter label {
            font-size: 0.875rem; font-weight: 600; color: #4b5563;
            display: flex; align-items: center; gap: 0.75rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0; padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem;
            font-size: 0.875rem; transition: all 0.2s; outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: {{ $accentHexLight }};
            box-shadow: 0 0 0 3px {{ $accentRing }};
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem; color: #6b7280; padding-top: 1rem;
        }
        .dataTables_wrapper .dataTables_paginate { padding-top: 1rem; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem; margin: 0 0.125rem;
            border-radius: 0.375rem; font-size: 0.875rem; transition: all 0.2s;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: {{ $accentHex }} !important;
            color: white !important;
            border: 1px solid {{ $accentHex }} !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: {{ $accentBgLight }} !important;
            color: {{ $accentHex }} !important;
            border: 1px solid {{ $accentHexLight }} !important;
        }
    </style>

    <script>
        const staffBasePath = @json($basePath);

        const staffModal = document.getElementById('staff-modal');
        const openAddBtn = document.getElementById('add-staff-trigger');
        const closeModalBtns = [document.getElementById('close-staff-modal'), document.getElementById(
            'cancel-staff-modal')];
        const modalTitle = document.getElementById('staff-modal-title');
        const staffForm = document.getElementById('staff-form');
        const submitBtn = document.getElementById('staff-modal-submit');

        const branchCheckboxes = document.querySelectorAll('input[name="branch_id[]"]');
        const selectedDisplay = document.getElementById('selected-branches');
        const selectedList = document.getElementById('selected-branches-list');

        // Branch Search Functionality
        const branchSearch = document.getElementById('branch-search');
        const branchItems = document.querySelectorAll('.branch-item');

        branchSearch.addEventListener('input', () => {
            const term = branchSearch.value.toLowerCase();
            branchItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(term) ? '' : 'none';
            });
        });

        function updateSelectedBranches() {
            const selected = Array.from(branchCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.nextElementSibling.textContent.trim());

            if (selected.length > 0) {
                selectedList.textContent = selected.join(', ');
                selectedDisplay.classList.remove('hidden');
            } else {
                selectedDisplay.classList.add('hidden');
            }
        }

        branchCheckboxes.forEach(cb => cb.addEventListener('change', updateSelectedBranches));

        // Open Add Modal - Only add listener if button exists (user has permission)
        if (openAddBtn) {
            openAddBtn.addEventListener('click', () => {
                staffForm.reset();
                staffForm.action = "{{ route($routes['register']) }}";
                modalTitle.textContent = "Add New Staff";
                submitBtn.textContent = "Add Staff";
                branchSearch.value = ''; // Clear search
                branchItems.forEach(item => item.style.display = ''); // Show all branches
                updateSelectedBranches();
                staffModal.classList.remove('hidden');
            });
        }

        // Close Modal
        closeModalBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => staffModal.classList.add('hidden'));
            }
        });
        staffModal.addEventListener('click', (e) => {
            if (e.target === staffModal) staffModal.classList.add('hidden');
        });

        // Edit Buttons
        document.querySelectorAll('.edit-staff-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name;
                const username = btn.dataset.username;
                const branches = btn.dataset.branches ? btn.dataset.branches.split(',') : [];
                const accessLevel = btn.dataset.accessLevel;

                // No-login staff (no username) → open no-login modal
                if (!username) {
                    document.getElementById('nologin-form').reset();
                    document.getElementById('nologin-form').action = `${staffBasePath}/${id}/update-no-login`;
                    document.getElementById('nologin-modal-title').textContent = 'Edit Staff (No Login)';
                    document.getElementById('nologin-submit-btn').textContent = 'Update Staff';
                    document.getElementById('nologin-name').value = name;
                    noLoginBranchItems.forEach(item => {
                        const cb = item.querySelector('input[type="checkbox"]');
                        cb.checked = branches.includes(cb.value);
                    });
                    noLoginBranchSearch.value = '';
                    noLoginBranchItems.forEach(item => item.style.display = '');
                    noLoginModal.classList.remove('hidden');
                    return;
                }

                staffForm.reset();
                staffForm.action = `${staffBasePath}/${id}/update`;
                modalTitle.textContent = "Edit Staff";
                submitBtn.textContent = "Update Staff";

                document.getElementById('staff-id').value = id;
                document.getElementById('staff-name').value = name;
                document.getElementById('staff-username').value = username;
                document.getElementById('staff-access-level').value = accessLevel ?? '';

                branchCheckboxes.forEach(cb => {
                    cb.checked = branches.includes(cb.value);
                });

                branchSearch.value = '';
                branchItems.forEach(item => item.style.display = '');
                updateSelectedBranches();

                staffModal.classList.remove('hidden');
            });
        });

        // No Login Modal
        const noLoginModal = document.getElementById('nologin-modal');
        const noLoginBranchSearch = document.getElementById('nologin-branch-search');
        const noLoginBranchItems = document.querySelectorAll('.nologin-branch-item');

        document.getElementById('add-nologin-trigger')?.addEventListener('click', () => {
            const form = document.getElementById('nologin-form');
            form.reset();
            form.action = "{{ route($routes['registerNoLogin']) }}";
            document.getElementById('nologin-modal-title').textContent = 'Add Staff (No Login)';
            document.getElementById('nologin-submit-btn').textContent = 'Add Staff';
            noLoginBranchSearch.value = '';
            noLoginBranchItems.forEach(item => item.style.display = '');
            noLoginModal.classList.remove('hidden');
        });

        [document.getElementById('close-nologin-modal'), document.getElementById('cancel-nologin-modal')].forEach(btn => {
            btn?.addEventListener('click', () => noLoginModal.classList.add('hidden'));
        });

        noLoginModal.addEventListener('click', e => {
            if (e.target === noLoginModal) noLoginModal.classList.add('hidden');
        });

        noLoginBranchSearch.addEventListener('input', () => {
            const term = noLoginBranchSearch.value.toLowerCase();
            noLoginBranchItems.forEach(item => {
                item.style.display = item.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });

        // DataTables
        $(function() {
            $('#staff-table').DataTable({
                pageLength: 25,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "Search staff:",
                    lengthMenu: "Show _MENU_ entries",
                    emptyTable: "No staff found."
                }
            });
        });

        // Export CSV functionality
        document.getElementById('export-csv-btn').addEventListener('click', function() {
            // Get table data
            const table = document.getElementById('staff-table');
            const rows = table.querySelectorAll('tbody tr');

            // CSV header
            let csv = ['Name,Username,Branches,Access Level,Status'];

            // Get data from each row
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 5) {
                    const name = cells[0].textContent.trim().replace(/,/g, ';');
                    const username = cells[1].textContent.trim().replace(/,/g, ';');
                    const branches = cells[2].textContent.trim().replace(/,/g, ';').replace(/\s+/g, ' ');
                    const access = cells[3].textContent.trim().replace(/,/g, ';');
                    const status = cells[4].textContent.trim().replace(/,/g, ';');

                    csv.push(`"${name}","${username}","${branches}","${access}","${status}"`);
                }
            });

            // Create and download CSV file
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', 'staff_list_' + new Date().toISOString().slice(0,10) + '.csv');
            link.style.visibility = 'hidden';

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
@endsection
