<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingCategoryController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $categories = MarketingCategory::withCount('items')
            ->orderBy('name')
            ->get();

        return view('marketingModule.category.list', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:inventory_marketing_category,name',
        ]);

        MarketingCategory::create(['name' => $request->name]);

        return redirect('/marketing/categories')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:inventory_marketing_category,name,' . $id,
        ]);

        $category = MarketingCategory::findOrFail($id);
        $category->update(['name' => $request->name]);

        return redirect('/marketing/categories')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = MarketingCategory::withCount('items')->findOrFail($id);

        if ($category->items_count > 0) {
            return redirect()->back()->with(
                'error',
                "Cannot delete category. It has {$category->items_count} item(s) assigned."
            );
        }

        $category->delete();

        return redirect('/marketing/categories')->with('success', 'Category deleted successfully.');
    }
}
