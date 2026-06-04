@php
    $itemsData = $items->mapWithKeys(fn ($i) => [$i->id => [
        'code'         => $i->item_running_number,
        'name'         => $i->name,
        'category'     => $i->category?->name ?? '',
        'unit_measure' => $i->unit_measure,
        'unit_cost'    => (float) ($i->purchase_cost ?? 0),
    ]])->all();
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">

    <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Stock Transfer</h2>

    <form id="stock-transfer-form" method="POST" action="{{ url('/marketing/transactions/transfer') }}">
        @csrf

        {{-- Top metadata grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            {{-- From Branch (locked to active) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">From Branch</label>
                @if ($activeBranch)
                    <input type="hidden" name="from_branch_id" value="{{ $activeBranch->id }}">
                    <input type="text" readonly value="{{ $activeBranch->branch_name }}"
                        class="w-full text-sm border border-gray-300 rounded px-3 py-2 bg-gray-50 text-gray-700">
                @else
                    <select name="from_branch_id" required
                        class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                        <option value="">[Select Branch — pick one from the topbar]</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- To Branch --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">To Branch</label>
                <select name="to_branch_id" required
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    <option value="">[Select Branch]</option>
                    @foreach ($branches as $b)
                        @if (!$activeBranch || $b->id !== $activeBranch->id)
                            <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Transfered By --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Transfered By</label>
                <input type="text" readonly value="{{ auth()->user()->name }}"
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 bg-gray-50 text-gray-700">
            </div>

            {{-- Purpose --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Purpose</label>
                <select name="transaction_purpose_id"
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    <option value="">[Select Type]</option>
                    @foreach ($purposes as $p)
                        <option value="{{ $p->id }}">{{ $p->transaction_purpose_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                <input type="text" readonly value="{{ now()->format('d/m/Y') }}"
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 bg-gray-50 text-gray-700">
            </div>

            {{-- Shipping Option --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Shipping Option</label>
                <select name="shipping_option_id"
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    <option value="">[Select Shipping]</option>
                    @foreach ($shippingOptions as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Items table --}}
        <div class="border border-gray-200 rounded mb-2 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-700">Item</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-700">Category</th>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-700">Unit of Measure</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-700">Quantity</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-700">Unit Price</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-700">Total Price</th>
                        <th class="px-3 py-2 text-center text-xs font-bold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody id="item-rows"></tbody>
            </table>
        </div>

        <button type="button" id="add-item-row" class="text-blue-600 text-sm hover:text-blue-700 mb-6">
            + Add Item
        </button>

        {{-- Total Amount --}}
        <div class="flex justify-end mb-6">
            <div class="w-64">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Total Amount (RM)</label>
                <input type="text" id="total-amount" readonly value="0.00"
                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 bg-gray-50 text-gray-700 text-right font-medium">
            </div>
        </div>

        {{-- Remarks --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks</label>
            <textarea name="remark" rows="3"
                class="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"></textarea>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded hover:bg-blue-700">
                Submit
            </button>
            <a href="{{ url('/marketing/transactions/transfer-list') }}"
                class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded hover:bg-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
(function () {
    const ITEMS = @json($itemsData);
    const tbody       = document.getElementById('item-rows');
    const addBtn      = document.getElementById('add-item-row');
    const totalAmount = document.getElementById('total-amount');
    let rowIndex      = 0;

    function rowHtml(idx) {
        const options = Object.entries(ITEMS)
            .map(([id, it]) => `<option value="${id}">${it.code} — ${it.name}</option>`)
            .join('');
        return `
            <tr class="border-b border-gray-100 item-row">
                <td class="px-3 py-2">
                    <select name="items[${idx}][item_id]" class="item-select w-full text-sm border border-gray-300 rounded px-2 py-1.5 outline-none" required>
                        <option value="">[Select]</option>${options}
                    </select>
                </td>
                <td class="px-3 py-2"><input type="text" readonly class="cat-cell w-full text-sm border border-gray-300 rounded px-2 py-1.5 bg-gray-50"></td>
                <td class="px-3 py-2"><input type="text" readonly class="uom-cell w-full text-sm border border-gray-300 rounded px-2 py-1.5 bg-gray-50"></td>
                <td class="px-3 py-2"><input type="number" min="1" step="1" value="1" name="items[${idx}][item_unit]" class="qty-cell w-full text-sm border border-gray-300 rounded px-2 py-1.5 outline-none text-right" required></td>
                <td class="px-3 py-2"><input type="number" min="0" step="0.01" value="0" name="items[${idx}][unit_price]" class="price-cell w-full text-sm border border-gray-300 rounded px-2 py-1.5 outline-none text-right"></td>
                <td class="px-3 py-2"><input type="text" readonly value="0.00" class="total-cell w-full text-sm border border-gray-300 rounded px-2 py-1.5 bg-gray-50 text-right"></td>
                <td class="px-3 py-2 text-center"><button type="button" class="remove-row text-red-500 hover:text-red-700">&times;</button></td>
            </tr>`;
    }

    function recalcRow(row) {
        const qty   = parseFloat(row.querySelector('.qty-cell').value)   || 0;
        const price = parseFloat(row.querySelector('.price-cell').value) || 0;
        row.querySelector('.total-cell').value = (qty * price).toFixed(2);
    }
    function recalcTotal() {
        let sum = 0;
        tbody.querySelectorAll('.item-row').forEach(r => {
            sum += parseFloat(r.querySelector('.total-cell').value) || 0;
        });
        totalAmount.value = sum.toFixed(2);
    }
    function bindRow(row) {
        const select = row.querySelector('.item-select');
        const qty    = row.querySelector('.qty-cell');
        const price  = row.querySelector('.price-cell');
        select.addEventListener('change', () => {
            const it = ITEMS[select.value];
            if (it) {
                row.querySelector('.cat-cell').value = it.category;
                row.querySelector('.uom-cell').value = it.unit_measure;
                price.value = it.unit_cost.toFixed(2);
                recalcRow(row); recalcTotal();
            } else {
                row.querySelector('.cat-cell').value = '';
                row.querySelector('.uom-cell').value = '';
            }
        });
        [qty, price].forEach(el => el.addEventListener('input', () => { recalcRow(row); recalcTotal(); }));
        row.querySelector('.remove-row').addEventListener('click', () => { row.remove(); recalcTotal(); });
    }
    function addRow() {
        tbody.insertAdjacentHTML('beforeend', rowHtml(rowIndex++));
        bindRow(tbody.lastElementChild);
    }
    addBtn.addEventListener('click', addRow);
    addRow();
})();
</script>
