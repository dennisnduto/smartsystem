@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 premium-font">My Timetable</h1>
                <p class="text-gray-500 mt-2 font-medium">Your personalized weekly learning schedule</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <div class="text-right mr-4 hidden md:block">
                    <div id="live-time" class="text-xl font-black text-indigo-600 premium-font">{{ now()->setTimezone('Africa/Nairobi')->format('H:i:s') }}</div>
                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ now()->setTimezone('Africa/Nairobi')->format('l, jS F') }}</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('student.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors shadow-sm text-sm font-semibold">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('student.rooms') }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-xl hover:bg-indigo-100 transition-colors text-sm font-semibold">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Find Room
                    </a>
                    <a href="{{ route('student.timetable.print') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-xl hover:bg-gray-800 transition-colors shadow-sm text-sm font-semibold">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4"/></svg>
                        PDF
                    </a>
                    <a href="{{ route('student.timetable.full') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl hover:opacity-90 transition-opacity shadow-md text-sm font-semibold">
                        Full Timetable
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>

        @if($entries->isEmpty())
            <div class="bg-white overflow-hidden shadow rounded-lg p-12 text-center">
                <div class="text-6xl mb-4 text-indigo-400 opacity-50">🗓️</div>
                <h3 class="text-xl font-bold text-gray-900">No Timetable Records Found</h3>
                <p class="text-gray-500 mt-2 max-w-sm mx-auto">It seems your personalized schedule hasn't been generated or published yet. Please check back later or contact your institution admin.</p>
            </div>
        @else
            <!-- Weekly Timetable Table -->
            <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">Weekly Timetable</h2>
                        <div class="text-xs text-gray-500">All your classes for the week</div>
                    </div>
                </div>
                <div class="p-6 overflow-auto">
                    @php
                        $dayNames = [1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday'];
                        $timeSlots = [
                            1 => '7:00am-10:00am',
                            2 => '10:00am-1:00pm', 
                            3 => '1:00pm-4:00pm',
                            4 => '4:00pm-7:00pm'
                        ];
                    @endphp
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
                                                    <div class="text-[10px] text-gray-600 mt-1">Course: {{ optional($e->course)->name ?? '—' }}</div>
                                                    <div class="text-[10px] text-green-700 mt-1">
                                                        <span class="px-2 py-0.5 bg-green-100 rounded-full font-semibold">{{ optional($e->room)->name ?? 'TBA' }}</span>
                                                    </div>
                                                    @if($e->lecturer)
                                                        <div class="text-[10px] text-gray-600 mt-1 flex items-center gap-1">
                                                            <span>👨‍🏫 {{ $e->lecturer->name }}</span>
                                                        </div>
                                                    @endif
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
</div>
@section('scripts')
<script>
setInterval(() => {
    const now = new Date();
    const options = { timeZone: 'Africa/Nairobi', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const timeStr = new Intl.DateTimeFormat('en-GB', options).format(now);
    const liveTime = document.getElementById('live-time');
    if (liveTime) liveTime.innerText = timeStr;
}, 1000);
</script>
@endsection

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&display=swap');
    .premium-font { font-family: 'Outfit', sans-serif; }
</style>
@endsection
@endsection
