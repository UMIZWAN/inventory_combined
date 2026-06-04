{{-- Purpose --}}
<div class="mb-4">
    <label class="block font-medium mb-1">Purpose</label>
    <select name="transfer_purpose" class="w-full border rounded px-4 py-2" required>
        <option value="">[Select Purpose]</option>
        <option value="Branch Transfer">Branch Transfer</option>
        <option value="Other">Other</option>
    </select>
</div>

{{-- From Branch and To Branch side by side --}}
<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block font-medium mb-1">From Branch <span class="text-xs text-gray-500">(Auto-filled)</span></label>
        <select name="transfer_from" id="transfer_from_select" class="w-full border rounded px-4 py-2" required>
            <option value="">[Select Branch]</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block font-medium mb-1">To Branch</label>
        <select name="transfer_to" class="w-full border rounded px-4 py-2" required>
            <option value="">[Select Branch]</option>
            @foreach($allBranches ?? $branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Selected assets table --}}
<div class="mb-4">
    <div class="flex justify-between items-center mb-2">
        <label class="block font-medium">Selected Assets</label>
        <button type="button" onclick="addAssetRow()"
            class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
            + Add Asset
        </button>
    </div>

    <table class="w-full border rounded text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-2 py-1">#</th>
                <th class="border px-2 py-1">Asset No</th>
                <th class="border px-2 py-1">Asset Name</th>
                <th class="border px-2 py-1">Asset Cost (RM)</th>
                <th class="border px-2 py-1">Action</th>
            </tr>
        </thead>
        <tbody id="transferAssetsTable">
            {{-- JS will populate this --}}
        </tbody>
    </table>

    <div class="mt-2 text-right font-semibold">
        Total Cost: RM <span id="transferTotalCost">0.00</span>
    </div>
</div>

{{-- Remarks --}}
<div class="mb-4">
    <label class="block font-medium mb-1">Remarks</label>
    <textarea name="transfer_log" rows="3" class="w-full border rounded px-4 py-2"></textarea>
</div>