{{-- resources/views/marketing/item/partials/form.blade.php --}}

<div class="flex gap-6 max-h-[70vh] overflow-auto">

    {{-- ================= LEFT : IMAGE ================= --}}
    <div class="w-56">
        <div>
            <label class="block mb-1">Item Image</label>

            {{-- Existing image (shown in edit mode) --}}
            <div id="existingImageContainer" class="mb-2 relative inline-block hidden">
                <img id="existingImage" src="" alt="Item Image"
                    class="w-32 h-32 object-cover rounded border cursor-pointer"
                    onclick="if(this.src) openImagePreview(this.src)">
            </div>
            <p id="replaceImageText" class="text-xs text-gray-500 mb-1 hidden">Upload new to replace</p>

            {{-- Placeholder (shown in add mode) --}}
            <div id="imagePlaceholder" class="mb-3">
                <div class="w-full h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <div class="text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm">No image</p>
                    </div>
                </div>
            </div>

            <input name="image" type="file" accept="image/*" class="w-full border rounded px-3 py-2">
            <small class="text-gray-500 text-xs">Max 5MB (JPEG, PNG, JPG, GIF)</small>
        </div>
    </div>

    {{-- ================= RIGHT : FORM ================= --}}
    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Item Number --}}
        <div>
            <label class="block mb-1">Item Number</label>
            <input name="item_running_number" id="item-running-number"
                class="w-full border rounded px-3 py-2" required>
        </div>

        {{-- Name --}}
        <div class="md:col-span-2">
            <label class="block mb-1">Name</label>
            <input name="name" id="item-name" class="w-full border rounded px-3 py-2" required>
        </div>

        {{-- Category --}}
        <div>
            <label class="block mb-1">Category</label>
            <select name="category_id" id="item-category" class="w-full border rounded px-3 py-2" required>
                <option value="">-- Select --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Type --}}
        <div>
            <label class="block mb-1">Type</label>
            <input name="type" id="item-type" class="w-full border rounded px-3 py-2"
                placeholder="e.g. Banner, Brochure">
        </div>

        {{-- UoM --}}
        <div>
            <label class="block mb-1">UoM</label>
            <input name="unit_measure" id="item-uom" class="w-full border rounded px-3 py-2"
                placeholder="pcs, box, roll" required>
        </div>

        {{-- Purchase Cost --}}
        <div>
            <label class="block mb-1">Purchase Cost</label>
            <input name="purchase_cost" id="item-purchase-cost" type="number" step="0.0001" min="0"
                class="w-full border rounded px-3 py-2">
        </div>

        {{-- Sales Cost --}}
        <div>
            <label class="block mb-1">Sales Cost</label>
            <input name="sales_cost" id="item-sales-cost" type="number" step="0.0001" min="0"
                class="w-full border rounded px-3 py-2">
        </div>

        {{-- Stable Unit --}}
        <div>
            <label class="block mb-1">Stable Unit (min stock)</label>
            <input name="stable_unit" id="item-stable-unit" type="number" min="0" value="0"
                class="w-full border rounded px-3 py-2">
        </div>

        {{-- Description --}}
        <div class="md:col-span-3">
            <label class="block mb-1">Description</label>
            <textarea name="description" id="item-description" rows="2"
                class="w-full border rounded px-3 py-2"></textarea>
        </div>

        {{-- Remark --}}
        <div class="md:col-span-3">
            <label class="block mb-1">Remark</label>
            <textarea name="remark" id="item-remark" rows="2"
                class="w-full border rounded px-3 py-2"></textarea>
        </div>
    </div>
</div>

{{-- Image preview modal (shared) --}}
<div id="imagePreviewModal"
    class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center"
    onclick="closeImagePreview()">
    <div class="relative max-w-[90vw] max-h-[90vh]">
        <img id="previewImage" src="" alt="Item Image" class="max-w-full max-h-[90vh] object-contain">
        <button type="button" onclick="closeImagePreview()"
            class="absolute top-4 right-4 bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-200 shadow-lg">
            ×
        </button>
    </div>
</div>

<script>
    function openImagePreview(imageUrl) {
        const modal = document.getElementById('imagePreviewModal');
        document.getElementById('previewImage').src = imageUrl;
        modal.classList.remove('hidden');
    }
    function closeImagePreview() {
        document.getElementById('imagePreviewModal').classList.add('hidden');
    }
</script>
