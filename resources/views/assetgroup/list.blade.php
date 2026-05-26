@extends('layouts.app')

@section('content')
<div class="px-4">

    {{-- Header Section --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="ml-2 text-xl font-semibold text-gray-800">Asset Groups</h3>
                <p class="ml-2 mt-1 text-sm text-gray-500">Manage asset group classifications.</p>
            </div>
            @if(auth()->user()->accessLevel?->add_edit_asset_grouping)
                <button id="add-assetgroup-trigger"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Asset Group
                </button>
            @endif
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3">
            <table id="assetgroup-table" class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Lifespan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service Interval</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($assetGroups as $group)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-800">{{ $group->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <span class="text-gray-600">{{ $group->description ?? '-' }}</span>
                                    @if($group->branc_pic || $group->remark)
                                        <div class="mt-1 text-xs text-gray-500">
                                            @if($group->branc_pic)
                                                <p><span class="font-medium">Branch PIC:</span> {{ $group->branc_pic }}</p>
                                            @endif
                                            @if($group->remark)
                                                <p><span class="font-medium">Remark:</span> {{ $group->remark }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-600">{{ $group->lifespan_display }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-gray-600">{{ $group->service_interval_display }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if(auth()->user()->accessLevel?->add_edit_asset_grouping)
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                            class="edit-assetgroup-btn inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100 transition-colors"
                                            data-id="{{ $group->id }}"
                                            data-name="{{ $group->name }}"
                                            data-description="{{ $group->description }}"
                                            data-lifespan-from="{{ $group->lifespan_from }}"
                                            data-lifespan-to="{{ $group->lifespan_to }}"
                                            data-service-interval-from="{{ $group->service_interval_from }}"
                                            data-service-interval-to="{{ $group->service_interval_to }}"
                                            data-branc-pic="{{ $group->branc_pic }}"
                                            data-remark="{{ $group->remark }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </button>
                                        <form action="{{ route('assetgroup.delete', $group->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete this asset group?')">
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
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400">No asset groups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Asset Group Modal -->
<div id="assetgroup-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 id="assetgroup-modal-title" class="text-2xl font-bold text-gray-800">Add Asset Group</h2>
                <button type="button" id="close-assetgroup-modal" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <form id="assetgroup-form" method="POST">
                @csrf
                <input type="hidden" name="assetgroup_id" id="assetgroup-id">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="assetgroup-name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" id="assetgroup-description"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Branch PIC</label>
                        <input type="text" name="branc_pic" id="assetgroup-branc-pic"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remark</label>
                        <textarea name="remark" id="assetgroup-remark" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lifespan (in months)</label>
                        <p class="text-xs text-gray-500 mb-2">Leave both empty for "Permanent"</p>
                        <div class="flex gap-2">
                            <div class="w-1/2">
                                <input type="number" name="lifespan_from" id="assetgroup-lifespan-from" min="0" placeholder="From"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="w-1/2">
                                <input type="number" name="lifespan_to" id="assetgroup-lifespan-to" min="0" placeholder="To (optional)"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service Interval (in months)</label>
                        <p class="text-xs text-gray-500 mb-2">Leave both empty for "Case by case"</p>
                        <div class="flex gap-2">
                            <div class="w-1/2">
                                <input type="number" name="service_interval_from" id="assetgroup-service-interval-from" min="0" placeholder="From"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="w-1/2">
                                <input type="number" name="service_interval_to" id="assetgroup-service-interval-to" min="0" placeholder="To (optional)"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-4">
                    <button type="button" id="cancel-assetgroup-modal"
                        class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" id="assetgroup-modal-submit"
                        class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700">
                        Add Asset Group
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
    const assetgroupModal = document.getElementById('assetgroup-modal');
    const openAddBtn = document.getElementById('add-assetgroup-trigger');
    const closeBtns = [document.getElementById('close-assetgroup-modal'), document.getElementById('cancel-assetgroup-modal')];
    const assetgroupForm = document.getElementById('assetgroup-form');
    const modalTitle = document.getElementById('assetgroup-modal-title');
    const submitBtn = document.getElementById('assetgroup-modal-submit');

    // Add Asset Group - Only add listener if button exists (user has permission)
    if (openAddBtn) {
        openAddBtn.addEventListener('click', () => {
            assetgroupForm.reset();
            assetgroupForm.action = "{{ route('assetgroup.add') }}";
            assetgroupForm.querySelector('[name="_method"]')?.remove();
            document.getElementById('assetgroup-id').value = '';

            modalTitle.textContent = 'Add Asset Group';
            submitBtn.textContent = 'Add Asset Group';

            assetgroupModal.classList.remove('hidden');
        });
    }

    // Edit Asset Group
    document.querySelectorAll('.edit-assetgroup-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const name = btn.dataset.name || '';
            const description = btn.dataset.description || '';
            const lifespanFrom = btn.dataset.lifespanFrom || '';
            const lifespanTo = btn.dataset.lifespanTo || '';
            const serviceIntervalFrom = btn.dataset.serviceIntervalFrom || '';
            const serviceIntervalTo = btn.dataset.serviceIntervalTo || '';
            const brancPic = btn.dataset.brancPic || '';
            const remark = btn.dataset.remark || '';

            assetgroupForm.reset();
            assetgroupForm.action = `/assetgroup/update/${id}`;
            if (!assetgroupForm.querySelector('[name="_method"]')) {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                assetgroupForm.appendChild(methodInput);
            } else {
                assetgroupForm.querySelector('[name="_method"]').value = 'PUT';
            }

            modalTitle.textContent = 'Edit Asset Group';
            submitBtn.textContent = 'Update Asset Group';

            document.getElementById('assetgroup-id').value = id;
            document.getElementById('assetgroup-name').value = name;
            document.getElementById('assetgroup-description').value = description;
            document.getElementById('assetgroup-lifespan-from').value = lifespanFrom;
            document.getElementById('assetgroup-lifespan-to').value = lifespanTo;
            document.getElementById('assetgroup-service-interval-from').value = serviceIntervalFrom;
            document.getElementById('assetgroup-service-interval-to').value = serviceIntervalTo;
            document.getElementById('assetgroup-branc-pic').value = brancPic;
            document.getElementById('assetgroup-remark').value = remark;

            assetgroupModal.classList.remove('hidden');
        });
    });

    // Close modal
    closeBtns.forEach(btn => {
        if (btn) {
            btn.addEventListener('click', () => assetgroupModal.classList.add('hidden'));
        }
    });
    assetgroupModal.addEventListener('click', e => { if (e.target === assetgroupModal) assetgroupModal.classList.add('hidden'); });

    // DataTables
    $(function () {
        $('#assetgroup-table').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            language: { search: "Search Asset Group:", lengthMenu: "Show _MENU_ entries" }
        });
    });
</script>
@endsection