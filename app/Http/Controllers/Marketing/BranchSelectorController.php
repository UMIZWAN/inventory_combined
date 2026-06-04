<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchSelectorController extends Controller
{
    public function set(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if ($request->filled('branch_id')) {
            session(['marketing_branch_id' => (int) $request->branch_id]);
        } else {
            session()->forget('marketing_branch_id');
        }

        return back();
    }
}
