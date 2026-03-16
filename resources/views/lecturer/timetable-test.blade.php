<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lecturer Dashboard') }}
        </h2>
    </x-slot>

<div class="max-w-7xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Lecturer Dashboard</h1>
      <p class="text-sm text-gray-600">Welcome back, {{ $user->name }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('lecturer.assigned') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Assigned</a>
      <a href="{{ route('lecturer.rooms') }}" class="px-3 py-1.5 bg-green-600 text-white rounded">Rooms</a>
      <button onclick="window.print()" class="px-3 py-1.5 bg-gray-700 text-white rounded">Print</button>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">📚</div>
      <div class="text-sm text-gray-500">This Week Classes</div>
      <div class="text-xl font-bold">{{ $entries->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🏫</div>
      <div class="text-sm text-gray-500">Rooms Used</div>
      <div class="text-xl font-bold">{{ $entries->pluck('room')->unique('id')->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🧪</div>
      <div class="text-sm text-gray-500">Lab Sessions</div>
      <div class="text-xl font-bold">{{ $labCount ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🎯</div>
      <div class="text-sm text-gray-500">Upcoming Next</div>
      <div class="text-sm font-semibold">
        @if($entries->count() > 0)
            {{ $entries->first()->unit->name ?? 'No unit' }}
        @else
            No classes
        @endif
      </div>
    </div>
  </div>

  <!-- Simple Today List -->
  <div class="bg-white rounded-xl shadow p-4">
    <h2 class="font-semibold mb-3">Today's Classes</h2>
    @if($entries->count() > 0)
      <div class="space-y-2">
        @foreach($entries as $entry)
          <div class="p-3 border rounded">
            <div class="font-semibold">{{ $entry->unit->name ?? 'No unit' }}</div>
            <div class="text-sm text-gray-600">{{ $entry->course->name ?? 'No course' }}</div>
            <div class="text-sm text-gray-600">Room: {{ $entry->room->name ?? 'TBA' }}</div>
            <div class="text-sm text-gray-600">Day {{ $entry->day_of_week }}, Slot {{ $entry->slot }}</div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-gray-500 text-center py-4">
        <div class="text-sm">No classes scheduled.</div>
      </div>
    @endif
  </div>

</div>
</x-app-layout>
