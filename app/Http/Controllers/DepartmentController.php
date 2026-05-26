<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('department.departmentList', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Department::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Department created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $department = Department::findOrFail($id);

        $department->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Department updated successfully.');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        $department->update([
            'is_active' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Department deleted successfully.');
    }
}
