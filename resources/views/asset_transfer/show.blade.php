@extends('layouts.app')

@section('content')
<div class="px-4">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('assetTransfer.index') }}"
                onclick="if(history.length>1){event.preventDefault();history.back();}"
                class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <h2 class="text-xl font-bold text-gray-800">Transfer Details</h2>
        </div>
        <a href="{{ route('assetTransfer.pdf', $transfer->id) }}"
            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded text-sm font-medium inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download PDF
        </a>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

        {{-- Transfer Info --}}
        <div class="flex justify-between mb-6">
            <div class="space-y-1">
                <p><span class="font-semibold">From:</span> {{ $transfer->fromBranch->branch_name ?? '-' }}</p>
                <p><span class="font-semibold">To:</span> {{ $transfer->toBranch->branch_name ?? '-' }}</p>
                <p><span class="font-semibold">Purpose:</span> {{ $transfer->transfer_purpose ?? '-' }}</p>
                <p><span class="font-semibold">By:</span> {{ $transfer->creator->name ?? '-' }}</p>
            </div>
            <div class="text-left mr-10 space-y-1">
                <p><span class="font-semibold">Date:</span> {{ $transfer->created_at->format('d/m/Y') }}</p>
                <p><span class="font-semibold">Reference:</span> {{ $transfer->transfer_running_no }}</p>
                <p>
                    <span class="font-semibold">Status:</span>
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-blue-100 text-blue-800',
                            'in_transit' => 'bg-purple-100 text-purple-800',
                            'partial_received' => 'bg-orange-100 text-orange-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                        ];
                        $color = $statusColors[$transfer->transfer_status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                        {{ ucfirst(str_replace('_', ' ', $transfer->transfer_status)) }}
                    </span>
                </p>
                <p><span class="font-semibold">Total Cost:</span> RM {{ number_format($transfer->transfer_cost, 2) }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        @php
            $user = auth()->user();
            $userBranchIds = $user->branches()->pluck('branches.id')->toArray();
            $hasAccessToFromBranch = in_array($transfer->transfer_from, $userBranchIds);
            $hasAccessToToBranch = in_array($transfer->transfer_to, $userBranchIds);
            $canReceive = in_array($transfer->transfer_status, ['in_transit', 'partial_received']) && $hasAccessToToBranch;
        @endphp

        <div class="mb-6">
            <h4 class="font-semibold mb-2">Items:</h4>

            <form method="POST" action="{{ route('assetTransfer.receiveSelected', $transfer->id) }}"
                enctype="multipart/form-data" id="receiveForm">
                @csrf
                <input type="hidden" name="asset_ids" id="receiveAssetIds">

                <div class="overflow-x-auto">
                    <table class="min-w-full border text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                @if (in_array($transfer->transfer_status, ['pending', 'in_transit', 'partial_received']))
                                    <th class="px-2 py-2 text-center border">
                                        <input type="checkbox" id="selectAllItems">
                                    </th>
                                @endif
                                <th class="px-4 py-2 border text-left">Code</th>
                                <th class="px-4 py-2 border text-left">Name</th>
                                <th class="px-4 py-2 border text-left">Group</th>
                                <th class="px-4 py-2 border text-center">Cost (RM)</th>
                                <th class="px-4 py-2 border text-center">Status</th>
                                @if ($canReceive)
                                    <th class="px-4 py-2 border text-center">User</th>
                                    <th class="px-4 py-2 border text-center">Department</th>
                                    <th class="px-4 py-2 border text-center">Photo</th>
                                @endif
                                @if (in_array($transfer->transfer_status, ['in_transit', 'partial_received']))
                                    <th class="px-4 py-2 border text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfer->assets as $index => $asset)
                                @php
                                    $pivot = $asset->pivot ?? null;
                                    $assetStatus = $pivot->asset_transfer_status ?? 'pending';
                                    $assetStatusColors = [
                                        'pending' => 'text-yellow-600',
                                        'approved' => 'text-blue-600',
                                        'rejected' => 'text-red-600',
                                        'in_transit' => 'text-purple-600',
                                        'received' => 'text-green-600',
                                        'returned' => 'text-red-600',
                                    ];
                                    $assetColor = $assetStatusColors[$assetStatus] ?? 'text-gray-600';
                                    $isSelectable = false;
                                    if ($transfer->transfer_status === 'pending' && $assetStatus === 'pending') {
                                        $isSelectable = true;
                                    } elseif (
                                        in_array($transfer->transfer_status, ['in_transit', 'partial_received']) &&
                                        in_array($assetStatus, ['in_transit', 'approved'])
                                    ) {
                                        $isSelectable = true;
                                    }
                                    $isReceivable = $canReceive && in_array($assetStatus, ['in_transit', 'approved']);
                                @endphp
                                <tr class="{{ !$isSelectable && in_array($transfer->transfer_status, ['pending', 'in_transit', 'partial_received']) ? 'opacity-50 bg-gray-100' : 'hover:bg-gray-50' }}">
                                    @if (in_array($transfer->transfer_status, ['pending', 'in_transit', 'partial_received']))
                                        <td class="px-2 py-2 border text-center">
                                            <input type="checkbox" class="item-checkbox" value="{{ $asset->id }}"
                                                data-cost="{{ $asset->asset_cost ?? 0 }}"
                                                {{ !$isSelectable ? 'disabled' : '' }}>
                                        </td>
                                    @endif
                                    <td class="px-4 py-2 border font-medium">{{ $asset->asset_no }}</td>
                                    <td class="px-4 py-2 border">{{ $asset->asset_name ?? '-' }}</td>
                                    <td class="px-4 py-2 border">{{ $asset->group->name ?? '-' }}</td>
                                    <td class="px-4 py-2 border text-center">{{ number_format($asset->asset_cost ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 border text-center">
                                        <span class="flex items-center justify-center gap-1 font-medium {{ $assetColor }}">
                                            @if ($assetStatus === 'received')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            @elseif($assetStatus === 'returned' || $assetStatus === 'rejected')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                            @endif
                                            {{ ucfirst(str_replace('_', ' ', $assetStatus)) }}
                                        </span>
                                    </td>

                                    {{-- Inline receive fields (per asset) --}}
                                    @if ($canReceive)
                                        <td class="px-3 py-2 border">
                                            @if ($isReceivable)
                                                <select name="items[{{ $asset->id }}][user_id]"
                                                    class="border rounded px-2 py-1 text-xs w-36">
                                                    <option value="">-- User --</option>
                                                    @foreach($toBranchUsers as $u)
                                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 border">
                                            @if ($isReceivable)
                                                <select name="items[{{ $asset->id }}][department_id]"
                                                    class="border rounded px-2 py-1 text-xs w-36">
                                                    <option value="">-- Dept --</option>
                                                    @foreach($departments as $dept)
                                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 border">
                                            @if ($isReceivable)
                                                <input type="file" name="items[{{ $asset->id }}][photo]"
                                                    accept="image/*" class="text-xs w-32">
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    @endif

                                    @if (in_array($transfer->transfer_status, ['in_transit', 'partial_received']))
                                        <td class="px-4 py-2 border text-center">
                                            @if ($isReceivable)
                                                <button type="button"
                                                    onclick="updateSingleAsset({{ $transfer->id }}, 'returned', {{ $asset->id }})"
                                                    class="text-red-600 hover:underline text-xs font-medium">
                                                    Return
                                                </button>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-4 text-center text-gray-500 border">
                                        No items in this transfer
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        {{-- Remark --}}
        @if($transfer->transfer_remark)
            <div class="mb-4">
                <p><strong>Remark:</strong> {{ $transfer->transfer_remark }}</p>
            </div>
        @endif

        {{-- Activity Log --}}
        @if ($transfer->transfer_log)
            <div class="mb-4">
                <p class="font-semibold mb-1">Activity Log:</p>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    @foreach (array_filter(explode("\n", $transfer->transfer_log)) as $log)
                        <li>{{ trim($log) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Footer Action Buttons --}}
        <div class="mt-6 flex justify-end space-x-2">
            @if ($transfer->transfer_status === 'pending' && auth()->user()->accessLevel?->approve_disaprove_transfer)
                <button onclick="rejectTransfer({{ $transfer->id }})"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium bg-red-100 text-red-700 hover:bg-red-200">
                    Reject All
                </button>
                <button onclick="approveTransfer({{ $transfer->id }})"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium bg-green-600 text-white hover:bg-green-700">
                    Approve
                </button>
            @endif

            @if ($transfer->transfer_status === 'approved' && $hasAccessToFromBranch)
                <button onclick="openSendModal({{ $transfer->id }})"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium bg-blue-600 text-white hover:bg-blue-700">
                    Send
                </button>
            @endif

            @if ($canReceive)
                <button type="button" onclick="submitReceiveSelected()"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium bg-green-600 text-white hover:bg-green-700">
                    Receive Selected
                </button>
            @endif
        </div>

    </div>
</div>

{{-- Send Modal --}}
<div id="sendModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Select Shipping Option</h3>
        <form id="sendForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="transfer_status" value="in_transit">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Option *</label>
                <select name="shipping_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select shipping option</option>
                    @foreach($shippingOptions ?? [] as $option)
                        <option value="{{ $option->id ?? '' }}">{{ $option->name ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeSendModal()"
                    class="px-4 py-2 text-sm border rounded hover:bg-gray-50">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Send Transfer</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Checkbox select-all
    document.getElementById('selectAllItems')?.addEventListener('click', function() {
        document.querySelectorAll('.item-checkbox:not(:disabled)').forEach(cb => cb.checked = this.checked);
    });
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        cb.addEventListener('click', function() {
            var all = document.querySelectorAll('.item-checkbox:not(:disabled)');
            var checked = document.querySelectorAll('.item-checkbox:not(:disabled):checked');
            var sel = document.getElementById('selectAllItems');
            if (!sel) return;
            if (checked.length === 0) { sel.checked = false; sel.indeterminate = false; }
            else if (checked.length === all.length) { sel.checked = true; sel.indeterminate = false; }
            else { sel.checked = false; sel.indeterminate = true; }
        });
    });

    function submitReceiveSelected() {
        var checked = document.querySelectorAll('.item-checkbox:checked');
        if (!checked.length) { alert('Please select at least one item to receive.'); return; }

        var missing = [];
        checked.forEach(function(cb) {
            var id = cb.value;
            var userSel = document.querySelector('select[name="items[' + id + '][user_id]"]');
            var deptSel = document.querySelector('select[name="items[' + id + '][department_id]"]');
            var photoIn = document.querySelector('input[name="items[' + id + '][photo]"]');
            var label   = cb.closest('tr')?.querySelector('td:nth-child(2)')?.textContent?.trim() || ('Item ' + id);

            var errors = [];
            if (!userSel || !userSel.value)   errors.push('User');
            if (!deptSel || !deptSel.value)   errors.push('Department');
            if (!photoIn || !photoIn.files?.length) errors.push('Photo');
            if (errors.length) missing.push(label + ': missing ' + errors.join(', '));
        });

        if (missing.length) {
            alert('Please fill in the required fields before receiving:\n\n' + missing.join('\n'));
            return;
        }

        document.getElementById('receiveAssetIds').value = Array.from(checked).map(cb => cb.value).join(',');
        document.getElementById('receiveForm').submit();
    }

    function approveTransfer(transferId) {
        var selected = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
        var all = Array.from(document.querySelectorAll('.item-checkbox:not(:disabled)')).map(cb => cb.value);
        if (!selected.length) { alert('Please select at least one item to approve.'); return; }
        var rejected = all.length - selected.length;
        var msg = rejected > 0
            ? 'Approve ' + selected.length + ' item(s) and reject ' + rejected + ' item(s)?'
            : 'Approve ' + selected.length + ' item(s)?';
        if (!confirm(msg)) return;
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/asset-transfer/' + transferId + '/approve-items';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
            + '<input type="hidden" name="approved_asset_ids" value="' + selected.join(',') + '">';
        document.body.appendChild(form);
        form.submit();
    }

    function rejectTransfer(transferId) {
        var remark = prompt('Please enter rejection reason:');
        if (!remark) return;
        if (!confirm('Are you sure you want to reject ALL items in this transfer?')) return;
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/asset-transfer/' + transferId + '/status';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
            + '<input type="hidden" name="_method" value="PUT">'
            + '<input type="hidden" name="transfer_status" value="rejected">'
            + '<input type="hidden" name="reject_all" value="1">'
            + '<input type="hidden" name="remark" value="' + remark.replace(/"/g, '&quot;') + '">';
        document.body.appendChild(form);
        form.submit();
    }

    function updateSingleAsset(transferId, status, assetId) {
        if (!confirm('Are you sure you want to ' + status + ' this asset?')) return;
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/asset-transfer/' + transferId + '/status';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
            + '<input type="hidden" name="_method" value="PUT">'
            + '<input type="hidden" name="transfer_status" value="' + status + '">'
            + '<input type="hidden" name="asset_id" value="' + assetId + '">';
        document.body.appendChild(form);
        form.submit();
    }

    function openSendModal(transferId) {
        document.getElementById('sendForm').action = '/asset-transfer/' + transferId + '/status';
        var modal = document.getElementById('sendModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeSendModal() {
        var modal = document.getElementById('sendModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('sendModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeSendModal();
    });
</script>
@endsection
