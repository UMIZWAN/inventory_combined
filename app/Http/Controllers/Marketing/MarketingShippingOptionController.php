<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ShippingOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingShippingOptionController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $shippingOptions = ShippingOption::orderBy('name')->get();

        return view('marketingModule.shipping-option.list', compact('shippingOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255|unique:shipping_options,name',
            'is_active' => 'nullable|boolean',
        ]);

        ShippingOption::create([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect('/marketing/shipping-options')->with('success', 'Shipping option created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'      => 'required|string|max:255|unique:shipping_options,name,' . $id,
            'is_active' => 'nullable|boolean',
        ]);

        $option = ShippingOption::findOrFail($id);
        $option->update([
            'name'      => $request->name,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect('/marketing/shipping-options')->with('success', 'Shipping option updated successfully.');
    }

    public function destroy($id)
    {
        $option = ShippingOption::findOrFail($id);
        $option->delete();

        return redirect('/marketing/shipping-options')->with('success', 'Shipping option deleted successfully.');
    }
}
