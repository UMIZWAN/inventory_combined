<?php

namespace App\Http\Controllers;

use App\Models\AssetGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetGroupController extends Controller
{
    /**
     * List asset groups
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $assetGroups = AssetGroup::orderBy('name')->where('is_deleted', false)->get();

        return view('assetgroup.list', compact('assetGroups'));
    }

    /**
     * Store new asset group
     */
    public function addAssetGroup(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string',
            'lifespan_from'         => 'nullable|integer|min:0',
            'lifespan_to'           => 'nullable|integer|min:0',
            'service_interval_from' => 'nullable|integer|min:0',
            'service_interval_to'   => 'nullable|integer|min:0',
            'branc_pic'             => 'nullable|string|max:255',
            'remark'                => 'nullable|string',
        ]);

        AssetGroup::create([
            'name'                  => $request->name,
            'description'           => $request->description,
            'lifespan_from'         => $request->lifespan_from,
            'lifespan_to'           => $request->lifespan_to,
            'service_interval_from' => $request->service_interval_from,
            'service_interval_to'   => $request->service_interval_to,
            'branc_pic'             => $request->branc_pic,
            'remark'                => $request->remark,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Asset group created successfully.');
    }

    /**
     * Update asset group
     */
    public function updateAssetGroup(Request $request, $id)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string',
            'lifespan_from'         => 'nullable|integer|min:0',
            'lifespan_to'           => 'nullable|integer|min:0',
            'service_interval_from' => 'nullable|integer|min:0',
            'service_interval_to'   => 'nullable|integer|min:0',
            'branc_pic'             => 'nullable|string|max:255',
            'remark'                => 'nullable|string',
        ]);

        $assetGroup = AssetGroup::findOrFail($id);

        $assetGroup->update([
            'name'                  => $request->name,
            'description'           => $request->description,
            'lifespan_from'         => $request->lifespan_from,
            'lifespan_to'           => $request->lifespan_to,
            'service_interval_from' => $request->service_interval_from,
            'service_interval_to'   => $request->service_interval_to,
            'branc_pic'             => $request->branc_pic,
            'remark'                => $request->remark,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Asset group updated successfully.');
    }

    /**
     * Delete asset group
     */
    public function deleteAssetGroup($id)
    {
        $assetGroup = AssetGroup::findOrFail($id);

        $assetGroup->update([
            'is_deleted' => true,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Asset group deleted successfully.');
    }
}
