@extends('layouts.minimal')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Minimal Room Test</h1>
  
  <div class="bg-white rounded-xl shadow p-6">
    <h2 class="text-lg font-semibold mb-2">Debug Variables</h2>
    <p>User: {{ $user->name }}</p>
    <p>Available Rooms Count: {{ $availableRooms->count() }}</p>
    <p>Day: {{ $day }}</p>
    <p>Slot: {{ $slot }}</p>
  </div>

  <div class="bg-white rounded-xl shadow mt-4">
    <h2 class="text-lg font-semibold p-4 border-b">Room List Test</h2>
    
    @php
    $roomCount = $availableRooms->count();
    @endphp
    
    @if($roomCount > 0)
      <div class="p-4">
        <p class="text-green-600">✅ Found {{ $roomCount }} rooms</p>
        @foreach($availableRooms as $room)
          <div class="p-2 border-b">
            <strong>{{ $room->name }}</strong> - {{ $room->capacity }} seats
          </div>
        @endforeach
      </div>
    @else
      <div class="p-4">
        <p class="text-red-600">❌ No rooms found</p>
      </div>
    @endif
  </div>
</div>
@endsection
