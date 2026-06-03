<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MarketingAccessLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MarketingStaffController extends Controller
{
    public function showStaffList(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $users = User::with(['branches' => function ($q) {
            $q->where('is_active', true);
        }])
            ->where('is_accessible', true)
            ->get();

        $branches     = Branch::where('is_active', true)->get();
        $accessLevels = MarketingAccessLevel::orderByRaw('COALESCE(sort_order, 9999) ASC, id ASC')->get();

        return view('staff.staffList', [
            'users'        => $users,
            'branches'     => $branches,
            'accessLevels' => $accessLevels,
            'module'       => 'marketing',
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                      => 'required|string',
            'username'                  => 'nullable|string',
            'password'                  => 'nullable|string|min:4',
            'branch_id'                 => 'nullable|array',
            'branch_id.*'               => 'exists:branches,id',
            'marketing_access_level_id' => 'required|exists:inventory_marketing_access_level,id',
        ], [
            'name.required'                      => 'Please provide name.',
            'marketing_access_level_id.required' => 'Please select an access level.',
        ]);

        if ($request->filled('username')) {
            $existing = User::where('username', $request->username)
                ->where('is_active', true)
                ->first();
            if ($existing) {
                return back()->withErrors(['username' => 'Username already taken.']);
            }
        }

        $user = User::create([
            'name'                      => $request->name,
            'username'                  => $request->username ?: null,
            'email'                     => $request->email,
            'password'                  => $request->filled('password') ? Hash::make($request->password) : null,
            'marketing_access_level_id' => $request->marketing_access_level_id,
        ]);

        if ($request->has('branch_id')) {
            $user->branches()->sync($request->branch_id);
        }

        return redirect('/marketing/staff')->with('success', 'User registered successfully.');
    }

    public function registerNoLogin(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'branch_id'   => 'nullable|array',
            'branch_id.*' => 'exists:branches,id',
        ], [
            'name.required' => 'Please provide name.',
        ]);

        $user = User::create([
            'name' => $request->name,
        ]);

        if ($request->has('branch_id')) {
            $user->branches()->sync($request->branch_id);
        }

        return redirect('/marketing/staff')->with('success', 'Staff added successfully.');
    }

    public function updateNoLogin(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'        => 'required|string',
            'branch_id'   => 'nullable|array',
            'branch_id.*' => 'exists:branches,id',
        ], [
            'name.required' => 'Please provide name.',
        ]);

        $user->update(['name' => $request->name]);
        $user->branches()->sync($request->branch_id ?? []);

        return redirect('/marketing/staff')->with('success', 'Staff updated successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'                      => 'required|string',
            'username'                  => 'required|string',
            'password'                  => 'nullable|string|min:4',
            'branch_id'                 => 'nullable|array',
            'branch_id.*'               => 'exists:branches,id',
            'marketing_access_level_id' => 'required|exists:inventory_marketing_access_level,id',
        ], [
            'name.required'                      => 'Please provide name.',
            'username.required'                  => 'Please provide username.',
            'marketing_access_level_id.required' => 'Please select an access level.',
        ]);

        $existing = User::where('username', $request->username)
            ->where('is_active', true)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return back()->withErrors(['username' => 'Username already taken.']);
        }

        $data = [
            'name'                      => $request->name,
            'username'                  => $request->username,
            'marketing_access_level_id' => $request->marketing_access_level_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('branch_id')) {
            $user->branches()->sync($request->branch_id);
        } else {
            $user->branches()->sync([]);
        }

        return redirect('/marketing/staff')->with('success', 'User updated successfully.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id && $user->is_active) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with(
            'success',
            $user->is_active
                ? 'Staff activated successfully.'
                : 'Staff deactivated successfully.'
        );
    }
}
