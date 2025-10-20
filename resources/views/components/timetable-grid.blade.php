@props(['timetable'])

@php
    // Load timetable entries with their relationships
    $timetable->load(['entries.unit', 'entries.course', 'entries.room', 'entries.lecturer']);

    $timeSlots = [
        1 => '7:00 AM - 10:00 AM',
        2 => '10:00 AM - 1:00 PM',
        3 => '1:00 PM - 4:00 PM',
        4 => '4:00 PM - 7:00 PM'
    ];

    $dayNames = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday'
    ];

    // Get institution name
    $institution = $timetable->institution ?? $timetable->department->institution ?? null;

    // Prepare a day/slot indexed grid for O(1) lookup when rendering matrix
    $timetableGrid = [];
    foreach ($dayNames as $dayNum => $dayName) {
        foreach ($timeSlots as $slotNum => $timeSlot) {
            $entries = $timetable->entries->filter(function ($entry) use ($dayNum, $slotNum) {
                return (int)$entry->day_of_week === (int)$dayNum && (int)$entry->slot === (int)$slotNum;
            });
            $timetableGrid[$dayNum][$slotNum] = [
                'day' => $dayName,
                'time' => $timeSlot,
                'entries' => $entries,
            ];
        }
    }
@endphp

<div id="tt-container" class="bg-white min-h-screen">
    <!-- Header -->
    <div class="bg-blue-600 text-white py-6 px-6 print:px-0">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold">{{ $institution->name ?? 'University Timetable' }}</h1>
            <p class="text-blue-100 mt-1">{{ $timetable->name }}</p>
            <div class="flex justify-between items-center mt-4 print:hidden">
                <div class="text-sm">
                    <span class="bg-blue-500 px-2 py-1 rounded">ID: #{{ $timetable->id }}</span>
                    <span class="bg-blue-500 px-2 py-1 rounded ml-2">{{ ucfirst($timetable->status ?? 'draft') }}</span>
                </div>
<div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('tt-container')?.classList.toggle('compact')" class="bg-white/90 text-blue-700 px-4 py-2 rounded font-medium hover:bg-blue-50">
                        Compact
                    </button>
                    <button onclick="window.print()" class="bg-white text-blue-600 px-4 py-2 rounded font-medium hover:bg-blue-50">
                        Print
                    </button>
                    <a href="{{ route('dashboard') }}" class="bg-blue-500 text-white px-4 py-2 rounded font-medium hover:bg-blue-400">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($timetable->entries->isEmpty())
        <div class="text-center py-16">
            <div class="text-6xl mb-4">📅</div>
            <h2 class="text-2xl font-bold text-gray-600 mb-2">No Schedule Yet</h2>
            <p class="text-gray-500">Generate entries to create the timetable.</p>
        </div>
    @else
        <!-- Timetable Matrix (Times as rows, Days as columns) -->
        <div class="p-6">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-fixed border-separate border-spacing-0">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="sticky left-0 bg-gray-50 z-10 px-4 py-3 text-left text-xs font-semibold text-gray-700 w-40 border-b">Time</th>
                                @foreach($dayNames as $dayNum => $dayName)
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border-b">{{ $dayName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slotNum => $timeLabel)
                                <tr class="align-top">
                                    <!-- Time column -->
                                    <td class="sticky left-0 bg-white z-10 px-4 py-4 text-sm font-medium text-gray-900 border-b border-r w-40">{{ $timeLabel }}</td>
                                    <!-- Day cells -->
                                    @foreach($dayNames as $dayNum => $dayLabel)
                                        @php $cell = $timetableGrid[$dayNum][$slotNum] ?? ['entries' => collect()]; @endphp
<td class="px-3 py-3 align-top border-b border-r">
                                            @php
                                                $entries = $cell['entries'];
                                                $visibleEntries = $entries->take(2);
                                                $hidden = $entries->slice(2);
                                                $hiddenTitle = '';
                                                if ($hidden->count() > 0) {
                                                    $hiddenTitle = implode("\n", $hidden->map(function($e){
                                                        $unitCode = $e->unit->code ?? 'N/A';
                                                        $courseName = $e->course ? ' (' . $e->course->name . ')' : '';
                                                        return $unitCode . $courseName . ' @ ' . ($e->room->name ?? 'No Room');
                                                    })->all());
                                                }
                                            @endphp
                                            @if($entries->isNotEmpty())
                                                <div class="cell-content space-y-2">
                                                    @foreach($visibleEntries as $entry)
                                                        <div class="entry-card bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-300 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow">
                                                            <div class="flex items-center justify-between gap-2 mb-2">
                                                                <div class="text-sm font-bold text-blue-900 truncate">
                                                                    {{ $entry->unit->code ?? 'N/A' }}
                                                                    @if($entry->course)
                                                                        <div class="text-xs text-blue-700 truncate font-medium">
                                                                            {{ $entry->course->name }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                @if($entry->room && $entry->room->name)
                                                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded font-semibold">
                                                                        🏢 {{ $entry->room->name }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded font-semibold">No Room</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-xs text-gray-600 truncate">
                                                                👨‍🏫 {{ $entry->lecturer->name ?? 'N/A' }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @if($hidden->count() > 0)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[10px]" title="{{ $hiddenTitle }}">+{{ $hidden->count() }} more</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-center text-sm text-gray-300">—</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Units -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">📚</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Units</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ $timetable->entries->pluck('unit')->unique('id')->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Lecturers -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">👨‍🏫</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Lecturers</h3>
                            <p class="text-2xl font-bold text-green-600">{{ $timetable->entries->pluck('lecturer')->unique('id')->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rooms -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="text-2xl mr-3">🏢</div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Rooms</h3>
                            <p class="text-2xl font-bold text-purple-600">{{ $timetable->entries->pluck('room')->unique('id')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* Dense/compact helpers and chips */
.cell-content { max-height: 10rem; overflow-y: auto; }
.entry-card { padding: 0.5rem; border-radius: 0.375rem; border: 1px solid rgba(0,0,0,0.06); background: #f9fafb; }
.chip { display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.125rem 0.375rem; font-size: 10px; font-weight: 600; }
.chip-green { background: #ecfdf5; color: #065f46; }
.chip-red { background: #fef2f2; color: #b91c1c; }

#tt-container.compact .cell-content { max-height: 6rem; }
#tt-container.compact .entry-card { padding: 0.25rem; }
#tt-container.compact td { padding-top: 0.5rem; padding-bottom: 0.5rem; }

@media print {
    .bg-blue-600 { background-color: #1e40af !important; color: white !important; }
    .shadow-lg { box-shadow: none !important; }
    .rounded-lg { border-radius: 0 !important; }
    .bg-green-100 { background-color: #f0fdf4 !important; color: #166534 !important; }
    .bg-red-100 { background-color: #fef2f2 !important; color: #dc2626 !important; }
    .print\:hidden { display: none !important; }
    .cell-content { max-height: none !important; overflow: visible !important; }
}
</style>
