<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-100">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight flex items-center gap-3">
                    <div class="bg-indigo-600 p-2 rounded-lg shadow-indigo-200 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    Institution Wide Timetable
                </h2>
                <p class="text-slate-500 text-sm mt-1 flex items-center gap-1">
                    Comparing programs in groups for maximum clarity
                </p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('lecturer.timetable.full.export', ['format' => 'pdf']) }}" 
                   class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-lg shadow-indigo-200">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                   </svg>
                   Download PDF
                </a>
                <a href="{{ route('lecturer.timetable.full.export', ['format' => 'csv']) }}" 
                   class="bg-slate-50 text-slate-700 px-5 py-2.5 rounded-xl font-semibold hover:bg-slate-100 transition-all flex items-center gap-2 border border-slate-200">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                   </svg>
                   Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-12">
            @foreach($programChunks as $chunkIndex => $chunkData)
                @php
                    $programs = $chunkData['programs'] ?? $chunkData;
                    $currentCourse = $chunkData['course'] ?? 'General';
                @endphp
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-200">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                        <h3 class="font-black text-slate-700 uppercase tracking-widest text-sm flex items-center gap-2">
                            <span class="bg-indigo-600 text-white px-2 py-1 rounded-md flex items-center justify-center text-[10px]">{{ strtoupper($currentCourse) }}</span>
                            Programs Group {{ $chunkIndex + 1 }}
                        </h3>
                        <span class="text-xs text-slate-400 font-medium">Showing {{ count($programs) }} programs side-by-side</span>
                    </div>
                    
                    <div class="p-0 overflow-x-auto overflow-y-auto max-h-[750px] relative">
                    <table class="min-w-full border-separate border-spacing-0 border border-slate-400">
                        <thead>
                            <tr>
                                <!-- Sticky Top-Left Corner -->
                                <th class="sticky top-0 left-0 z-50 bg-slate-200 border-r border-b border-slate-400 p-2 text-[10px] font-black text-slate-700 uppercase tracking-widest text-center min-w-[60px]">
                                    DAY
                                </th>
                                <th class="sticky top-0 left-[60px] z-50 bg-slate-200 border-r border-b border-slate-400 p-2 text-[10px] font-black text-slate-700 uppercase tracking-widest text-center min-w-[90px]">
                                    TIME
                                </th>
                                    
                                    @foreach($programs as $key => $program)
                                    <th class="bg-indigo-600 text-white border-r border-b border-indigo-500 p-2 text-[10px] font-bold uppercase tracking-wider text-center min-w-[130px]">
                                            <div class="truncate">{{ $program['course'] }}</div>
                                            <div class="text-[9px] text-indigo-200 font-medium mt-0.5">
                                                ({{ str_ireplace('Year ', 'Y', $program['year']) }})
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($days as $dayNum => $dayName)
                                    @if(!$loop->first)
                                        <!-- Bold Day Separator -->
                                        <tr class="bg-slate-700 h-1.5 shadow-inner">
                                            <td colspan="{{ 2 + count($programs) }}"></td>
                                        </tr>
                                    @endif
                                    @foreach($slots as $slotNum => $slotTime)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            @if($loop->first)
                                                <td rowspan="{{ count($slots) }}" class="sticky left-0 z-40 bg-white border-r border-b border-slate-400 p-2 text-center align-middle shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                                                    <div class="[writing-mode:vertical-lr] [transform:rotate(180deg)] text-indigo-600 font-black text-sm uppercase tracking-tighter">
                                                        {{ $dayName }}
                                                    </div>
                                                </td>
                                            @endif
                                            
                                            <td class="sticky left-[60px] z-30 bg-slate-50 border-r-2 border-b border-slate-400 p-1.5 text-center align-middle shadow-[1px_0_3px_rgba(0,0,0,0.01)]">
                                                <div class="text-[10px] font-bold text-slate-600 whitespace-nowrap">
                                                    {{ $slotTime }}
                                                </div>
                                            </td>
                                            
                                            @foreach($programs as $key => $program)
                                                @php
                                                    $entry = $matrix[$dayNum][$slotNum][$key] ?? null;
                                                    $isMyClass = $entry && $entry->lecturer_id === $user->lecturer_id;
                                                @endphp
                                                <td class="border-r border-b border-slate-300 p-1.5 text-center align-top min-h-[60px] {{ $isMyClass ? 'bg-indigo-50/70 border-2 border-indigo-300 z-10 rounded shadow-inner' : '' }}">
                                                    @if($entry)
                                                        <div class="flex flex-col gap-0.5 h-full justify-center">
                                                            <div class="font-black text-slate-900 text-[10px] leading-tight mb-0.5">
                                                                {{ $entry->unit->code ?? '—' }}
                                                            </div>
                                                            <div class="text-[9px] text-slate-500 font-medium leading-[1.1]">
                                                            {{ $entry->lecturer->name ?? '—' }}
                                                        </div>
                                                            <div class="mt-1">
                                                                <span class="bg-emerald-50 text-emerald-700 text-[8px] font-black px-1.5 py-0.2 rounded border border-emerald-100 uppercase tracking-tighter">
                                                                    {{ $entry->room->name ?? 'TBA' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center justify-center p-2">
                                                            <div class="w-1 h-1 rounded-full bg-slate-50"></div>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
            
            <!-- Matrix Legend -->
            <div class="flex flex-wrap gap-8 text-sm text-slate-500 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 rounded-md bg-indigo-100 border-2 border-indigo-300"></span>
                    <span class="font-medium">My Assigned Sessions</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 rounded-md bg-emerald-100 border-2 border-emerald-300"></span>
                    <span class="font-medium">Allocated Venues / Halls</span>
                </div>
                <div class="flex items-center gap-3 ml-auto text-slate-400 italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Programs are grouped vertically into pages for better scannability.
                </div>
            </div>
        </div>
    </div>

    <style>
        .overflow-x-auto::-webkit-scrollbar {
            height: 10px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
            border: 2px solid #f8fafc;
        }
        
        table { border-spacing: 0; border-collapse: separate; }
        
        .sticky.left-\[100px\]::after {
            content: '';
            position: absolute;
            top: 0;
            right: -8px;
            bottom: 0;
            width: 8px;
            pointer-events: none;
            background: linear-gradient(to right, rgba(0,0,0,0.03), transparent);
        }
    </style>
</x-app-layout>
