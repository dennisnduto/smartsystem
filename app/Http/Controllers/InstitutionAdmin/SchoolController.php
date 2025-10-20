<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolController extends Controller
{
    public function index()
    {
        $institution = Auth::user()->institution;
        $schools = $institution->schools()->withCount('departments')->latest()->paginate(10);
        return view('institution-admin.schools.index', compact('schools', 'institution'));
    }

    public function create()
    {
        $institution = Auth::user()->institution;
        return view('institution-admin.schools.create', compact('institution'));
    }

    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        $request->validate([
            'name' => 'required|string|max:255|unique:schools,name,NULL,id,institution_id,' . $institution->id,
            'code' => 'nullable|string|max:20|unique:schools,code,NULL,id,institution_id,' . $institution->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $institution->schools()->create($request->only('name','code','description'));
        return redirect()->route('institution-admin.schools.index')->with('success', 'School created successfully.');
    }

    public function edit(School $school)
    {
        $this->authorize('update', $school);
        return view('institution-admin.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $this->authorize('update', $school);
        $request->validate([
            'name' => 'required|string|max:255|unique:schools,name,' . $school->id . ',id,institution_id,' . $school->institution_id,
            'code' => 'nullable|string|max:20|unique:schools,code,' . $school->id . ',id,institution_id,' . $school->institution_id,
            'description' => 'nullable|string|max:1000',
        ]);
        $school->update($request->only('name','code','description'));
        return redirect()->route('institution-admin.schools.index')->with('success', 'School updated successfully.');
    }

    public function destroy(School $school)
    {
        $this->authorize('delete', $school);
        $school->delete();
        return redirect()->route('institution-admin.schools.index')->with('success', 'School deleted successfully.');
    }
}
