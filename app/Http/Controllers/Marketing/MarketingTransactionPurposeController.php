<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingTransactionPurpose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingTransactionPurposeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $purposes = MarketingTransactionPurpose::orderBy('transaction_purpose_name')->get();

        return view('marketingModule.transaction-purpose.list', compact('purposes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_purpose_name' => 'required|string|max:255|unique:inventory_marketing_transaction_purpose,transaction_purpose_name',
        ]);

        MarketingTransactionPurpose::create([
            'transaction_purpose_name' => $request->transaction_purpose_name,
        ]);

        return redirect('/marketing/transaction-purposes')->with('success', 'Transaction purpose created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'transaction_purpose_name' => 'required|string|max:255|unique:inventory_marketing_transaction_purpose,transaction_purpose_name,' . $id,
        ]);

        $purpose = MarketingTransactionPurpose::findOrFail($id);
        $purpose->update([
            'transaction_purpose_name' => $request->transaction_purpose_name,
        ]);

        return redirect('/marketing/transaction-purposes')->with('success', 'Transaction purpose updated successfully.');
    }

    public function destroy($id)
    {
        $purpose = MarketingTransactionPurpose::findOrFail($id);
        $purpose->delete();

        return redirect('/marketing/transaction-purposes')->with('success', 'Transaction purpose deleted successfully.');
    }
}
