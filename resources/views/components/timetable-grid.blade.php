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

    // Get academic year for each entry
    $entriesWithYear = $timetable->entries->map(function($entry) {
        $cuy = \Illuminate\Support\Facades\DB::table('course_unit_year')
            ->where('course_id', $entry->course_id)
            ->where('unit_id', $entry->unit_id)
            ->first();
        $entry->academic_year = $cuy->academic_year ?? null;
        return $entry;
    });

    // Group into rows visually sorted by course and then year
    $programs = [];
    foreach ($entriesWithYear as $entry) {
        $rawName = $entry->course->name ?? 'Unknown Course';
        $cleanName = str_replace(["\xc2\xa0", "\xa0", "&nbsp;"], ' ', $rawName);
        $normalizedName = trim(preg_replace('/\s+/', ' ', $cleanName));
        if (empty($normalizedName)) $normalizedName = 'UNKNOWN COURSE';

        $academicYear = $entry->academic_year ?? 'Unknown';
        $yearLabel = $academicYear !== 'Unknown' ? 'Year ' . substr($academicYear, 1) : 'Unknown Year';

        $key = $normalizedName . '|' . $academicYear;
        
        if (!isset($programs[$key])) {
            $programs[$key] = [
                'course' => $normalizedName,
                'year_label' => $yearLabel,
                'year_val' => $academicYear,
                'entries' => collect()
            ];
        }
        $programs[$key]['entries']->push($entry);
    }

    // Sort the programs by course name, then by year
    uasort($programs, function($a, $b) {
        $courseCmp = strcmp($a['course'], $b['course']);
        if ($courseCmp === 0) {
            return strcmp($a['year_val'], $b['year_val']);
        }
        return $courseCmp;
    });

@endphp

<div id="tt-container" class="bg-slate-50 min-h-screen pb-12 font-sans text-slate-900 transition-all duration-300">
    <!-- Header -->
    <div class="relative bg-white shadow-sm border-b border-slate-200 py-8 px-6 print:py-4 print:px-0 z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">
                    {{ $institution->name ?? 'University Timetable' }}
                </h1>
                <p class="text-lg text-slate-500 mt-1 font-medium">{{ $timetable->name }}</p>
                
                <div class="flex items-center gap-3 mt-4 print:hidden">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                        ID: #{{ $timetable->id }}
                    </span>
                    @php
                        $statusColors = [
                            'draft' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
                            'pending_approval' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                            'approved' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                            'published' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                        ];
                        $statusClass = $statusColors[$timetable->status ?? 'draft'] ?? $statusColors['draft'];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $timetable->status ?? 'draft')) }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/20">
                        {{ count($programs) }} Programs
                    </span>
                </div>
            </div>

            <div class="flex gap-3 print:hidden self-start md:self-auto">
                <button type="button" onclick="document.getElementById('tt-container')?.classList.toggle('compact')" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-all active:scale-95">
                    <svg class="h-4 w-4 mr-2 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3.25 3A2.25 2.25 0 001 5.25v2.5A2.25 2.25 0 003.25 10h13.5A2.25 2.25 0 0019 7.75v-2.5A2.25 2.25 0 0016.75 3H3.25zM3.25 12A2.25 2.25 0 001 14.25v2.5A2.25 2.25 0 003.25 19h13.5A2.25 2.25 0 0019 16.75v-2.5A2.25 2.25 0 0016.75 12H3.25z" clip-rule="evenodd" />
                    </svg>
                    Toggle Compact
                </button>
                <button onclick="window.print()" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-all active:scale-95">
                    <svg class="h-4 w-4 mr-2 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0118 8.653v4.083A2.25 2.25 0 0115.75 15h-.241l.041.087C15.938 15.897 15.541 17 14.542 17H5.458c-.999 0-1.396-1.103-1.008-1.913l.041-.087H4.25A2.25 2.25 0 012 12.736V8.653c0-1.082.775-2.034 1.848-2.201.374-.056.75-.107 1.127-.153V2.75zm1.5-.25a.25.25 0 00-.25.25v3.5h7.5V2.75a.25.25 0 00-.25-.25h-6.5zm-1.636 10H15.14l-.396.837c-.15.318-.465.5-.838.5H6.094c-.373 0-.688-.182-.838-.5l-.392-.837z" clip-rule="evenodd" />
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    @if($timetable->entries->isEmpty())
        <div class="max-w-3xl mx-auto mt-12 text-center py-20 px-6 bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 mb-6 border border-slate-100 shadow-sm">
                <span class="text-4xl text-slate-400">📅</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3 tracking-tight">No Schedule Generated Yet</h2>
            <p class="text-slate-500 max-w-md mx-auto mb-8">This timetable is currently empty. Generate entries to create the schedule matrix automatically based on assignments.</p>
        </div>
    @else
        <!-- Timetable Master Matrix -->
        <div class="max-w-[95vw] mx-auto p-4 lg:p-6 relative">
            <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-sm text-left border-collapse">
                        <!-- Table Head -->
                        <thead class="bg-slate-50/80 backdrop-blur-sm sticky top-0 z-20 shadow-sm">
                            <tr>
                                <th scope="col" class="sticky left-0 bg-slate-50/95 backdrop-blur-md z-30 px-4 py-3 text-[10px] font-semibold text-slate-500 uppercase tracking-wider min-w-[200px] border-b border-r border-slate-200 shadow-[1px_0_0_0_theme(colors.slate.200)]">
                                    Program / Year
                                </th>
                                @foreach($dayNames as $dayNum => $dayName)
                                    <th scope="col" class="px-4 py-3 text-[10px] font-bold text-slate-700 uppercase tracking-wider border-b border-r border-slate-200 min-w-[180px] last:border-r-0 text-center shadow-sm relative sticky-day">
                                        {{ $dayName }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <!-- Table Body -->
                        <tbody class="divide-y divide-slate-100">
                            @php
                                // We use colors logic similar to before to differentiate courses
                                $cIndex = 0;
                            @endphp
                            @foreach($programs as $key => $program)
                                <tr class="group hover:bg-slate-50/30 transition-colors">
                                    <!-- Course & Year header cell sticky left -->
                                    <td class="sticky left-0 bg-white group-hover:bg-slate-50 z-10 px-4 py-3 align-top border-r border-slate-200 shadow-[1px_0_0_0_theme(colors.slate.200)] transition-colors">
                                        <div class="font-bold text-slate-900 leading-snug text-xs">{{ $program['course'] }}</div>
                                        <div class="mt-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                                            {{ $program['year_label'] }}
                                        </div>
                                    </td>

                                    <!-- Mon -> Fri cells -->
                                    @foreach($dayNames as $dayNum => $dayLabel)
                                        <td class="p-2 align-top border-r border-slate-100 last:border-r-0 bg-white/50">
                                            @php
                                                // Get the entries that occur on this exact day for this specific program/year
                                                $dayEntries = $program['entries']->filter(fn($e) => $e->day_of_week == $dayNum);
                                                
                                                // Sort these entries by time slot
                                                $dayEntries = $dayEntries->sortBy('slot');
                                            @endphp

                                            @if($dayEntries->isNotEmpty())
                                                <div class="cell-content space-y-2.5 custom-scrollbar bg-slate-50/30 p-1.5 rounded-lg border border-slate-100/50">
                                                    @foreach($dayEntries as $entry)
                                                        @php
                                                            $timeLabel = $timeSlots[$entry->slot] ?? 'Unknown Time';
                                                        @endphp
                                                        <!-- Compact internal card for course instance block -->
                                                        <div class="entry-card w-full bg-white border border-slate-200 rounded-lg p-2.5 shadow-sm hover:border-indigo-300 hover:shadow transition-all group/item">
                                                            
                                                            <!-- Time label -->
                                                            <div class="mb-1.5">
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-[9px] font-bold text-slate-600 border border-slate-200 uppercase tracking-widest whitespace-nowrap">
                                                                    🕒 {{ str_replace(' - ', ' — ', $timeLabel) }}
                                                                </span>
                                                            </div>

                                                            <!-- Unit code and Room -->
                                                            <div class="flex items-start justify-between gap-2 mb-1">
                                                                <div class="font-bold text-slate-800 text-sm leading-tight tracking-tight">
                                                                    {{ $entry->unit->code ?? 'N/A' }}
                                                                </div>
                                                                
                                                                @if($entry->room && $entry->room->name)
                                                                    <div class="flex items-center shrink-0 px-1.5 py-0.5 rounded bg-emerald-50 border border-emerald-100">
                                                                        <span class="text-[10px] font-bold text-emerald-700 tracking-wide uppercase">{{ $entry->room->name }}</span>
                                                                    </div>
                                                                @else
                                                                    <div class="flex items-center shrink-0 px-1.5 py-0.5 rounded bg-rose-50 border border-rose-100">
                                                                        <span class="text-[10px] font-bold text-rose-700 uppercase">No Rm</span>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- Lecturer -->
                                                            <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-slate-100/80">
                                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                                <span class="text-xs font-medium text-slate-600 truncate" title="{{ $entry->lecturer->name ?? 'No Lecturer' }}">
                                                                    {{ $entry->lecturer->name ?? 'N/A' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="h-full min-h-[50px] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <span class="text-slate-300 text-xs font-medium">—</span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @php $cIndex++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="mt-8">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4 px-2">Timetable Summary</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-5 hover:shadow-md transition-shadow">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-600/10">
                            <span class="text-2xl">🎓</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Programs</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900">{{ count($programs) }}</p>
                        </div>
                    </div>
                
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-5 hover:shadow-md transition-shadow">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-600/10">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Unique Units</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900">{{ $timetable->entries->pluck('unit_id')->filter()->unique()->count() }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-5 hover:shadow-md transition-shadow">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-600/10">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Lecturers</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900">{{ $timetable->entries->pluck('lecturer_id')->filter()->unique()->count() }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex items-center gap-5 hover:shadow-md transition-shadow">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-purple-50 text-purple-600 ring-1 ring-inset ring-purple-600/10">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Rooms</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900">{{ $timetable->entries->pluck('room_id')->filter()->unique()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    @endif
</div>

<style>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background-color: #94a3b8; }

.cell-content { max-height: 24rem; overflow-y: auto; }

/* Compact Mode Styles */
#tt-container.compact .cell-content { max-height: 12rem; overflow-y: auto; }
#tt-container.compact .entry-card { padding: 0.35rem 0.5rem; }
#tt-container.compact td { padding-top: 0.4rem; padding-bottom: 0.4rem; }

@media print {
    body { background: white; }
    .bg-slate-50 { background-color: transparent !important; }
    .shadow-\[0_8px_30px_rgb\(0\,0\,0\,0\.04\)\] { box-shadow: none !important; }
    .ring-1 { box-shadow: none !important; border: 1px solid #e2e8f0; }
    .print\:hidden { display: none !important; }
    .cell-content { max-height: none !important; overflow: visible !important; }
    table { page-break-inside: auto; break-inside: auto; }
    tr { page-break-inside: avoid; break-inside: avoid; }
    .sticky { position: static !important; }
}
</style>
