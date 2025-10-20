@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Real-time Room Availability</h1>
  <div class="mb-3 text-sm text-gray-600">Current slot: Day {{ $day }}, Slot {{ $slot }}</div>

  <div class="bg-white rounded shadow divide-y">
    @forelse($availableRooms as $r)
      <div class="p-4 flex items-center justify-between">
        <div>
          <div class="font-semibold">{{ $r->name }}</div>
          <div class="text-sm text-gray-600">Type: {{ $r->room_type ?? '—' }} • Capacity: {{ $r->capacity ?? '—' }}</div>
        </div>
        <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold">Available</span>
      </div>
    @empty
      <div class="p-4 text-gray-600">No available rooms for this slot.</div>
    @endforelse
  </div>
</div>
@endsection


