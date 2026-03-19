<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $institution = Auth::user()->institution;

        $query = Unit::where('institution_id', $institution->id);
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
        $institution = Auth::user()->institution;

        $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'code')->where(fn($q) => $q->where('institution_id', $institution->id)),
            ],
            'name' => 'required|string|max:255',
            'hours_per_week' => 'nullable|integer|min:1|max:12',
            'year_level' => 'nullable|integer|min:1|max:6',
            'semester' => 'nullable|integer|min:1|max:2',
        ]);

        Unit::create([
            'institution_id' => $institution->id,
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
        $this->authorize('view', $unit);
        return view('institution-admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->authorize('update', $unit);
        $institution = Auth::user()->institution;

        $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('units', 'code')
                    ->ignore($unit->id)
                    ->where(fn($q) => $q->where('institution_id', $institution->id)),
            ],
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
        $this->authorize('delete', $unit);
        // Optional: prevent deletion if mapped
        $unit->delete();
        return redirect()->route('institution-admin.units.index')->with('success', 'Unit deleted successfully.');
    }
}