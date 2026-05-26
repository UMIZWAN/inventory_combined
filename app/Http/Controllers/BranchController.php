<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * List branches
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $branches = Branch::orderBy('branch_name')->where('is_active', true)->get();

        return view('branch.branchList', compact('branches'));
    }

    /**
     * Store new branch
     */
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

        return redirect()
            ->back()
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Update branch
     */
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

        return redirect()
            ->back()
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Delete branch
     */
    public function deleteBranch($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->update([
            'is_active' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Branch deleted successfully.');
    }
}
