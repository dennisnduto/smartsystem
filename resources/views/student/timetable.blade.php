@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">My Timetable</h1>
      <p class="text-sm text-gray-600">View your course timetable</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('student.dashboard') }}" class="px-3 py-1.5 bg-gray-600 text-white rounded">Dashboard</a>
      <a href="{{ route('student.rooms') }}" class="px-3 py-1.5 bg-green-600 text-white rounded">Room Availability</a>
      <a href="{{ route('student.timetable.print') }}" class="px-3 py-1.5 bg-gray-700 text-white rounded">Print</a>
    </div>
  </div>

  @if($entries->isEmpty())
    <div class="bg-white rounded-xl shadow p-6 text-center">
      <p class="text-gray-600">No timetable entries found. Please contact your institution administrator.</p>
    </div>
  @else
    <!-- Week Grid -->
    <div class="bg-white rounded-xl shadow p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Weekly Timetable</h2>
        <div class="text-xs text-gray-500">All your classes for the week</div>
      </div>
      @php
        $timeSlots = [1=>'7:00am-10:00am',2=>'10:00am-1:00pm',3=>'1:00pm-4:00pm',4=>'4:00pm-7:00pm'];
        $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
      @endphp
      <div class="overflow-auto">
        <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left p-3 font-semibold text-gray-600 border-b border-r">Time</th>
              @foreach($dayNames as $dn)
                <th class="text-left p-3 font-semibold text-gray-600 border-b border-r">{{ $dn }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($timeSlots as $slotNum => $label)
              <tr class="align-top">
                <td class="p-3 font-medium text-gray-700 border-t border-r bg-gray-50">{{ $label }}</td>
                @foreach($dayNames as $dayNum => $dn)
                  @php $cell = ($entriesByDay[$dayNum] ?? collect())->filter(fn($e) => (int)$e->slot === $slotNum); @endphp
                  <td class="p-3 border-t border-r">
                    @forelse($cell as $e)
                      <div class="border rounded p-2 mb-2 bg-blue-50 border-blue-200">
                        <div class="font-semibold text-blue-900">{{ optional($e->unit)->code }}</div>
                        <div class="text-xs text-gray-700 mt-1">{{ optional($e->unit)->name }}</div>
                        <div class="text-xs text-gray-600 mt-1">Course: {{ optional($e->course)->name ?? '—' }}</div>
                        <div class="text-xs text-green-700 mt-1">
                          <span class="px-2 py-0.5 bg-green-100 rounded-full font-semibold">{{ optional($e->room)->name ?? 'TBA' }}</span>
                        </div>
                        @if($e->lecturer)
                          <div class="text-xs text-gray-600 mt-1">Lecturer: {{ $e->lecturer->name }}</div>
                        @endif
                      </div>
                    @empty
                      <div class="text-xs text-gray-300">—</div>
                    @endforelse
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- List View by Day -->
    <div class="bg-white rounded-xl shadow p-4">
      <h2 class="font-semibold mb-3">Classes by Day</h2>
      @foreach($dayNames as $dayNum => $dayName)
        @php $dayEntries = ($entriesByDay[$dayNum] ?? collect())->sortBy('slot'); @endphp
        @if($dayEntries->isNotEmpty())
          <div class="mb-4 pb-4 border-b last:border-b-0">
            <h3 class="font-semibold text-lg mb-2">{{ $dayName }}</h3>
            @foreach($dayEntries as $entry)
              <div class="p-3 border rounded mb-2 hover:bg-gray-50">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="text-sm text-gray-500">Slot {{ $entry->slot }} ({{ $timeSlots[$entry->slot] ?? 'Unknown' }})</div>
                    <div class="font-semibold">{{ optional($entry->unit)->code }} — {{ optional($entry->unit)->name }}</div>
                    <div class="text-sm text-gray-600 mt-1">
                      Course: {{ optional($entry->course)->name ?? '—' }} • 
                      Room: {{ optional($entry->room)->name ?? 'TBA' }}
                      @if($entry->lecturer)
                        • Lecturer: {{ $entry->lecturer->name }}
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      @endforeach
    </div>
  @endif
</div>
@endsection
