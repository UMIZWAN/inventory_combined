@extends('layouts.app')

@section('content')
    <div class="px-4">

        {{-- Header Section --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="ml-2 text-xl font-semibold text-gray-800">Departments</h3>
                    <p class="ml-2 mt-1 text-sm text-gray-500">Manage department list.</p>
                </div>
                @if (auth()->user()->accessLevel?->add_edit_department)
                    <button id="add-dept-trigger"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Department
                    </button>
                @endif
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3">
                <table id="dept-table" class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Department Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($departments as $department)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-800">{{ $department->name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if (auth()->user()->accessLevel?->add_edit_department)
                                        <div class="flex items-center gap-2">
                                            <button
                                                class="edit-dept-btn inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors"
                                                data-id="{{ $department->id }}" data-name="{{ $department->name }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                                Edit
                                            </button>

                                            <form method="POST" action="{{ url('/departments/' . $department->id) }}"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this department?')"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No access</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= MODAL ================= --}}
    <div id="dept-modal" class="fixed inset-0 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
            <div class="p-6">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                                </path>
                            </svg>
                        </div>
                        <h2 id="dept-modal-title" class="text-xl font-bold text-gray-900">Add Department</h2>
                    </div>
                    <button id="close-dept-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form id="dept-form" method="POST" action="{{ url('/departments') }}">
                    @csrf
                    @method('POST')

                    <input type="hidden" id="dept-id">

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Department Name <span
                                    class="text-red-500">*</span></label>
                            <input name="name" id="dept-name" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                placeholder="Enter department name">
                        </div>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200">
                        <button type="button" id="cancel-dept-modal"
                            class="px-5 py-2.5 text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="dept-submit"
                            class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                            Add Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= DEPENDENCIES ================= --}}
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
            gap: 0 rem;
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
            ring: 2px;
            ring-color: #818cf8;
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
        const modal = document.getElementById('dept-modal');
        const openBtn = document.getElementById('add-dept-trigger');
        const closeBtns = [
            document.getElementById('close-dept-modal'),
            document.getElementById('cancel-dept-modal')
        ];

        const form = document.getElementById('dept-form');
        const title = document.getElementById('dept-modal-title');
        const submitBtn = document.getElementById('dept-submit');

        // Add
        if (openBtn) {
            openBtn.addEventListener('click', () => {
                form.reset();
                form.action = '/departments';
                title.textContent = 'Add Department';
                submitBtn.textContent = 'Add Department';
                // Remove any spoofed PUT method
                const methodInput = form.querySelector('input[name="_method"][value="PUT"]');
                if (methodInput) methodInput.remove();
                modal.classList.remove('hidden');
            });
        }

        // Close
        closeBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => modal.classList.add('hidden'));
            }
        });
        modal.addEventListener('click', e => {
            if (e.target === modal) modal.classList.add('hidden');
        });

        // Edit
        document.querySelectorAll('.edit-dept-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name;

                form.action = `/departments/${id}`;
                title.textContent = 'Edit Department';
                submitBtn.textContent = 'Update Department';

                document.getElementById('dept-name').value = name;

                // Remove existing PUT spoof if any, then add new one
                const existingMethod = form.querySelector('input[name="_method"][value="PUT"]');
                if (!existingMethod) {
                    form.insertAdjacentHTML('beforeend', '@method('PUT')');
                }

                modal.classList.remove('hidden');
            });
        });

        // DataTable
        $(function() {
            $('#dept-table').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "Search Department:",
                    lengthMenu: "Show _MENU_ entries"
                }
            });
        });
    </script>
@endsection
