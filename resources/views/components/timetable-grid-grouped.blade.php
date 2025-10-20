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

    // Group entries by course
    $entriesByCourse = $timetable->entries->groupBy('course_id');
    
    // Get course information
    $courses = $entriesByCourse->map(function($entries, $courseId) {
        $firstEntry = $entries->first();
        return [
            'id' => $courseId,
            'name' => $firstEntry->course->name ?? 'Unknown Course',
            'entries' => $entries
        ];
    })->sortBy('name');
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
                    <span class="bg-blue-500 px-2 py-1 rounded ml-2">{{ $courses->count() }} Courses</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('tt-container')?.classList.toggle('compact')" class="bg-white/90 text-blue-700 px-4 py-2 rounded font-medium hover:bg-blue-50">
                        Compact
                    </button>
                    <a href="{{ route('institution-admin.timetables.export-pdf', $timetable) }}" class="bg-white text-red-600 px-4 py-2 rounded font-medium hover:bg-red-50 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export PDF
                    </a>
                    <button onclick="window.print()" class="bg-white text-blue-600 px-4 py-2 rounded font-medium hover:bg-blue-50 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
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
        <!-- Course Navigation -->
        <div class="bg-gray-50 border-b px-6 py-4 print:hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Course Navigation</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($courses as $course)
                    <a href="#course-{{ $course['id'] }}" class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors">
                        {{ $course['name'] }}
                        <span class="ml-2 bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">
                            {{ $course['entries']->count() }} units
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Course Grouped Timetables -->
        <div class="p-6 space-y-8">
            @foreach($courses as $course)
                <div id="course-{{ $course['id'] }}" class="course-section">
                    <!-- Course Header -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg p-6 mb-6">
                        <h2 class="text-2xl font-bold">{{ $course['name'] }}</h2>
                        <p class="text-indigo-100 mt-1">{{ $course['entries']->count() }} units scheduled</p>
                    </div>

                    <!-- Course Timetable -->
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
                                                @php 
                                                    $courseEntries = $course['entries']->filter(function ($entry) use ($dayNum, $slotNum) {
                                                        return (int)$entry->day_of_week === (int)$dayNum && (int)$entry->slot === (int)$slotNum;
                                                    });
                                                @endphp
                                                <td class="px-3 py-3 align-top border-b border-r">
                                                    @if($courseEntries->isNotEmpty())
                                                        <div class="cell-content space-y-2">
                                                            @foreach($courseEntries as $entry)
                                                                <div class="entry-card course-entry">
                                                                    <div class="flex items-center justify-between gap-2">
                                                                        <div class="text-xs font-semibold text-gray-900 truncate">
                                                                            {{ $entry->unit->code ?? 'N/A' }}
                                                                            <div class="text-[10px] text-gray-500 truncate">
                                                                                {{ $entry->unit->name ?? 'N/A' }}
                                                                            </div>
                                                                        </div>
                                                                        @if($entry->room && $entry->room->name)
                                                                            <span class="chip chip-green" title="Room">
                                                                                {{ $entry->room->name }}
                                                                            </span>
                                                                        @else
                                                                            <span class="chip chip-red" title="Room">No Room</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="mt-1 text-[11px] text-gray-600 truncate">
                                                                        {{ $entry->lecturer->name ?? 'N/A' }}
                                                                    </div>
                                                                </div>
                                                            @endforeach
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

                    <!-- Course Summary -->
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="flex items-center">
                                <div class="text-xl mr-3">📚</div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">Units</h4>
                                    <p class="text-lg font-bold text-blue-600">{{ $course['entries']->pluck('unit')->unique('id')->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="flex items-center">
                                <div class="text-xl mr-3">👨‍🏫</div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">Lecturers</h4>
                                    <p class="text-lg font-bold text-green-600">{{ $course['entries']->pluck('lecturer')->unique('id')->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="flex items-center">
                                <div class="text-xl mr-3">🏢</div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">Rooms</h4>
                                    <p class="text-lg font-bold text-purple-600">{{ $course['entries']->pluck('room')->unique('id')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Course-specific Export Buttons -->
                    <div class="mt-4 flex justify-center gap-3">
                        <a href="{{ route('institution-admin.timetables.export-pdf', ['timetable' => $timetable, 'course_id' => $course['id']]) }}" 
                           class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 inline-flex items-center text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export {{ $course['name'] }} PDF
                        </a>
                        <button onclick="printCourseSection('course-{{ $course['id'] }}')" 
                                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 inline-flex items-center text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print {{ $course['name'] }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Overall Summary -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">Overall Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">📚</div>
                    <strong class="text-blue-900">Total Units</strong>
                    <p class="text-blue-700 mt-1">{{ $timetable->entries->pluck('unit')->unique('id')->count() }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">🎓</div>
                    <strong class="text-blue-900">Courses</strong>
                    <p class="text-blue-700 mt-1">{{ $courses->count() }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">👨‍🏫</div>
                    <strong class="text-blue-900">Lecturers</strong>
                    <p class="text-blue-700 mt-1">{{ $timetable->entries->pluck('lecturer')->unique('id')->count() }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">🏢</div>
                    <strong class="text-blue-900">Rooms</strong>
                    <p class="text-blue-700 mt-1">{{ $timetable->entries->pluck('room')->unique('id')->count() }}</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* Dense/compact helpers and chips */
.cell-content { max-height: 10rem; overflow-y: auto; }
.entry-card { padding: 0.5rem; border-radius: 0.375rem; border: 1px solid rgba(0,0,0,0.06); background: #f9fafb; }
.entry-card.course-entry { background: #f0f9ff; border-color: #0ea5e9; }
.chip { display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.125rem 0.375rem; font-size: 10px; font-weight: 600; }
.chip-green { background: #ecfdf5; color: #065f46; }
.chip-red { background: #fef2f2; color: #b91c1c; }

#tt-container.compact .cell-content { max-height: 6rem; }
#tt-container.compact .entry-card { padding: 0.25rem; }
#tt-container.compact td { padding-top: 0.5rem; padding-bottom: 0.5rem; }

/* Smooth scrolling for course navigation */
html { scroll-behavior: smooth; }

/* Course section spacing */
.course-section { scroll-margin-top: 2rem; }

@media print {
    .bg-blue-600 { background-color: #1e40af !important; color: white !important; }
    .bg-gradient-to-r { background: linear-gradient(to right, #6366f1, #8b5cf6) !important; color: white !important; }
    .shadow-lg { box-shadow: none !important; }
    .rounded-lg { border-radius: 0 !important; }
    .bg-green-100 { background-color: #f0fdf4 !important; color: #166534 !important; }
    .bg-red-100 { background-color: #fef2f2 !important; color: #dc2626 !important; }
    .print\:hidden { display: none !important; }
    .cell-content { max-height: none !important; overflow: visible !important; }
    .course-section { page-break-inside: avoid; margin-bottom: 2rem; }
}
</style>

<script>
function printCourseSection(courseId) {
    // Hide all other course sections
    const allSections = document.querySelectorAll('.course-section');
    allSections.forEach(section => {
        if (section.id !== courseId) {
            section.style.display = 'none';
        }
    });
    
    // Hide the overall summary and navigation
    const overallSummary = document.querySelector('.mt-8.bg-gradient-to-r');
    if (overallSummary) overallSummary.style.display = 'none';
    
    const navigation = document.querySelector('.flex.gap-2');
    if (navigation) navigation.style.display = 'none';
    
    // Print
    window.print();
    
    // Restore all sections after printing
    setTimeout(() => {
        allSections.forEach(section => {
            section.style.display = '';
        });
        if (overallSummary) overallSummary.style.display = '';
        if (navigation) navigation.style.display = '';
    }, 1000);
}
</script>
