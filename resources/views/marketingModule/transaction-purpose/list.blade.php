@php
    $canEdit = auth()->user()?->marketingAccessLevel?->add_edit_transaction
        || auth()->user()?->marketingAccessLevel?->settings;
@endphp

@extends('layouts.app', ['module' => 'marketing'])

@section('content')
    <div class="px-4">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="ml-2 text-xl font-semibold text-gray-800">Transaction Purposes</h3>
                    <p class="ml-2 mt-1 text-sm text-gray-500">Reasons used for marketing transactions (GRN, transfers, stock-outs).</p>
                </div>
                @if ($canEdit)
                    <button id="add-purpose-trigger"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Purpose
                    </button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3">
                <table id="purpose-table" class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Purpose</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($purposes as $purpose)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-gray-800">{{ $purpose->transaction_purpose_name }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                    {{ optional($purpose->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($canEdit)
                                        <div class="flex items-center gap-2">
                                            <button
                                                class="edit-purpose-btn inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-md hover:bg-emerald-100 transition-colors"
                                                data-id="{{ $purpose->id }}"
                                                data-name="{{ $purpose->transaction_purpose_name }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ url('/marketing/transaction-purposes/' . $purpose->id) }}"
                                                class="inline"
                                                onsubmit="return confirm('Delete this transaction purpose?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
                                <td colspan="3" class="px-4 py-10 text-center text-gray-400">No transaction purposes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div id="purpose-modal" class="fixed inset-0 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h2 id="purpose-modal-title" class="text-xl font-bold text-gray-900">Add Purpose</h2>
                    </div>
                    <button id="close-purpose-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="purpose-form" method="POST" action="{{ url('/marketing/transaction-purposes') }}">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Purpose Name <span class="text-red-500">*</span></label>
                        <input name="transaction_purpose_name" id="purpose-name" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            placeholder="e.g. Branch Restock, Event / Roadshow">
                    </div>

                    <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200">
                        <button type="button" id="cancel-purpose-modal"
                            class="px-5 py-2.5 text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" id="purpose-submit"
                            class="px-5 py-2.5 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 shadow-sm">
                            Add Purpose
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <style>
        .dataTables_wrapper .dataTables_length { margin-bottom: 1rem; }
        .dataTables_wrapper .dataTables_length label {
            font-size: 0.875rem; font-weight: 600; color: #4b5563;
            display: flex; align-items: center;
        }
        .dataTables_wrapper .dataTables_length select {
            margin: 0 0.5rem; padding: 0.5rem 2rem 0.5rem 0.75rem;
            border: 1px solid #d1d5db; border-radius: 0.5rem;
            font-size: 0.875rem; outline: none; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center; background-repeat: no-repeat;
            background-size: 1.25rem;
        }
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #a7f3d0;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .dataTables_wrapper .dataTables_filter { margin-bottom: 1rem; }
        .dataTables_wrapper .dataTables_filter label {
            font-size: 0.875rem; font-weight: 600; color: #4b5563;
            display: flex; align-items: center; gap: 0.75rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.5rem 0.75rem; border: 1px solid #d1d5db;
            border-radius: 0.5rem; font-size: 0.875rem; outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #a7f3d0;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem; color: #6b7280; padding-top: 1rem;
        }
        .dataTables_wrapper .dataTables_paginate { padding-top: 1rem; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem; margin: 0 0.125rem;
            border-radius: 0.375rem; font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #059669 !important;
            color: white !important;
            border: 1px solid #059669 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #ecfdf5 !important;
            color: #059669 !important;
            border: 1px solid #a7f3d0 !important;
        }
    </style>

    <script>
        const modal = document.getElementById('purpose-modal');
        const openBtn = document.getElementById('add-purpose-trigger');
        const closeBtns = [
            document.getElementById('close-purpose-modal'),
            document.getElementById('cancel-purpose-modal'),
        ];

        const form = document.getElementById('purpose-form');
        const title = document.getElementById('purpose-modal-title');
        const submitBtn = document.getElementById('purpose-submit');

        function resetMethodSpoof() {
            form.querySelector('input[name="_method"]')?.remove();
        }

        if (openBtn) {
            openBtn.addEventListener('click', () => {
                form.reset();
                resetMethodSpoof();
                form.action = '/marketing/transaction-purposes';
                title.textContent = 'Add Purpose';
                submitBtn.textContent = 'Add Purpose';
                modal.classList.remove('hidden');
            });
        }

        closeBtns.forEach(btn => btn?.addEventListener('click', () => modal.classList.add('hidden')));
        modal.addEventListener('click', e => {
            if (e.target === modal) modal.classList.add('hidden');
        });

        document.querySelectorAll('.edit-purpose-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name;

                form.reset();
                resetMethodSpoof();
                form.action = `/marketing/transaction-purposes/${id}`;
                title.textContent = 'Edit Purpose';
                submitBtn.textContent = 'Update Purpose';

                document.getElementById('purpose-name').value = name;

                form.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT">');
                modal.classList.remove('hidden');
            });
        });

        $(function () {
            $('#purpose-table').DataTable({
                pageLength: 25,
                order: [[0, 'asc']],
                language: {
                    search: "Search Purpose:",
                    lengthMenu: "Show _MENU_ entries",
                }
            });
        });
    </script>
@endsection
