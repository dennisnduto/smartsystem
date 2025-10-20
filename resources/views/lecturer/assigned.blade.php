@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6 space-y-6">
  <div>
    <h1 class="text-2xl font-bold mb-2">My Assigned Classes</h1>
    <div class="bg-white rounded shadow divide-y">
      @forelse($classes as $c)
        <div class="p-4">
          <div class="font-semibold">{{ $c->unit_code }} — {{ $c->unit_name }}</div>
          <div class="text-sm text-gray-600">Course: {{ $c->course_name }} • Year: {{ $c->academic_year }} • Semester: {{ $c->semester }}</div>
        </div>
      @empty
        <div class="p-4 text-gray-600">No assignments yet.</div>
      @endforelse
    </div>
  </div>

  <div>
    <h2 class="text-xl font-semibold mb-2">Rooms (All)</h2>
    <div class="bg-white rounded shadow divide-y">
      @forelse($rooms as $r)
        <div class="p-4 flex items-center justify-between">
          <div>
            <div class="font-semibold">{{ $r->name }}</div>
            <div class="text-sm text-gray-600">Type: {{ $r->room_type ?? '—' }} • Capacity: {{ $r->capacity ?? '—' }}</div>
          </div>
        </div>
      @empty
        <div class="p-4 text-gray-600">No rooms found.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection


