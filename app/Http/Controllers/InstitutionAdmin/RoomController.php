<?php

namespace App\Http\Controllers\InstitutionAdmin;

use App\Http\Controllers\Controller;
use App\Models\{Room, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institution = Auth::user()->institution;
        $rooms = Room::whereHas('department', function($q) use ($institution) {
            $q->where('institution_id', $institution->id);
        })->with('department')->latest()->paginate(10);
        
        return view('institution-admin.rooms.index', compact('rooms', 'institution'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $institution = Auth::user()->institution;
        // No department selection needed on create; will default to a general department
        return view('institution-admin.rooms.create', compact('institution'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $institution = Auth::user()->institution;
        
        // Default to a general department within this institution
        $department = Department::firstOrCreate(
            ['institution_id' => $institution->id, 'name' => 'General'],
            ['description' => 'Default department for unassigned rooms']
        );

        $request->validate([
            'name' => [
                'required','string','max:255',
                Rule::unique('rooms','name')->where(fn($q) => $q->where('department_id', $department->id)),
            ],
            'capacity' => 'required|integer|min:1|max:1000',
            'room_type' => 'required|in:normal,hall,lab',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string|max:100'
        ]);

        $room = Room::create([
            'department_id' => $department->id,
            'name' => $request->name,
            'capacity' => $request->capacity,
            'room_type' => $request->room_type,
            'facilities' => $request->facilities ? array_filter($request->facilities) : null
        ]);

        return redirect()->route('institution-admin.rooms.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $this->authorize('view', $room);
        
        $room->load('department');
        
        return view('institution-admin.rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $this->authorize('update', $room);
        
        $institution = Auth::user()->institution;
        $departments = $institution->departments;
        
        return view('institution-admin.rooms.edit', compact('room', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);
        
        $institution = Auth::user()->institution;
        
        $request->validate([
            'name' => [
                'required','string','max:255',
                Rule::unique('rooms','name')->ignore($room->id)->where(fn($q) => $q->where('department_id', $room->department_id)),
            ],
            'capacity' => 'required|integer|min:1|max:1000',
            'room_type' => 'required|in:normal,hall,lab',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string|max:100'
        ]);

        $room->update([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'room_type' => $request->room_type,
            'facilities' => $request->facilities ? array_filter($request->facilities) : null
        ]);

        return redirect()->route('institution-admin.rooms.index')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);
        
        $room->delete();

        return redirect()->route('institution-admin.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
