{{-- resources/views/asset/partials/form.blade.php --}}

<div class="flex gap-6 max-h-[70vh] overflow-auto">

    {{-- ================= LEFT : IMAGE ================= --}}
    <div class="w-56">
        {{-- Asset Image --}}
        <div>
            <label class="block mb-1">Asset Image</label>

            {{-- Container for existing image (populated via JS in edit mode) --}}
            @if (isset($isEdit) && $isEdit)
                <div id="existingImageContainer" class="mb-2 relative inline-block hidden">
                    <img id="existingImage" src="" alt="Asset Image"
                        class="w-32 h-32 object-cover rounded border cursor-pointer"
                        onclick="if(this.src) openImagePreview(this.src)">

                    {{-- Delete image button --}}
                    {{-- <button type="button" id="deleteImageBtn"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 shadow-lg text-sm font-bold"
                        title="Delete image">
                        ×
                    </button> --}}
                </div>
                <p id="replaceImageText" class="text-xs text-gray-500 mb-1 hidden">Upload new to replace</p>
            @else
                <div id="imagePlaceholder" class="mb-3">
                    <div
                        class="w-full h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                        <div class="text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <p class="text-sm">No image</p>
                        </div>
                    </div>
                </div>
            @endif

            <input name="asset_image" type="file" accept="image/*" class="w-full border rounded px-3 py-2">
            <small class="text-gray-500 text-xs">Max 2MB (JPEG, PNG, JPG, GIF)</small>
        </div>
    </div>

    {{-- ================= RIGHT : FORM ================= --}}
    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Asset No --}}
        <div>
            <label class="block mb-1">Asset No</label>
            <input name="asset_no" class="w-full border rounded px-3 py-2" required>
        </div>

        {{-- Old Asset No --}}
        <div>
            <label class="block mb-1">Old Asset No</label>
            <input name="old_asset_no" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Asset Name --}}
        <div>
            <label class="block mb-1">Asset Name</label>
            <input name="asset_name" class="w-full border rounded px-3 py-2">
        </div>



        {{-- Color --}}
        <div>
            <label class="block mb-1">Color</label>
            <select name="color" class="w-full border rounded px-3 py-2">
                <option value="">-- No Color --</option>
                @php
                    $presetColors = [
                        '#ef4444' => 'Red',
                        '#f97316' => 'Orange',
                        '#eab308' => 'Yellow',
                        '#22c55e' => 'Green',
                        '#06b6d4' => 'Cyan',
                        '#3b82f6' => 'Blue',
                        '#8b5cf6' => 'Purple',
                        '#ec4899' => 'Pink',
                        '#6b7280' => 'Gray',
                        '#000000' => 'Black',
                        '#ffffff' => 'White',
                        '#92400e' => 'Brown',
                    ];
                @endphp
                @foreach ($presetColors as $hex => $name)
                    <option value="{{ $hex }}" {{ ($asset->color ?? '') === $hex ? 'selected' : '' }}
                        style="background-color: {{ $hex }}; color: {{ in_array($hex, ['#000000', '#8b5cf6', '#3b82f6', '#ef4444', '#22c55e', '#92400e']) ? '#fff' : '#000' }}">
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- UOM --}}
        <div>
            <label class="block mb-1">UOM</label>
            <input name="asset_uom" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Cost --}}
        <div>
            <label class="block mb-1">Cost</label>
            <input name="asset_cost" type="number" step="0.01" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Asset Group --}}
        <div>
            <label class="block mb-1">Asset Group</label>
            <select name="group_id" class="w-full border rounded px-3 py-2 searchable-select">
                <option value="">-- Select --</option>
                @foreach ($groups as $group)
                    <option value="{{ $group->id }}">
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Branch --}}
        <div>
            <label class="block mb-1">Branch</label>
            <select name="branch_id" class="w-full border rounded px-3 py-2 searchable-select">
                <option value="">-- Select --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">
                        {{ $branch->branch_name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- User --}}
        <div>
            <label class="block mb-1">User</label>
            <select name="user_id" class="w-full border rounded px-3 py-2 searchable-select">
                <option value="">-- Select --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Department --}}
        <div>
            <label class="block mb-1">Department</label>
            <select name="department_id" class="w-full border rounded px-3 py-2 searchable-select">
                <option value="">-- Select --</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Supplier --}}
        <div>
            <label class="block mb-1">Supplier</label>
            <select name="supplier_id" class="w-full border rounded px-3 py-2 searchable-select">
                <option value="">-- Select --</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Invoice No --}}
        <div>
            <label class="block mb-1">Invoice No</label>
            <input name="inv_no" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Warranty --}}
        <div>
            <label class="block mb-1">Has Warranty</label>
            <select name="has_warranty" class="w-full border rounded px-3 py-2">
                <option value="">-- Select --</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>

        <div>
            <label class="block mb-1">Warranty Period</label>
            <input name="warranty_period" value="{{ $asset->warranty_period ?? '' }}" ype="text"
                placeholder="e.g. 6 Months" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Purchase Date --}}
        <div>
            <label class="block mb-1">Purchase Date</label>
            <input name="asset_purchase_date"
                value="{{ isset($asset->asset_purchase_date) ? \Carbon\Carbon::parse($asset->asset_purchase_date)->format('Y-m-d') : '' }}"
                type="date" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Lifespan --}}
        <div>
            <label class="block mb-1">Lifespan</label>
            <input name="asset_lifespan" value="{{ $asset->asset_lifespan ?? '' }}" type="text"
                placeholder="e.g. 5-6 Years" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Service Interval --}}
        <div>
            <label class="block mb-1">Service Interval</label>
            <input name="asset_service_interval" value="{{ $asset->asset_service_interval ?? '' }}" type="text"
                placeholder="e.g. 6 Months" class="w-full border rounded px-3 py-2">
        </div>

        {{-- Venue --}}
        <div>
            <label class="block mb-1">Venue</label>
            <input name="venue" value="{{ $asset->venue ?? '' }}" class="w-full border rounded px-3 py-2">
        </div>
    </div>
</div>

{{-- Image Preview Modal (Only added once, works for all images) --}}
<div id="imagePreviewModal"
    class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center"
    onclick="closeImagePreview()">
    <div class="relative max-w-[90vw] max-h-[90vh]">
        <img id="previewImage" src="" alt="Asset Image" class="max-w-full max-h-[90vh] object-contain">
        <button onclick="closeImagePreview()"
            class="absolute top-4 right-4 bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-200 shadow-lg">
            ×
        </button>
    </div>
</div>

<script>
    // Image preview functions
    function openImagePreview(imageUrl, assetId) {
        const modal = document.getElementById('imagePreviewModal');
        const previewImage = document.getElementById('previewImage');
        previewImage.src = imageUrl;
        modal.classList.remove('hidden');
    }

    function closeImagePreview() {
        const modal = document.getElementById('imagePreviewModal');
        modal.classList.add('hidden');
    }

    // Delete asset image
    function deleteAssetImage(assetId) {
        if (!confirm('Are you sure you want to delete this image?')) {
            return;
        }

        fetch(`/asset/${assetId}/delete-image`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting image');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting image');
            });
    }
</script>
