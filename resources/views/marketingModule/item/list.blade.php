@php
    $canEdit = auth()->user()?->marketingAccessLevel?->add_edit_asset;
@endphp

@extends('layouts.app', ['module' => 'marketing'])

@section('content')
    <div class="px-4">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="ml-2 text-xl font-semibold text-gray-800">Items</h3>
                    <p class="ml-2 mt-1 text-sm text-gray-500">Marketing inventory items.</p>
                </div>
                @if ($canEdit)
                    <button id="add-item-trigger"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Item
                    </button>
                @endif
            </div>
        </div>

        {{-- Selection action bar (shown when rows are checked) --}}
        <div id="selection-bar"
            class="hidden mb-4 px-4 py-2 bg-white border border-gray-200 rounded-full shadow-sm flex items-center gap-3">
            <span class="text-sm text-gray-600 font-medium">
                <span id="selection-count">0</span> item(s) selected
            </span>

            <button type="button" id="bulk-receive"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-violet-600 bg-violet-50 border border-violet-200 rounded-md hover:bg-violet-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Receive
            </button>

            <button type="button" id="bulk-request"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-md hover:bg-emerald-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21.923 2.92a1.08 1.08 0 0 0-1.317-.244L2.763 9.38a.81.81 0 0 0 .02 1.476l4.612 1.697 1.753 5.43c.183.565.759.925 1.348.894.567-.03 1.086-.401 1.287-.927l2.02-5.02 4.587 3.294c.35.253.816.214 1.116-.09a.804.804 0 0 0 .245-1.06l-5.57-8.67 6.648-5.784c.462-.402.534-1.075.172-1.542z"/>
                </svg>
                Request
            </button>

            <button type="button" id="bulk-transfer"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-50 border border-amber-200 rounded-md hover:bg-amber-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Transfer
            </button>

            <button type="button" id="bulk-invoice"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-600 bg-purple-50 border border-purple-200 rounded-md hover:bg-purple-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Invoice
            </button>
        </div>

        {{-- Sticky-column table (mirrors asset masterList styling) --}}
        <style>
            #tbl-scroll {
                overflow-x: auto;
                overflow-y: auto;
                clear: both;
                border-top: 1px solid #e5e7eb;
                margin-top: 0.5rem;
            }
            #itemsTable {
                border-collapse: separate;
                border-spacing: 0;
                font-size: 0.8rem;
            }
            #itemsTable th, #itemsTable td {
                white-space: nowrap;
                border-bottom: 1px solid #e5e7eb;
            }
            #itemsTable th { padding: 0.45rem 0.75rem; }
            #itemsTable td { padding: 0.4rem 0.75rem; }

            #itemsTable thead th.sc,
            #itemsTable tbody td.sc {
                position: sticky;
                will-change: transform;
                background-clip: padding-box;
            }
            #itemsTable thead th.sc {
                z-index: 30;
                background-color: #f3f4f6 !important;
            }
            #itemsTable tbody td.sc {
                z-index: 10;
                background-color: #ffffff;
            }
            .sc-chk  { left: 0px; min-width: 50px;  border-right: 1px solid #d1d5db; }
            .sc-code { min-width: 110px; border-right: 1px solid #d1d5db; }
            .sc-name { min-width: 320px; box-shadow: 4px 0 6px -2px rgba(0,0,0,.10); }
            .sc-act  { right: 0px; min-width: 60px; box-shadow: -4px 0 6px -2px rgba(0,0,0,.10); }
            #itemsTable thead th.sc-act { background-color: #f3f4f6 !important; }

            /* Non-sticky cells transparent so row hover shows through */
            #itemsTable tbody tr > td:not(.sc),
            #itemsTable tbody tr:hover > td:not(.sc),
            #itemsTable tbody tr.odd > td:not(.sc),
            #itemsTable tbody tr.even > td:not(.sc),
            #itemsTable tbody tr.odd:hover > td:not(.sc),
            #itemsTable tbody tr.even:hover > td:not(.sc) {
                background-color: transparent !important;
                box-shadow: none !important;
            }
            #itemsTable tbody tr:hover td.sc { background-color: #f9fafb; }
        </style>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-4 py-3">
                <table id="itemsTable" class="text-sm">
                    <thead style="background-color:#f3f4f6; color:#374151;">
                        <tr>
                            <th class="sc sc-chk text-center" style="width:50px;min-width:50px">
                                <input type="checkbox" id="select-all"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            </th>
                            <th class="sc sc-code text-left">Code</th>
                            <th class="sc sc-name text-left">Item</th>
                            <th style="min-width:140px" class="text-left">Category</th>
                            <th style="min-width:110px" class="text-right">Unit Cost</th>
                            <th style="min-width:110px" class="text-right">Price</th>
                            <th style="min-width:140px" class="text-left">Branch</th>
                            <th style="min-width:90px"  class="text-right">Quantity</th>
                            <th style="min-width:120px" class="text-left">Date Created</th>
                            <th class="sc sc-act text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php
                                $totalQty   = $item->values->sum('current_unit');
                                $branchList = $item->values
                                    ->pluck('branch.code')
                                    ->filter()
                                    ->unique()
                                    ->values();
                                $branchLabel = $branchList->isEmpty()
                                    ? '—'
                                    : ($branchList->count() === 1
                                        ? 'MKT-' . $branchList->first()
                                        : 'MKT-' . $branchList->first() . ' +' . ($branchList->count() - 1));
                            @endphp
                            <tr>
                                {{-- ── FROZEN LEFT ── --}}
                                <td class="sc sc-chk text-center">
                                    <input type="checkbox" value="{{ $item->id }}"
                                        class="row-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                </td>

                                <td class="sc sc-code font-medium">{{ $item->item_running_number }}</td>

                                <td class="sc sc-name">
                                    <div class="flex items-center gap-2">
                                        @if ($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}"
                                                class="w-10 h-10 object-cover rounded border flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 bg-gray-200 rounded border flex items-center justify-center text-gray-400 flex-shrink-0">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-900 truncate">{{ $item->name }}</div>
                                            @if ($item->type)
                                                <div class="text-xs text-gray-500 truncate">{{ $item->type }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- ── SCROLLABLE ── --}}
                                <td class="text-emerald-700">{{ $item->category?->name ?? '—' }}</td>
                                <td class="text-right">{{ $item->purchase_cost !== null ? 'RM ' . number_format($item->purchase_cost, 2) : '—' }}</td>
                                <td class="text-right">{{ $item->sales_cost !== null ? 'RM ' . number_format($item->sales_cost, 2) : '—' }}</td>
                                <td class="text-emerald-700">{{ $branchLabel }}</td>
                                <td class="text-right">{{ $totalQty }}</td>
                                <td>{{ optional($item->created_at)->format('d/m/Y') }}</td>

                                {{-- ── FROZEN RIGHT ── --}}
                                <td class="sc sc-act text-center">
                                    @if ($canEdit)
                                        <button type="button"
                                            class="row-menu-trigger p-1 rounded hover:bg-gray-100 text-gray-400 hover:text-gray-600"
                                            data-item='@json($item)'>
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-gray-400">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Row action menu (shared, repositioned per row) --}}
    @if ($canEdit)
        <div id="row-action-menu"
            class="hidden fixed z-50 w-40 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
            <button type="button" id="menu-edit"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </button>
            <form id="menu-delete-form" method="POST" action="" class="block"
                onsubmit="return confirm('Delete this item?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    @endif

    {{-- Add/Edit Modal — layout mirrors asset masterList edit form --}}
    <div id="item-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-auto">
        <div class="bg-white w-full max-w-5xl rounded shadow p-6 mx-4 my-8 relative">
            <h3 id="item-modal-title" class="text-lg font-semibold mb-4">Add Item</h3>

            {{-- Error container --}}
            <div id="itemFormErrors"
                class="hidden mb-4 px-4 py-3 rounded-md bg-red-100 border border-red-400 text-red-800">
                <ul class="list-disc list-inside text-sm" id="itemFormErrorList"></ul>
            </div>

            <form id="item-form" method="POST" action="{{ url('/marketing/items') }}" enctype="multipart/form-data">
                @csrf
                @include('marketingModule.item.partials.form')

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" id="cancel-item-modal"
                        class="px-4 py-2 text-sm border rounded">Cancel</button>
                    <button type="submit" id="item-submit"
                        class="px-4 py-2 bg-emerald-600 text-white text-sm rounded">Save</button>
                </div>
            </form>
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
    (function () {
        const modal = document.getElementById('item-modal');
        const openBtn = document.getElementById('add-item-trigger');
        const cancelBtn = document.getElementById('cancel-item-modal');

        const form = document.getElementById('item-form');
        const title = document.getElementById('item-modal-title');
        const submitBtn = document.getElementById('item-submit');
        const imagePlaceholder = document.getElementById('imagePlaceholder');
        const existingImageContainer = document.getElementById('existingImageContainer');
        const existingImage = document.getElementById('existingImage');
        const replaceImageText = document.getElementById('replaceImageText');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function resetMethodSpoof() {
            form.querySelector('input[name="_method"]')?.remove();
        }

        if (openBtn) {
            openBtn.addEventListener('click', () => {
                form.reset();
                resetMethodSpoof();
                form.action = '/marketing/items';
                title.textContent = 'Add Item';
                submitBtn.textContent = 'Save';
                // Add mode: show placeholder, hide existing image
                imagePlaceholder.classList.remove('hidden');
                existingImageContainer.classList.add('hidden');
                replaceImageText.classList.add('hidden');
                openModal();
            });
        }

        cancelBtn?.addEventListener('click', closeModal);
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

        function openEditModal(item) {
            form.reset();
            resetMethodSpoof();
            form.action = `/marketing/items/${item.id}`;
            title.textContent = 'Edit Item';
            submitBtn.textContent = 'Update';

            document.getElementById('item-name').value = item.name ?? '';
            document.getElementById('item-running-number').value = item.item_running_number ?? '';
            document.getElementById('item-category').value = item.category_id ?? '';
            document.getElementById('item-type').value = item.type ?? '';
            document.getElementById('item-uom').value = item.unit_measure ?? '';
            document.getElementById('item-purchase-cost').value = item.purchase_cost ?? '';
            document.getElementById('item-sales-cost').value = item.sales_cost ?? '';
            document.getElementById('item-stable-unit').value = item.stable_unit ?? 0;
            document.getElementById('item-description').value = item.description ?? '';
            document.getElementById('item-remark').value = item.remark ?? '';

            // Edit mode: hide placeholder, show existing image (if any), show replace hint
            if (item.image) {
                existingImage.src = `/storage/${item.image}`;
                existingImageContainer.classList.remove('hidden');
                imagePlaceholder.classList.add('hidden');
                replaceImageText.classList.remove('hidden');
            } else {
                existingImageContainer.classList.add('hidden');
                imagePlaceholder.classList.remove('hidden');
                replaceImageText.classList.add('hidden');
            }

            form.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT">');
            openModal();
        }

        // Kebab row action menu
        const rowMenu = document.getElementById('row-action-menu');
        let activeItem = null;

        document.querySelectorAll('.row-menu-trigger').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                activeItem = JSON.parse(trigger.dataset.item);
                const rect = trigger.getBoundingClientRect();
                rowMenu.style.top = `${rect.bottom + 4}px`;
                rowMenu.style.left = `${rect.right - rowMenu.offsetWidth}px`;
                rowMenu.classList.remove('hidden');
                // Position correctly after it's visible (so width is measurable)
                rowMenu.style.left = `${rect.right - rowMenu.offsetWidth}px`;
            });
        });

        document.addEventListener('click', () => rowMenu?.classList.add('hidden'));

        document.getElementById('menu-edit')?.addEventListener('click', () => {
            rowMenu.classList.add('hidden');
            if (activeItem) openEditModal(activeItem);
        });

        document.getElementById('menu-delete-form')?.addEventListener('submit', function (e) {
            if (activeItem) {
                this.action = `/marketing/items/${activeItem.id}`;
            }
        });

        // ─── Selection tracking ───
        window.selectedItemIds = new Set();
        const selectionBar   = document.getElementById('selection-bar');
        const selectionCount = document.getElementById('selection-count');
        const selectAll      = document.getElementById('select-all');

        function refreshSelectionUI() {
            // Sync checkbox states with the persistent Set
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = window.selectedItemIds.has(cb.value);
            });

            // Toggle bar + count
            const count = window.selectedItemIds.size;
            selectionCount.textContent = count;
            selectionBar.classList.toggle('hidden', count === 0);
            selectionBar.classList.toggle('flex',  count > 0);

            // Sync select-all header checkbox
            if (selectAll) {
                const visibleCBs = document.querySelectorAll('.row-checkbox');
                const allChecked = visibleCBs.length > 0 &&
                    Array.from(visibleCBs).every(cb => window.selectedItemIds.has(cb.value));
                selectAll.checked = allChecked;
            }
        }

        function bindRowCheckboxes() {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.onchange = () => {
                    if (cb.checked) {
                        window.selectedItemIds.add(cb.value);
                    } else {
                        window.selectedItemIds.delete(cb.value);
                    }
                    refreshSelectionUI();
                };
            });
        }
        bindRowCheckboxes();

        selectAll?.addEventListener('change', () => {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = selectAll.checked;
                if (selectAll.checked) {
                    window.selectedItemIds.add(cb.value);
                } else {
                    window.selectedItemIds.delete(cb.value);
                }
            });
            refreshSelectionUI();
        });

        // Re-bind row checkboxes after every DataTables redraw (paging / search / sort)
        $('#itemsTable').on('draw.dt', () => {
            bindRowCheckboxes();
            refreshSelectionUI();
        });

        // Bulk action button stubs — wire to real handlers when those flows exist
        document.getElementById('bulk-receive')?.addEventListener('click', () => {
            const ids = Array.from(window.selectedItemIds);
            console.log('Receive →', ids);
            // TODO: POST to GRN creation endpoint with selected item IDs
        });
        document.getElementById('bulk-request')?.addEventListener('click', () => {
            const ids = Array.from(window.selectedItemIds);
            console.log('Request →', ids);
            // TODO: POST to PO request endpoint
        });
        document.getElementById('bulk-transfer')?.addEventListener('click', () => {
            const ids = Array.from(window.selectedItemIds);
            console.log('Transfer →', ids);
            // TODO: POST to transfer creation endpoint
        });
        document.getElementById('bulk-invoice')?.addEventListener('click', () => {
            const ids = Array.from(window.selectedItemIds);
            console.log('Invoice →', ids);
            // TODO: POST to invoice generation endpoint
        });

        $(function () {
            const table = $('#itemsTable').DataTable({
                responsive: false,
                autoWidth: false,
                pageLength: 25,
                order: [[2, 'asc']],
                columnDefs: [
                    { width: '50px',  targets: 0 },
                    { width: '110px', targets: 1 },
                    { width: '320px', targets: 2 },
                    { width: '140px', targets: 3 },
                    { width: '110px', targets: 4 },
                    { width: '110px', targets: 5 },
                    { width: '140px', targets: 6 },
                    { width: '90px',  targets: 7 },
                    { width: '120px', targets: 8 },
                    { width: '60px',  targets: 9 },
                    { orderable: false, searchable: false, targets: [0, 9] },
                ],
                language: {
                    search: "Search Item:",
                    lengthMenu: "Show _MENU_ entries",
                },
                drawCallback: function () {
                    if (window._itemsStickyW0 !== undefined) {
                        document.querySelectorAll('#itemsTable tbody td.sc-code').forEach(function (el) {
                            el.style.left = window._itemsStickyW0 + 'px';
                        });
                        document.querySelectorAll('#itemsTable tbody td.sc-name').forEach(function (el) {
                            el.style.left = (window._itemsStickyW0 + window._itemsStickyW1) + 'px';
                        });
                    }
                },
                initComplete: function () {
                    // Wrap in scroll container so frozen columns stay in place during horizontal scroll
                    $('#itemsTable').wrap('<div id="tbl-scroll"></div>');

                    function fitTableHeight() {
                        const el = document.getElementById('tbl-scroll');
                        if (!el) return;
                        el.style.maxHeight = '99999px';
                        const rect    = el.getBoundingClientRect();
                        const dtWrap  = el.parentElement;
                        const card    = el.closest('.bg-white');
                        const belowDT = dtWrap.getBoundingClientRect().bottom - rect.bottom;
                        const belowCard = card
                            ? card.getBoundingClientRect().bottom - dtWrap.getBoundingClientRect().bottom
                            : 0;
                        const available = window.innerHeight - rect.top - belowDT - belowCard - 24;
                        el.style.maxHeight = Math.max(200, available) + 'px';
                    }
                    fitTableHeight();
                    window.addEventListener('resize', fitTableHeight);

                    // Compute sticky-column left offsets from actual rendered widths
                    const ths = document.querySelectorAll('#itemsTable thead th');
                    if (ths.length >= 3) {
                        const w0 = ths[0].offsetWidth; // checkbox
                        const w1 = ths[1].offsetWidth; // code
                        window._itemsStickyW0 = w0;
                        window._itemsStickyW1 = w1;
                        document.querySelectorAll('.sc-code').forEach(function (el) { el.style.left = w0 + 'px'; });
                        document.querySelectorAll('.sc-name').forEach(function (el) { el.style.left = (w0 + w1) + 'px'; });
                    }
                }
            });
        });
    })();
    </script>
@endsection
