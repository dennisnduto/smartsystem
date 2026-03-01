@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Full Timetable</h1>
      <p class="text-sm text-gray-600">All published classes in your institution</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('student.timetable') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">My Course Timetable</a>
      <a href="{{ route('student.dashboard') }}" class="px-3 py-1.5 bg-gray-600 text-white rounded">Dashboard</a>
    </div>
  </div>

  @if($entries->isEmpty())
    <div class="bg-white rounded-xl shadow p-6 text-center">
      <p class="text-gray-600">No published timetable entries found for your institution.</p>
    </div>
  @else
    <div class="bg-white rounded-xl shadow p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Weekly Timetable (All Courses)</h2>
        <div class="text-xs text-gray-500">Published entries only</div>
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
                        <div class="text-xs text-gray-600 mt-1">
                          Course: {{ optional($e->course)->name ?? '—' }}
                          @if($e->lecturer)
                            • Lecturer: {{ $e->lecturer->name }}
                          @endif
                        </div>
                        <div class="text-xs text-green-700 mt-1">
                          <span class="px-2 py-0.5 bg-green-100 rounded-full font-semibold">{{ optional($e->room)->name ?? 'TBA' }}</span>
                        </div>
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
  @endif
</div>
@endsection

