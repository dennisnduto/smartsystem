@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <!-- Conflict Alerts -->
    @if(isset($conflicts) && ($conflicts['lecturer_conflicts']->isNotEmpty() || $conflicts['room_conflicts']->isNotEmpty()))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center mb-2">
                <span class="text-red-600 text-lg font-semibold">⚠️ Conflicts Detected</span>
            </div>
            @if($conflicts['lecturer_conflicts']->isNotEmpty())
                <div class="mb-2">
                    <strong class="text-red-700">Lecturer Conflicts:</strong> {{ $conflicts['lecturer_conflicts']->count() }} double-bookings
                </div>
            @endif
            @if($conflicts['room_conflicts']->isNotEmpty())
                <div class="mb-2">
                    <strong class="text-red-700">Room Conflicts:</strong> {{ $conflicts['room_conflicts']->count() }} double-bookings
                </div>
            @endif
        </div>
    @endif
    
    <!-- Recommendations -->
    @if(isset($recommendations) && $recommendations->isNotEmpty())
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg print:hidden">
            <div class="flex items-center mb-3">
                <span class="text-blue-600 text-lg font-semibold">💡 Optimization Recommendations</span>
            </div>
            @foreach($recommendations->take(3) as $rec)
                <div class="mb-2 flex items-start gap-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $rec['priority'] === 'high' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ strtoupper($rec['priority']) }}
                    </span>
                    <div>
                        <div class="font-medium text-blue-800">{{ $rec['title'] }}</div>
                        <div class="text-sm text-blue-600">{{ $rec['description'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-timetable-grid :timetable="$timetable" :conflicts="$conflicts ?? []" />
</div>
@endsection
