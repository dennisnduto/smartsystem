@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-bold">Real-time Room Availability</h1>
      <p class="text-sm text-gray-600 mt-1">Check which rooms are currently available</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('student.dashboard') }}" class="px-3 py-1.5 bg-gray-600 text-white rounded">Dashboard</a>
      <a href="{{ route('student.timetable') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Timetable</a>
    </div>
  </div>

  @php
    $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
    $timeSlots = [1=>'7:00am-10:00am',2=>'10:00am-1:00pm',3=>'1:00pm-4:00pm',4=>'4:00pm-7:00pm'];
  @endphp

  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
    <div class="text-sm font-semibold text-blue-900">Current Time Slot</div>
    <div class="text-sm text-blue-700 mt-1">
      {{ $dayNames[$day] ?? 'Day ' . $day }}, Slot {{ $slot }} ({{ $timeSlots[$slot] ?? 'Unknown' }})
    </div>
    <div class="text-xs text-blue-600 mt-1">Last updated: {{ $now->format('H:i:s') }}</div>
  </div>

  <div class="bg-white rounded-xl shadow divide-y">
    @forelse($availableRooms as $room)
      <div class="p-4 flex items-center justify-between hover:bg-gray-50">
        <div class="flex-1">
          <div class="font-semibold text-lg">{{ $room->name }}</div>
          <div class="text-sm text-gray-600 mt-1">
            Type: {{ ucfirst($room->room_type ?? 'Normal') }} • 
            Capacity: {{ $room->capacity ?? 'Not specified' }}
            @if($room->facilities && is_array($room->facilities) && count($room->facilities) > 0)
              • Facilities: {{ implode(', ', $room->facilities) }}
            @endif
          </div>
          @if($room->department)
            <div class="text-xs text-gray-500 mt-1">Department: {{ $room->department->name }}</div>
          @endif
        </div>
        <span class="px-3 py-1.5 rounded-full bg-green-100 text-green-700 text-sm font-semibold">Available</span>
      </div>
    @empty
      <div class="p-6 text-center text-gray-600">
        <div class="text-4xl mb-2">🏫</div>
        <div>No rooms are currently available for this time slot.</div>
        <div class="text-sm text-gray-500 mt-2">All rooms may be in use or reserved.</div>
      </div>
    @endforelse
  </div>

  <div class="mt-4 text-sm text-gray-500 text-center">
    Room availability is updated in real-time based on published timetables.
  </div>
</div>
@endsection
