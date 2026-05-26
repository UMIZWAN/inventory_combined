<?php

namespace App\Http\Controllers;

use App\Models\AssetAccessLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetAccessLevelController extends Controller
{
    /**
     * Display a listing of access levels
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $query = AssetAccessLevel::withCount('users');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $accessLevels = $query->orderByRaw('COALESCE(sort_order, 9999) ASC, id ASC')->get();
        $permissions = AssetAccessLevel::getPermissionFields();

        return view('access_levels.index', compact('accessLevels', 'permissions'));
    }

    /**
     * Store a newly created access level
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:inventory_asset_access_level,name',
        ]);

        $data = ['name' => $request->name];

        // Add all permission fields
        foreach (array_keys(AssetAccessLevel::getPermissionFields()) as $field) {
            $data[$field] = $request->has($field) ? true : false;
        }

        AssetAccessLevel::create($data);

        return redirect()->back()->with('success', 'Access level created successfully!');
    }

    /**
     * Update the specified access level
     */
    public function update(Request $request, $id)
    {
        $accessLevel = AssetAccessLevel::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:inventory_asset_access_level,name,' . $id,
        ]);

        $data = ['name' => $request->name];

        // Update all permission fields
        foreach (array_keys(AssetAccessLevel::getPermissionFields()) as $field) {
            $data[$field] = $request->has($field) ? true : false;
        }

        $accessLevel->update($data);

        return redirect()->back()->with('success', 'Access level updated successfully!');
    }

    /**
     * Remove the specified access level
     */
    public function destroy($id)
    {
        $accessLevel = AssetAccessLevel::findOrFail($id);

        // Check if any users are assigned to this access level
        if ($accessLevel->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete access level. It is assigned to ' . $accessLevel->users()->count() . ' user(s).');
        }

        $accessLevel->delete();

        return redirect()->back()->with('success', 'Access level deleted successfully!');
    }

    /**
     * Save drag-and-drop sort order
     */
    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:inventory_asset_access_level,id']);

        foreach ($request->ids as $order => $id) {
            AssetAccessLevel::where('id', $id)->update(['sort_order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Duplicate an access level
     */
    public function duplicate($id)
    {
        $accessLevel = AssetAccessLevel::findOrFail($id);

        $newAccessLevel = $accessLevel->replicate();
        $newAccessLevel->name = $accessLevel->name . ' (Copy)';
        $newAccessLevel->save();

        return redirect()->back()->with('success', 'Access level duplicated successfully!');
    }
}
