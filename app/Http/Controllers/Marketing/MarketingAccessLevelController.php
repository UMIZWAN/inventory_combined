<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingAccessLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingAccessLevelController extends Controller
{
    /**
     * Display a listing of access levels
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $query = MarketingAccessLevel::withCount('users');

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $accessLevels = $query->orderByRaw('COALESCE(sort_order, 9999) ASC, id ASC')->get();
        $permissions  = MarketingAccessLevel::getPermissionFields();

        return view('access_levels.index', [
            'accessLevels' => $accessLevels,
            'permissions'  => $permissions,
            'module'       => 'marketing',
        ]);
    }

    /**
     * Store a newly created access level
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:inventory_marketing_access_level,name',
        ]);

        $data = ['name' => $request->name];

        foreach (array_keys(MarketingAccessLevel::getPermissionFields()) as $field) {
            $data[$field] = $request->has($field) ? true : false;
        }

        MarketingAccessLevel::create($data);

        return redirect()->back()->with('success', 'Access level created successfully!');
    }

    /**
     * Update the specified access level
     */
    public function update(Request $request, $id)
    {
        $accessLevel = MarketingAccessLevel::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:inventory_marketing_access_level,name,' . $id,
        ]);

        $data = ['name' => $request->name];

        foreach (array_keys(MarketingAccessLevel::getPermissionFields()) as $field) {
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
        $accessLevel = MarketingAccessLevel::findOrFail($id);

        if ($accessLevel->users()->count() > 0) {
            return redirect()->back()->with(
                'error',
                'Cannot delete access level. It is assigned to ' . $accessLevel->users()->count() . ' user(s).'
            );
        }

        $accessLevel->delete();

        return redirect()->back()->with('success', 'Access level deleted successfully!');
    }

    /**
     * Save drag-and-drop sort order
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:inventory_marketing_access_level,id',
        ]);

        foreach ($request->ids as $order => $id) {
            MarketingAccessLevel::where('id', $id)->update(['sort_order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Duplicate an access level
     */
    public function duplicate($id)
    {
        $accessLevel = MarketingAccessLevel::findOrFail($id);

        $newAccessLevel = $accessLevel->replicate();
        $newAccessLevel->name = $accessLevel->name . ' (Copy)';
        $newAccessLevel->save();

        return redirect()->back()->with('success', 'Access level duplicated successfully!');
    }
}
