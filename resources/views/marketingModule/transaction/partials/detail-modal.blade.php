{{--
    Shared transaction detail modal.
    Triggered by any button with class .view-txn-btn and data-id="{tx_id}".
    Fetches /marketing/transactions/{id}/detail and populates inline.
--}}

<div id="txn-detail-modal"
    class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50 overflow-auto py-10">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full mx-4">
        <div class="p-6">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-6 pb-4 border-b border-gray-200">
                <div>
                    <h2 class="text-xl font-bold text-gray-900" id="txn-ref">Loading…</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        <span id="txn-type" class="font-medium"></span>
                        <span class="mx-1">·</span>
                        <span id="txn-status" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"></span>
                    </p>
                </div>
                <button type="button" id="close-txn-modal"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div id="txn-body" class="space-y-5">

                {{-- Metadata grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 uppercase">From Branch</div>
                        <div class="font-medium text-gray-800" id="txn-from-branch">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">To Branch</div>
                        <div class="font-medium text-gray-800" id="txn-to-branch">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Purpose</div>
                        <div class="font-medium text-gray-800" id="txn-purpose">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Shipping</div>
                        <div class="font-medium text-gray-800" id="txn-shipping">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Created By</div>
                        <div class="font-medium text-gray-800" id="txn-created-by">—</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Created At</div>
                        <div class="font-medium text-gray-800" id="txn-created-at">—</div>
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <div class="text-xs text-gray-500 uppercase mb-2">Items</div>
                    <div class="border border-gray-200 rounded overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-700">Item</th>
                                    <th class="px-3 py-2 text-right text-xs font-bold text-gray-700">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs font-bold text-gray-700">Status</th>
                                </tr>
                            </thead>
                            <tbody id="txn-items" class="divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Totals + Remarks --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Total Cost</div>
                        <div class="font-semibold text-gray-900 text-base" id="txn-total">RM 0.00</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 uppercase">Attachment</div>
                        <div id="txn-attachment" class="text-sm">—</div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-xs text-gray-500 uppercase">Remark</div>
                        <div class="text-gray-700" id="txn-remark">—</div>
                    </div>
                </div>
            </div>

            {{-- Loader overlay --}}
            <div id="txn-loading" class="hidden text-center py-8 text-gray-400">Loading…</div>
            <div id="txn-error" class="hidden text-center py-8 text-red-500"></div>

            {{-- Footer --}}
            <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
                <button type="button" id="close-txn-modal-2"
                    class="px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modal       = document.getElementById('txn-detail-modal');
    const body        = document.getElementById('txn-body');
    const loadingEl   = document.getElementById('txn-loading');
    const errorEl     = document.getElementById('txn-error');

    const STATUS_STYLE = {
        'RECEIVED':   'bg-green-100 text-green-700',
        'IN-TRANSIT': 'bg-amber-100 text-amber-700',
        'APPROVED':   'bg-blue-100 text-blue-700',
        'REJECTED':   'bg-red-100 text-red-700',
        'COMPLETED':  'bg-emerald-100 text-emerald-700',
        'REQUESTED':  'bg-slate-100 text-slate-700',
    };

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    function showLoading() {
        body.classList.add('hidden');
        errorEl.classList.add('hidden');
        loadingEl.classList.remove('hidden');
    }
    function showError(msg) {
        body.classList.add('hidden');
        loadingEl.classList.add('hidden');
        errorEl.textContent = msg || 'Failed to load.';
        errorEl.classList.remove('hidden');
    }
    function showBody() {
        loadingEl.classList.add('hidden');
        errorEl.classList.add('hidden');
        body.classList.remove('hidden');
    }

    function fmtBranch(b) {
        return b && b.code ? `MKT-${b.code}` : '—';
    }
    function fmtDate(s) {
        if (!s) return '—';
        const d = new Date(s);
        if (isNaN(d)) return '—';
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        return `${dd}/${mm}/${d.getFullYear()}`;
    }
    function fmtMoney(n) {
        const num = parseFloat(n);
        return isNaN(num) ? '—' : 'RM ' + num.toFixed(2);
    }

    function populate(txn) {
        document.getElementById('txn-ref').textContent = txn.running_number ?? '—';
        document.getElementById('txn-type').textContent = txn.transaction_type ?? '';

        const statusEl = document.getElementById('txn-status');
        statusEl.textContent = txn.transaction_status ?? '—';
        statusEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' +
            (STATUS_STYLE[txn.transaction_status] || 'bg-gray-100 text-gray-600');

        document.getElementById('txn-from-branch').textContent = fmtBranch(txn.fromBranch);
        document.getElementById('txn-to-branch').textContent   = fmtBranch(txn.toBranch);
        document.getElementById('txn-purpose').textContent     = txn.purpose?.transaction_purpose_name ?? '—';
        document.getElementById('txn-shipping').textContent    = txn.shippingOption?.name ?? '—';
        document.getElementById('txn-created-by').textContent  = txn.creator?.name ?? '—';
        document.getElementById('txn-created-at').textContent  = fmtDate(txn.created_at);
        document.getElementById('txn-total').textContent       = fmtMoney(txn.transaction_total_cost);
        document.getElementById('txn-remark').textContent      = txn.transaction_remark || '—';

        const attachEl = document.getElementById('txn-attachment');
        if (txn.attachment) {
            attachEl.innerHTML = `<a href="/storage/${txn.attachment}" target="_blank" class="text-blue-600 hover:underline">View file</a>`;
        } else {
            attachEl.textContent = '—';
        }

        const itemsTbody = document.getElementById('txn-items');
        itemsTbody.innerHTML = '';
        (txn.items || []).forEach(line => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2 text-gray-800">${line.item?.name ?? '—'}</td>
                <td class="px-3 py-2 text-right text-gray-700">${line.item_unit ?? 0}</td>
                <td class="px-3 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${STATUS_STYLE[line.status] || 'bg-gray-100 text-gray-600'}">${line.status ?? '—'}</span></td>
            `;
            itemsTbody.appendChild(tr);
        });
    }

    async function loadDetail(id) {
        showLoading();
        openModal();
        try {
            const res = await fetch(`/marketing/transactions/${id}/detail`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            if (json.success && json.data) {
                populate(json.data);
                showBody();
            } else {
                showError(json.message || 'Not found.');
            }
        } catch (e) {
            showError('Network error: ' + e.message);
        }
    }

    // Bind any .view-txn-btn (works for current rows; if DT redraws, call window.bindTxnViewButtons() again)
    function bindButtons() {
        document.querySelectorAll('.view-txn-btn').forEach(btn => {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';
            btn.addEventListener('click', () => loadDetail(btn.dataset.id));
        });
    }
    bindButtons();
    window.bindTxnViewButtons = bindButtons;

    document.getElementById('close-txn-modal')?.addEventListener('click', closeModal);
    document.getElementById('close-txn-modal-2')?.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
})();
</script>
