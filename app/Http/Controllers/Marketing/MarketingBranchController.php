<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingBranchController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $branches = Branch::orderBy('branch_name')->where('is_active', true)->get();

        return view('branch.branchList', [
            'branches' => $branches,
            'module'   => 'marketing',
        ]);
    }

    public function addBranch(Request $request)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'code'        => 'required|string|max:50',
        ]);

        Branch::create([
            'branch_name' => $request->branch_name,
            'code'        => $request->code,
        ]);

        return redirect('/marketing/branches')->with('success', 'Branch created successfully.');
    }

    public function updateBranch(Request $request, $id)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'code'        => 'required|string|max:50',
        ]);

        $branch = Branch::findOrFail($id);

        $branch->update([
            'branch_name' => $request->branch_name,
            'code'        => $request->code,
        ]);

        return redirect('/marketing/branches')->with('success', 'Branch updated successfully.');
    }

    public function deleteBranch($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->update(['is_active' => false]);

        return redirect('/marketing/branches')->with('success', 'Branch deleted successfully.');
    }
}
