<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institution = Auth::user()->institution;
        $departments = $institution->departments()->withCount(['courses', 'rooms'])->latest()->paginate(10);
        
        return view('institution-admin.departments.index', compact('departments', 'institution'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $institution = Auth::user()->institution;
        if ($institution->schools()->count() === 0) {
            return redirect()->route('institution-admin.schools.create')
                ->with('error', 'Please create a School first before adding departments.');
        }
        $schools = $institution->schools;
        return view('institution-admin.departments.create', compact('institution','schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,NULL,id,institution_id,' . $institution->id,
'school_id' => 'required|exists:schools,id',
            'description' => 'nullable|string|max:1000'
        ]);

        $department = $institution->departments()->create([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'description' => $request->description
        ]);

        return redirect()->route('institution-admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $this->authorize('view', $department);
        
        $department->load(['courses', 'rooms', 'timetables']);
        
        return view('institution-admin.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $this->authorize('update', $department);
        
        $institution = Auth::user()->institution;
        $schools = $institution->schools;
        return view('institution-admin.departments.edit', compact('department','schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $this->authorize('update', $department);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id . ',id,institution_id,' . $department->institution_id,
            'school_id' => 'nullable|exists:schools,id',
            'description' => 'nullable|string|max:1000'
        ]);

        $department->update([
            'name' => $request->name,
            'school_id' => $request->school_id,
            'description' => $request->description
        ]);

        return redirect()->route('institution-admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);
        
        $department->delete();

        return redirect()->route('institution-admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
