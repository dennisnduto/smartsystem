@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-bold">{{ $timetable->name }}</h1>
      <div class="text-sm text-gray-600">
        <strong>Institution:</strong> {{ $timetable->institution->name ?? 'N/A' }} • 
        <strong>Department:</strong> {{ $timetable->department->name ?? 'N/A' }} • 
        {{ $timetable->academic_year ?? '—' }} • {{ $timetable->semester ?? 'N/A' }}
      </div>
    </div>
    <div class="space-x-3">
      <a href="{{ route('super-admin.timetables') }}" class="text-indigo-600 hover:underline">← Back</a>
      <a href="{{ route('super-admin.timetables.download', $timetable) }}" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700">
        Download PDF
      </a>
    </div>
  </div>

  @if($timetable->entries->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <p class="text-yellow-800">No timetable entries found for this timetable.</p>
    </div>
  @else
    <x-timetable-grid :timetable="$timetable" />
  @endif
</div>
@endsection
