<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();
        if ($term = $request->get('q')) {
            $query->where(function($q) use ($term) {
                $q->where('code', 'like', "%$term%")
                  ->orWhere('name', 'like', "%$term%");
            });
        }
        $units = $query->orderBy('code')->paginate(15)->withQueryString();
        return view('institution-admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('institution-admin.units.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:units,code',
            'name' => 'required|string|max:255',
'hours_per_week' => 'nullable|integer|min:1|max:12',
            'year_level' => 'nullable|integer|min:1|max:6',
            'semester' => 'nullable|integer|min:1|max:2',
        ]);

        Unit::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
'hours_per_week' => $request->hours_per_week ?? 3,
            'year_level' => $request->year_level,
            'semester' => $request->semester,
        ]);

        return redirect()->route('institution-admin.units.index')->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        return view('institution-admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:units,code,' . $unit->id,
            'name' => 'required|string|max:255',
'hours_per_week' => 'nullable|integer|min:1|max:12',
            'year_level' => 'nullable|integer|min:1|max:6',
            'semester' => 'nullable|integer|min:1|max:2',
        ]);

        $unit->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
'hours_per_week' => $request->hours_per_week ?? 3,
            'year_level' => $request->year_level,
            'semester' => $request->semester,
        ]);

        return redirect()->route('institution-admin.units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        // Optional: prevent deletion if mapped
        $unit->delete();
        return redirect()->route('institution-admin.units.index')->with('success', 'Unit deleted successfully.');
    }
}