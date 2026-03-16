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

    // Get academic year for each entry by joining with course_unit_year
    $entriesWithYear = $timetable->entries->map(function($entry) {
        $cuy = \Illuminate\Support\Facades\DB::table('course_unit_year')
            ->where('course_id', $entry->course_id)
            ->where('unit_id', $entry->unit_id)
            ->first();
        $entry->academic_year = $cuy->academic_year ?? null;
        return $entry;
    });

    // Organize into course -> year structure using manual grouping for maximum robustness
    $coursesByYear = [];
    foreach ($entriesWithYear as $entry) {
        $rawName = $entry->course->name ?? 'Unknown Course';
        
        // Manual normalization: collapse all whitespace sequences and uppercase
        // handle common NBSP variants that simple trim() misses
        $cleanName = str_replace(["\xc2\xa0", "\xa0", "&nbsp;"], ' ', $rawName);
        $normalizedName = strtoupper(trim(preg_replace('/\s+/', ' ', $cleanName)));
        
        if (empty($normalizedName)) {
            $normalizedName = 'UNKNOWN COURSE';
        }

        if (!isset($coursesByYear[$normalizedName])) {
            $coursesByYear[$normalizedName] = [
                'id' => $entry->course_id,
                'name' => trim($rawName), // Display name from the first match
                'years' => []
            ];
        }
        
        $academicYear = $entry->academic_year ?? 'Unknown';
        if (!isset($coursesByYear[$normalizedName]['years'][$academicYear])) {
            $yearLabel = $academicYear ? 'Year ' . substr($academicYear, 1) : 'Unknown Year';
            $coursesByYear[$normalizedName]['years'][$academicYear] = [
                'year' => $academicYear,
                'year_label' => $yearLabel,
                'entries' => collect([])
            ];
        }

        $coursesByYear[$normalizedName]['years'][$academicYear]['entries']->push($entry);
    }

    // Sort courses by name, and years within each course
    uksort($coursesByYear, function($a, $b) use ($coursesByYear) {
        return strcmp($coursesByYear[$a]['name'], $coursesByYear[$b]['name']);
    });
    
    foreach ($coursesByYear as &$course) {
        ksort($course['years']);
    }
    unset($course);
@endphp

<div id="tt-container" class="bg-white min-h-screen">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-6 px-6 print:px-0 shadow-lg">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold">{{ $institution->name ?? 'University Timetable' }}</h1>
            <p class="text-indigo-100 mt-1 text-lg">{{ $timetable->name }}</p>
            <div class="flex justify-between items-center mt-4 print:hidden">
                <div class="text-sm flex gap-2">
                    <span class="bg-indigo-500 px-3 py-1 rounded-full font-medium">ID: #{{ $timetable->id }}</span>
                    <span class="bg-indigo-500 px-3 py-1 rounded-full font-medium">{{ ucfirst($timetable->status ?? 'draft') }}</span>
                    <span class="bg-indigo-500 px-3 py-1 rounded-full font-medium">{{ count($coursesByYear) }} Programs</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('tt-container')?.classList.toggle('compact')" class="bg-white/90 text-indigo-700 px-4 py-2 rounded-lg font-medium hover:bg-white transition-colors">
                        Compact
                    </button>
                    <a href="{{ route('institution-admin.timetables.export-pdf', $timetable) }}" class="bg-white text-red-600 px-4 py-2 rounded-lg font-medium hover:bg-red-50 inline-flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export PDF
                    </a>
                    <button onclick="window.print()" class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium hover:bg-indigo-50 inline-flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    <a href="{{ route('dashboard') }}" class="bg-indigo-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-400 transition-colors">
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
        <div class="bg-white/80 backdrop-blur-md border-b px-6 py-4 print:hidden sticky top-0 z-30 shadow-sm border border-indigo-100/50">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">Quick Navigation</h3>
                    <p class="text-sm text-gray-500">Jump to a specific program and year</p>
                </div>
                <div class="flex flex-wrap gap-3 items-center">
                    @foreach($coursesByYear as $course)
                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg overflow-hidden transition-all hover:border-indigo-300 hover:shadow-sm">
                            <span class="px-3 py-1.5 text-sm font-bold text-gray-800 bg-white border-r truncate max-w-[150px]" title="{{ $course['name'] }}">
                                {{ $course['name'] }}
                            </span>
                            <div class="flex p-1 gap-1">
                                @foreach($course['years'] as $yearData)
                                    <a href="#course-{{ $course['id'] }}-year-{{ $yearData['year'] }}" 
                                       class="px-2 py-1 text-[10px] font-bold rounded uppercase transition-all bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white"
                                       title="Jump to {{ $yearData['year_label'] }}">
                                        {{ substr($yearData['year_label'], 0, 2) }}{{ substr($yearData['year_label'], -1) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Course & Year Grouped Timetables -->
        <div class="p-6 space-y-12">
            @foreach($coursesByYear as $course)
                <div class="course-section">
                    <!-- Course Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-2xl p-8 mb-8 shadow-xl relative overflow-hidden group">
                        <!-- Decorative circle -->
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-all group-hover:scale-110"></div>
                        
                        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-3xl font-extrabold tracking-tight">{{ $course['name'] }}</h2>
                                <div class="flex items-center gap-4 mt-3 text-indigo-100 font-medium">
                                    <span class="flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                        {{ count($course['years']) }} Academic Year(s)
                                    </span>
                                    <span class="flex items-center gap-1.5 bg-white/20 px-3 py-1 rounded-full text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                        {{ collect($course['years'])->sum(fn($y) => $y['entries']->count()) }} Total Sessions
                                    </span>
                                </div>
                            </div>
                            <div class="hidden md:block">
                                <div class="text-6xl opacity-20">🎓</div>
                            </div>
                        </div>
                    </div>

                    <!-- Year Sections -->
                    @foreach($course['years'] as $yearData)
                        <div id="course-{{ $course['id'] }}-year-{{ $yearData['year'] }}" class="year-section mb-8">
                            <!-- Year Header -->
                            <div class="flex items-center gap-3 mb-4 mt-12">
                                <div class="h-8 w-1.5 bg-indigo-500 rounded-full"></div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $yearData['year_label'] }}</h3>
                                <span class="bg-indigo-50 text-indigo-700 px-3 py-0.5 rounded-full text-xs font-bold border border-indigo-100">
                                    {{ $yearData['entries']->count() }} Sessions
                                </span>
                            </div>

                            <!-- Year Timetable -->
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full table-fixed border-separate border-spacing-0">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="sticky left-0 bg-gray-50 z-10 px-4 py-4 text-left text-sm font-bold text-gray-800 w-44 border-b-2 border-gray-300">Time</th>
                                                @foreach($dayNames as $dayNum => $dayName)
                                                    <th class="px-4 py-4 text-center text-sm font-bold text-gray-800 border-b-2 border-gray-300 bg-gray-50">{{ $dayName }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($timeSlots as $slotNum => $timeLabel)
                                                <tr class="align-top hover:bg-gray-50 transition-colors">
                                                    <!-- Time column -->
                                                    <td class="sticky left-0 bg-white z-10 px-4 py-4 text-sm font-semibold text-gray-700 border-b border-r border-gray-200 w-44">{{ $timeLabel }}</td>
                                                    <!-- Day cells -->
                                                    @foreach($dayNames as $dayNum => $dayLabel)
                                                        @php 
                                                            $yearEntries = $yearData['entries']->filter(function ($entry) use ($dayNum, $slotNum) {
                                                                return (int)$entry->day_of_week === (int)$dayNum && (int)$entry->slot === (int)$slotNum;
                                                            });
                                                        @endphp
                                                        <td class="px-3 py-3 align-top border-b border-r border-gray-200 min-h-[80px]">
                                                            @if($yearEntries->isNotEmpty())
                                                                <div class="cell-content space-y-2">
                                                                    @foreach($yearEntries as $entry)
                                                                        <div class="entry-card year-entry bg-blue-50 border-l-4 border-blue-500 hover:bg-blue-100 transition-colors">
                                                                            <div class="flex items-start justify-between gap-2">
                                                                                <div class="flex-1 min-w-0">
                                                                                    <div class="text-sm font-bold text-gray-900 truncate">
                                                                                        {{ $entry->unit->code ?? 'N/A' }}
                                                                                    </div>
                                                                                    <div class="text-xs text-gray-600 truncate mt-0.5">
                                                                                        {{ Str::limit($entry->unit->name ?? 'N/A', 30) }}
                                                                                    </div>
                                                                                    <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                                                        <span>👨‍🏫</span>
                                                                                        <span class="truncate">{{ $entry->lecturer->name ?? 'N/A' }}</span>
                                                                                    </div>
                                                                                </div>
                                                                                @if($entry->room && $entry->room->name)
                                                                                    <span class="chip chip-green flex-shrink-0" title="Room">
                                                                                        🏢 {{ $entry->room->name }}
                                                                                    </span>
                                                                                @else
                                                                                    <span class="chip chip-red flex-shrink-0" title="Room">No Room</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="text-center text-sm text-gray-300 py-2">—</div>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Year Summary -->
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-4 border border-blue-200">
                                    <div class="flex items-center">
                                        <div class="text-2xl mr-3">📚</div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700">Units</h4>
                                            <p class="text-xl font-bold text-blue-600">{{ $yearData['entries']->pluck('unit')->unique('id')->count() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow p-4 border border-green-200">
                                    <div class="flex items-center">
                                        <div class="text-2xl mr-3">👨‍🏫</div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700">Lecturers</h4>
                                            <p class="text-xl font-bold text-green-600">{{ $yearData['entries']->pluck('lecturer')->unique('id')->count() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow p-4 border border-purple-200">
                                    <div class="flex items-center">
                                        <div class="text-2xl mr-3">🏢</div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700">Rooms</h4>
                                            <p class="text-xl font-bold text-purple-600">{{ $yearData['entries']->pluck('room')->unique('id')->count() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- Overall Summary -->
        <div class="mt-12 bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 rounded-xl p-8 mx-6 mb-6 border border-indigo-200">
            <h3 class="text-2xl font-bold text-indigo-900 mb-6 text-center">Overall Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-md text-center border border-indigo-100">
                    <div class="text-4xl mb-3">📚</div>
                    <strong class="text-indigo-900 block text-lg">Total Units</strong>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $timetable->entries->pluck('unit')->unique('id')->count() }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md text-center border border-indigo-100">
                    <div class="text-4xl mb-3">🎓</div>
                    <strong class="text-indigo-900 block text-lg">Programs</strong>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ count($coursesByYear) }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md text-center border border-indigo-100">
                    <div class="text-4xl mb-3">👨‍🏫</div>
                    <strong class="text-indigo-900 block text-lg">Lecturers</strong>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $timetable->entries->pluck('lecturer')->unique('id')->count() }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md text-center border border-indigo-100">
                    <div class="text-4xl mb-3">🏢</div>
                    <strong class="text-indigo-900 block text-lg">Rooms</strong>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $timetable->entries->pluck('room')->unique('id')->count() }}</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* Enhanced styling */
.cell-content { max-height: 12rem; overflow-y: auto; }
.entry-card { padding: 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(0,0,0,0.08); }
.entry-card.year-entry { 
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 4px solid #3b82f6;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.chip { 
    display: inline-flex; 
    align-items: center; 
    border-radius: 9999px; 
    padding: 0.25rem 0.5rem; 
    font-size: 11px; 
    font-weight: 600; 
    white-space: nowrap;
}
.chip-green { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.chip-red { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

#tt-container.compact .cell-content { max-height: 8rem; }
#tt-container.compact .entry-card { padding: 0.5rem; }
#tt-container.compact td { padding-top: 0.5rem; padding-bottom: 0.5rem; }

/* Smooth scrolling */
html { scroll-behavior: smooth; }
.course-section { scroll-margin-top: 2rem; }
.year-section { scroll-margin-top: 1rem; }

/* Print styles */
@media print {
    .bg-gradient-to-r { background: linear-gradient(to right, #4f46e5, #7c3aed) !important; color: white !important; }
    .shadow-lg, .shadow-md, .shadow { box-shadow: none !important; }
    .rounded-xl, .rounded-lg { border-radius: 0.25rem !important; }
    .print\:hidden { display: none !important; }
    .cell-content { max-height: none !important; overflow: visible !important; }
    .course-section, .year-section { page-break-inside: avoid; margin-bottom: 1.5rem; }
    .sticky { position: static !important; }
}
</style>

<script>
function printCourseSection(courseId, year) {
    const sectionId = `course-${courseId}-year-${year}`;
    const allSections = document.querySelectorAll('.year-section');
    allSections.forEach(section => {
        if (section.id !== sectionId) {
            section.style.display = 'none';
        }
    });
    
    window.print();
    
    setTimeout(() => {
        allSections.forEach(section => {
            section.style.display = '';
        });
    }, 1000);
}
</script>
