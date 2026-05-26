@extends('layouts.app')

@section('content')
    <div class="px-4">
        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-md bg-green-100 border border-green-400 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 px-4 py-3 rounded-md bg-yellow-100 border border-yellow-400 text-yellow-800">
                {{ session('warning') }}
            </div>
        @endif

        @if(session('import_errors'))
            <div class="mb-4 px-4 py-3 rounded-md bg-red-100 border border-red-400 text-red-800">
                <p class="font-semibold mb-2">Import Errors:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="ml-2 text-xl font-semibold text-gray-800">Suppliers</h3>
                    <p class="ml-2 mt-1 text-sm text-gray-500">Manage supplier information.</p>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Export CSV Button --}}
                    <a href="{{ route('supplier.export') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export CSV
                    </a>

                    {{-- Import CSV Button --}}
                    @if (auth()->user()->accessLevel?->add_edit_supplier)
                        <button id="import-supplier-trigger"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Import CSV
                        </button>

                        <button id="add-supplier-trigger"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Supplier
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3">
                <table id="supplier-table" class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Address</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-800">{{ $supplier->name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-gray-600">{{ $supplier->email ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-gray-600">{{ $supplier->phone_no ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-gray-600">{{ $supplier->address ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if (auth()->user()->accessLevel?->add_edit_supplier)
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                class="edit-supplier-btn inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors"
                                                data-id="{{ $supplier->id }}" data-name="{{ $supplier->name }}"
                                                data-email="{{ $supplier->email }}" data-phone="{{ $supplier->phone_no }}"
                                                data-address="{{ $supplier->address }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <form action="{{ route('supplier.delete', $supplier->id) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Delete this supplier?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-400">No suppliers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Import CSV Modal -->
    <div id="import-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Import Suppliers from CSV</h2>
                    <button type="button" id="close-import-modal"
                        class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form action="{{ route('supplier.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                        <input type="file" name="csv_file" accept=".csv" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Max 2MB. Format: Name, Email, Phone, Address</p>
                    </div>

                    <div class="mb-6 p-4 bg-blue-50 rounded-md">
                        <p class="text-sm text-blue-800 mb-2 font-semibold">CSV Format:</p>
                        <ul class="text-xs text-blue-700 list-disc list-inside space-y-1">
                            <li>Header row: Name, Email, Phone, Address</li>
                            <li>Name is required, other fields are optional</li>
                            <li>Email must be valid format if provided</li>
                        </ul>
                        <a href="{{ route('supplier.template') }}" class="text-xs text-indigo-600 hover:underline mt-2 inline-block">
                            Download Template
                        </a>
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="button" id="cancel-import-modal"
                            class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Supplier Modal -->
    <div id="supplier-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="supplier-modal-title" class="text-2xl font-bold text-gray-800">Add Supplier</h2>
                    <button type="button" id="close-supplier-modal"
                        class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>

                <form id="supplier-form" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="supplier_id" id="supplier-id">

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" id="supplier-name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="supplier-email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" name="phone_no" id="supplier-phone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" name="address" id="supplier-address"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <button type="button" id="cancel-supplier-modal"
                            class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" id="supplier-modal-submit"
                            class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700">
                            Add Supplier
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

    <script>
        // Import Modal
        const importModal = document.getElementById('import-modal');
        const openImportBtn = document.getElementById('import-supplier-trigger');
        const closeImportBtns = [document.getElementById('close-import-modal'), document.getElementById('cancel-import-modal')];

        if (openImportBtn) {
            openImportBtn.addEventListener('click', () => {
                importModal.classList.remove('hidden');
            });
        }

        closeImportBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => importModal.classList.add('hidden'));
            }
        });

        importModal?.addEventListener('click', e => {
            if (e.target === importModal) importModal.classList.add('hidden');
        });

        // Add/Edit Supplier Modal
        const supplierModal = document.getElementById('supplier-modal');
        const openAddBtn = document.getElementById('add-supplier-trigger');
        const closeBtns = [document.getElementById('close-supplier-modal'), document.getElementById(
            'cancel-supplier-modal')];
        const supplierForm = document.getElementById('supplier-form');
        const methodInput = supplierForm.querySelector('[name="_method"]');
        const modalTitle = document.getElementById('supplier-modal-title');
        const submitBtn = document.getElementById('supplier-modal-submit');

        // Add Supplier - Only add listener if button exists (user has permission)
        if (openAddBtn) {
            openAddBtn.addEventListener('click', () => {
                supplierForm.reset();
                supplierForm.action = "{{ route('supplier.add') }}";
                methodInput.value = 'POST';
                document.getElementById('supplier-id').value = '';

                modalTitle.textContent = 'Add Supplier';
                submitBtn.textContent = 'Add Supplier';

                supplierModal.classList.remove('hidden');
            });
        }

        // Edit Supplier
        document.querySelectorAll('.edit-supplier-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name || '';
                const email = btn.dataset.email || '';
                const phone = btn.dataset.phone || '';
                const address = btn.dataset.address || '';

                supplierForm.reset();
                supplierForm.action = `/supplier/update/${id}`;
                methodInput.value = 'PUT';

                modalTitle.textContent = 'Edit Supplier';
                submitBtn.textContent = 'Update Supplier';

                document.getElementById('supplier-id').value = id;
                document.getElementById('supplier-name').value = name;
                document.getElementById('supplier-email').value = email;
                document.getElementById('supplier-phone').value = phone;
                document.getElementById('supplier-address').value = address;

                supplierModal.classList.remove('hidden');
            });
        });

        // Close modal
        closeBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => supplierModal.classList.add('hidden'));
            }
        });
        supplierModal.addEventListener('click', e => {
            if (e.target === supplierModal) supplierModal.classList.add('hidden');
        });

        // DataTables
        $(function() {
            $('#supplier-table').DataTable({
                pageLength: 25,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "Search Supplier:",
                    lengthMenu: "Show _MENU_ entries"
                }
            });
        });
    </script>
@endsection
