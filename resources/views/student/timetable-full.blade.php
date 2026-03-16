@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-12">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="bg-indigo-600 p-2 rounded-lg shadow-indigo-200 shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-2xl text-slate-800 leading-tight premium-font tracking-tight">Institution Wide Timetable</h2>
                        <p class="text-slate-500 text-sm mt-1 flex items-center gap-1 font-medium">Standard Matrix View • Full Academic Schedule</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('student.timetable') }}" class="inline-flex items-center px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition-all shadow-sm font-semibold text-sm">
                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    My Schedule
                </a>
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center px-5 py-2.5 bg-slate-900 text-white rounded-2xl hover:bg-slate-800 transition-all shadow-md font-semibold text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
            </div>
        </div>

        @if(empty($programChunks))
            <div class="bg-white rounded-[2.5rem] p-20 text-center shadow-sm border border-slate-200">
                <div class="text-8xl mb-6 grayscale opacity-20">🗓️</div>
                <h2 class="text-2xl font-bold text-slate-900 premium-font">No Published Entries Found</h2>
                <p class="text-slate-500 mt-2 max-w-md mx-auto font-medium">There are currently no published timetable entries available for your institution. Please contact your coordinator for more information.</p>
            </div>
        @else
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
                    
                    <div class="p-0 overflow-x-auto overflow-y-auto max-h-[750px] relative custom-scrollbar">
                        <table class="min-w-full border-separate border-spacing-0 border border-slate-300">
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
                                                @endphp
                                                <td class="border-r border-b border-slate-300 p-1.5 text-center align-top min-h-[60px]">
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
                    <span class="font-medium">Shared Sessions</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 rounded-md bg-emerald-100 border-2 border-emerald-300"></span>
                    <span class="font-medium">Allocated Venues / Halls</span>
                </div>
                <div class="flex items-center gap-3 ml-auto text-slate-400 italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Programs are grouped vertically for better scannability on all devices.
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');
    .premium-font { font-family: 'Outfit', sans-serif; }

    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
        border: 2px solid #f8fafc;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    table { border-spacing: 0; border-collapse: separate; }
    
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 50;
    }
</style>
@endsection

