@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-r from-indigo-700 to-purple-800 shadow-xl mb-6">
    <div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-indigo-500/30 text-white text-xs font-bold px-2 py-0.5 rounded-full border border-white/20 uppercase tracking-wider">
                        Super Admin View
                    </span>
                    <span class="bg-white/10 text-white/80 text-xs px-2 py-0.5 rounded-full border border-white/10">
                        {{ $timetable->institution->name ?? 'N/A' }}
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">
                    {{ $timetable->name }}
                </h1>
                <p class="text-indigo-100 mt-1 flex items-center gap-2">
                    <span class="opacity-75">Academic Focus:</span>
                    <span class="font-semibold">{{ $timetable->academic_year ?? 'All Years' }}</span>
                    <span class="opacity-40">|</span>
                    <span class="font-semibold">{{ $timetable->semester }}</span>
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('super-admin.timetables') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/30 rounded-xl transition-all duration-200 group">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
                
                <a href="{{ route('super-admin.timetables.download', $timetable) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 hover:bg-indigo-50 rounded-xl shadow-lg border border-transparent transition-all duration-200 font-bold group">
                    <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    @if(empty($programChunks))
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center border-2 border-dashed border-gray-200">
            <div class="text-6xl mb-4">📅</div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No Entries Found</h3>
            <p class="text-gray-500 max-w-md mx-auto">This timetable has been approved but doesn't seem to have any scheduled sessions yet.</p>
        </div>
    @else
        <div class="space-y-12">
            @foreach($programChunks as $chunkIndex => $chunkData)
                @php 
                    $currentPrograms = $chunkData['programs'];
                    $currentCourse = $chunkData['course'];
                @endphp
                
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 flex flex-col h-[800px]">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-6 bg-indigo-600 rounded-full"></div>
                            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">
                                {{ $currentCourse }} <span class="text-gray-400 mx-2">•</span> Group {{ $chunkIndex + 1 }}
                            </h3>
                        </div>
                        <div class="text-sm font-medium text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">
                            {{ count($currentPrograms) }} Programs in this view
                        </div>
                    </div>

                    <div class="flex-grow overflow-auto">
                        <table class="w-full border-collapse table-fixed min-w-[1200px]">
                            <thead class="sticky top-0 z-30">
                                <tr>
                                    <th class="w-20 bg-gray-900 text-white p-3 text-xs font-black uppercase tracking-widest border-r border-gray-800">DAY</th>
                                    <th class="w-40 bg-gray-800 text-white p-3 text-xs font-black uppercase tracking-widest border-r border-gray-700">TIME</th>
                                    @foreach($currentPrograms as $key => $program)
                                        <th class="bg-indigo-700 text-white p-4 border-r border-indigo-600 shadow-inner">
                                            <div class="text-sm font-black whitespace-nowrap overflow-hidden text-ellipsis">{{ $program['course'] }}</div>
                                            <div class="text-[10px] text-indigo-200 mt-1 font-bold uppercase tracking-tighter">
                                                Academic Year {{ $program['year'] }}
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($days as $dayNum => $dayName)
                                    @php $isLastDay = $loop->last; @endphp
                                    @foreach($slots as $slotNum => $slotTime)
                                        <tr class="group hover:bg-indigo-50/30 transition-colors">
                                            @if($loop->first)
                                                <td rowspan="{{ count($slots) }}" class="bg-gray-100 border-r-2 border-gray-300 font-black text-center text-sm p-4 rotate-180 [writing-mode:vertical-lr] uppercase tracking-[0.3em] text-gray-600">
                                                    {{ $dayName }}
                                                </td>
                                            @endif
                                            
                                            <td class="bg-gray-50/80 border-r border-b border-gray-200 p-4 text-center z-10">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-xs font-bold text-gray-700 shadow-sm">
                                                    <svg class="w-3 h-3 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $slotTime }}
                                                </span>
                                            </td>

                                            @foreach($currentPrograms as $programKey => $program)
                                                @php $entry = $matrix[$dayNum][$slotNum][$programKey] ?? null; @endphp
                                                <td class="relative border-r border-b border-gray-100 p-3 h-32 vertical-top {{ $entry ? 'z-10 bg-white' : 'bg-gray-50/30' }}">
                                                    @if($entry)
                                                        <div class="h-full flex flex-col p-3 rounded-xl border border-indigo-100 shadow-sm hover:shadow-md transition-all duration-200 group-hover:border-indigo-300 bg-gradient-to-br from-white to-indigo-50/20">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                                    {{ $entry->unit->code ?? 'UNIT' }}
                                                                </span>
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase">
                                                                    {{ $entry->room->name ?? 'TBA' }}
                                                                </span>
                                                            </div>
                                                            
                                                            <div class="text-xs font-black text-gray-900 mb-1 leading-tight line-clamp-2">
                                                                {{ $entry->unit->name ?? 'Course' }}
                                                            </div>
                                                            
                                                            <div class="mt-auto pt-2 flex items-center gap-2 border-t border-indigo-100/50">
                                                                <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] font-bold text-white uppercase shadow-sm">
                                                                    {{ substr($entry->lecturer->name ?? 'L', 0, 1) }}
                                                                </div>
                                                                <span class="text-[10px] font-bold text-gray-600 truncate">
                                                                    {{ $entry->lecturer->name ?? 'TBA' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    @if(!$isLastDay)
                                        <tr class="bg-gray-800 h-1"><td colspan="{{ 2 + count($currentPrograms) }}" class="p-0 border-none"></td></tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    .vertical-top { vertical-align: top; }
    /* Hide scrollbar but keep functionality */
    .overflow-auto::-webkit-scrollbar { height: 10px; width: 10px; }
    .overflow-auto::-webkit-scrollbar-track { background: #f1f5f9; }
    .overflow-auto::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 5px; }
    .overflow-auto::-webkit-scrollbar-thumb:hover { background: #818cf8; }
</style>
@endsection
