<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingCategory;
use App\Models\MarketingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MarketingItemController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (!Auth::user()->marketing_access_level_id) {
            return redirect('/')->with('error', 'You do not have access to the Marketing system.');
        }

        $items = MarketingItem::with(['category', 'values.branch'])
            ->orderBy('name')
            ->get();

        $categories = MarketingCategory::orderBy('name')->get();

        return view('marketing.item.list', compact('items', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('marketing/items', 'public');
        }

        MarketingItem::create($data);

        return redirect('/marketing')->with('success', 'Item created successfully.');
    }

    public function update(Request $request, $id)
    {
        $item = MarketingItem::findOrFail($id);
        $data = $this->validateItem($request, $id);

        if ($request->hasFile('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('marketing/items', 'public');
        }

        $item->update($data);

        return redirect('/marketing')->with('success', 'Item updated successfully.');
    }

    public function destroy($id)
    {
        $item = MarketingItem::findOrFail($id);

        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect('/marketing')->with('success', 'Item deleted successfully.');
    }

    private function validateItem(Request $request, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:inventory_marketing_items,item_running_number'
            . ($ignoreId ? ',' . $ignoreId : '');

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'item_running_number' => 'required|string|max:255|' . $uniqueRule,
            'description'         => 'nullable|string',
            'type'                => 'nullable|string|max:255',
            'category_id'         => 'required|exists:inventory_marketing_category,id',
            'stable_unit'         => 'nullable|integer|min:0',
            'purchase_cost'       => 'nullable|numeric|min:0',
            'sales_cost'          => 'nullable|numeric|min:0',
            'unit_measure'        => 'required|string|max:50',
            'image'               => 'nullable|image|max:5120',
            'remark'              => 'nullable|string',
        ]);

        unset($validated['image']);
        $validated['stable_unit'] = $validated['stable_unit'] ?? 0;

        return $validated;
    }
}
