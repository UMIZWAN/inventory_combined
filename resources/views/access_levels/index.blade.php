@extends('layouts.app')

@section('content')
    <div class="px-4">

        {{-- Header Section --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="ml-2 text-xl font-semibold text-gray-800">Access Levels</h3>
                    <p class="ml-2 mt-1 text-sm text-gray-500">Manage access levels and permissions.</p>
                </div>
                @if (auth()->user()->accessLevel?->add_edit_access)
                    <button onclick="openAddModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Access Level
                    </button>
                @endif
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3">
                <table id="accesslevel-table" class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Access Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Permissions Count</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Users Assigned</th>
                            @if (auth()->user()->accessLevel?->add_edit_access)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Order</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($accessLevels as $accessLevel)
                            <tr data-id="{{ $accessLevel->id }}" class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-800">{{ $accessLevel->name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $accessLevel->active_permissions_count }} / {{ count($permissions) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $accessLevel->users_count }} {{ Str::plural('user', $accessLevel->users_count) }}
                                    </span>
                                </td>
                                @if (auth()->user()->accessLevel?->add_edit_access)
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <button onclick="moveRow({{ $accessLevel->id }}, 'up')"
                                                class="p-1 text-gray-400 hover:text-gray-700 rounded hover:bg-gray-100 transition-colors"
                                                title="Move up">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            </button>
                                            <button onclick="moveRow({{ $accessLevel->id }}, 'down')"
                                                class="p-1 text-gray-400 hover:text-gray-700 rounded hover:bg-gray-100 transition-colors"
                                                title="Move down">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button onclick='viewPermissions(@json($accessLevel))'
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </button>
                                        @if (auth()->user()->accessLevel?->add_edit_access)
                                            <button onclick='openEditModal(@json($accessLevel))'
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <form action="{{ route('accessLevels.duplicate', $accessLevel->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-purple-700 bg-purple-50 rounded-md hover:bg-purple-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                    Duplicate
                                                </button>
                                            </form>
                                            <form action="{{ route('accessLevels.destroy', $accessLevel->id) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this access level?')">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->accessLevel?->add_edit_access ? 5 : 4 }}" class="px-4 py-10 text-center text-gray-400">
                                    No access levels found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    <div id="addModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-3xl rounded shadow p-6 mx-4 my-8">
            <h3 class="text-lg font-semibold mb-4">Add Access Level</h3>
            <form action="{{ route('accessLevels.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block font-medium mb-1">Access Level Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="e.g., Admin, Manager, Staff">
                </div>
                <div class="mb-4">
                    <label class="block font-medium mb-3">Permissions</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto border rounded p-4">
                        @foreach ($permissions as $field => $label)
                            <label class="flex items-center space-x-2 hover:bg-gray-50 p-2 rounded cursor-pointer">
                                <input type="checkbox" name="{{ $field }}" value="1"
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 border rounded text-sm hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Create Access Level
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-3xl rounded shadow p-6 mx-4 my-8">
            <h3 class="text-lg font-semibold mb-4">Edit Access Level</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block font-medium mb-1">Access Level Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editName" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block font-medium mb-3">Permissions</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto border rounded p-4"
                        id="editPermissions">
                        @foreach ($permissions as $field => $label)
                            <label class="flex items-center space-x-2 hover:bg-gray-50 p-2 rounded cursor-pointer">
                                <input type="checkbox" name="{{ $field }}" value="1"
                                    id="edit_{{ $field }}"
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border rounded text-sm hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Update Access Level
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- View Permissions Modal --}}
    <div id="viewModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-2xl rounded shadow p-6 mx-4 my-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold" id="viewTitle"></h3>
                <button onclick="closeViewModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-2">Active Permissions:</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2" id="viewPermissionsList">
                    {{-- Populated by JS --}}
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeViewModal()" class="px-4 py-2 border rounded text-sm hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <style>
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
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
        }
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

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.getElementById('addModal').classList.add('flex');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('addModal').classList.remove('flex');
        }

        function openEditModal(accessLevel) {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            form.action = `/access-levels/${accessLevel.id}`;
            document.getElementById('editName').value = accessLevel.name;
            document.querySelectorAll('#editPermissions input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            const permissions = @json(array_keys($permissions));
            permissions.forEach(perm => {
                const checkbox = document.getElementById('edit_' + perm);
                if (checkbox && accessLevel[perm]) {
                    checkbox.checked = true;
                }
            });
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        function viewPermissions(accessLevel) {
            const modal = document.getElementById('viewModal');
            const title = document.getElementById('viewTitle');
            const list = document.getElementById('viewPermissionsList');
            title.textContent = accessLevel.name;
            const permissionLabels = @json($permissions);
            const permissions = @json(array_keys($permissions));
            let html = '';
            let activeCount = 0;
            permissions.forEach(perm => {
                if (accessLevel[perm]) {
                    activeCount++;
                    html += `
                        <div class="flex items-center space-x-2 p-2 bg-green-50 rounded">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm">${permissionLabels[perm]}</span>
                        </div>
                    `;
                }
            });
            if (activeCount === 0) {
                html = '<div class="col-span-2 text-center text-gray-500 py-4">No permissions assigned</div>';
            }
            list.innerHTML = html;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
            document.getElementById('viewModal').classList.remove('flex');
        }

        // Close modals on outside click
        ['addModal', 'editModal', 'viewModal'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                    this.classList.remove('flex');
                }
            });
        });

        $(function() {
            $('#accesslevel-table').DataTable({
                pageLength: 25,
                order: [],
                columnDefs: [
                    { orderable: false, targets: -1 },
                    @if(auth()->user()->accessLevel?->add_edit_access)
                    { orderable: false, targets: -2 },
                    @endif
                ],
                language: {
                    search: "Search Access Level:",
                    lengthMenu: "Show _MENU_ entries"
                }
            });
        });

        function moveRow(id, direction) {
            const table = $('#accesslevel-table').DataTable();
            const rows = table.rows({ order: 'current' }).nodes();
            const ids = Array.from(rows).map(row => row.getAttribute('data-id'));
            const index = ids.indexOf(String(id));

            if (direction === 'up' && index > 0) {
                [ids[index], ids[index - 1]] = [ids[index - 1], ids[index]];
            } else if (direction === 'down' && index < ids.length - 1) {
                [ids[index], ids[index + 1]] = [ids[index + 1], ids[index]];
            } else {
                return;
            }

            fetch('{{ route("accessLevels.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(r => r.json())
            .then(data => { if (data.success) window.location.reload(); });
        }
    </script>
@endsection
