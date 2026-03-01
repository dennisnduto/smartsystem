@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Student Dashboard</h1>
            <p class="text-gray-600 mt-2">Welcome back, {{ $user->name }} • Live as of: <span id="live-time">{{ now()->format('H:i:s') }}</span></p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="text-3xl mb-2">📚</div>
                    <div class="text-sm font-medium text-gray-500">My Courses</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $courses->count() }}</div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="text-3xl mb-2">🏫</div>
                    <div class="text-sm font-medium text-gray-500">Today's Classes</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $todayEntries->count() }}</div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="text-3xl mb-2">📅</div>
                    <div class="text-sm font-medium text-gray-500">Next Lecture</div>
                    <div class="text-xl font-bold text-gray-900">
                        @if($nextLecture)
                            {{ $nextLecture->unit->code ?? 'Unknown' }}
                        @else
                            <span class="text-gray-400">None</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="text-3xl mb-2">🎓</div>
                    <div class="text-sm font-medium text-gray-500">Year of Study</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $user->year_of_study ? substr($user->year_of_study, 1) : '?' }}</div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="text-3xl mb-2">🏛️</div>
                    <div class="text-sm font-medium text-gray-500">Institution</div>
                    <div class="text-lg font-bold text-gray-900">{{ $user->institution->name ?? 'None' }}</div>
                </div>
            </div>
        </div>

        <!-- Today's Sessions -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Today's Sessions</h2>
            </div>
            <div class="p-6">
                @php
                    $currentDayOfWeek = (int)now()->dayOfWeekIso;
                    $isWeekend = $currentDayOfWeek >= 6;
                    $currentTime = now()->format('H:i');
                    $currentSlot = $currentTime < '10:00' ? 1 : ($currentTime < '13:00' ? 2 : ($currentTime < '16:00' ? 3 : 4));
                    $timeSlots = [1=>'7:00am-10:00am',2=>'10:00am-1:00pm',3=>'1:00pm-4:00pm',4=>'4:00pm-7:00pm'];
                @endphp
                
                @if($isWeekend)
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🌴</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Weekend - No Classes</h3>
                        <p class="text-gray-600">Enjoy your weekend! Classes resume on Monday.</p>
                    </div>
                @else
                    <div class="mb-4">
                        <span class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }} • Current time: <span id="current-time">{{ now()->format('H:i') }}</span></span>
                    </div>
                    
                    @forelse($todayEntries as $entry)
                        <div class="border-l-4 border-blue-500 pl-4 py-3 mb-4 bg-blue-50 rounded-r-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $entry->unit->code ?? 'Unknown' }} — {{ $entry->unit->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ $timeSlots[$entry->slot] }} • Room: {{ $entry->room->name ?? 'TBA' }}
                                        @if($entry->lecturer) • Lecturer: {{ $entry->lecturer->name }} @endif
                                    </div>
                                </div>
                                <div class="text-sm">
                                    @if($entry->slot == $currentSlot)
                                        <span class="px-3 py-1 bg-green-600 text-white text-xs rounded-full animate-pulse">NOW</span>
                                    @elseif($entry->slot < $currentSlot)
                                        <span class="px-3 py-1 bg-gray-400 text-white text-xs rounded-full">COMPLETED</span>
                                    @else
                                        <span class="px-3 py-1 bg-blue-600 text-white text-xs rounded-full">UPCOMING</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">📚</div>
                            <p>No classes scheduled for today</p>
                        </div>
                    @endforelse
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('student.timetable') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                        <div class="text-3xl mb-4">📅</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">View Timetable</h3>
                        <p class="text-sm text-gray-600">See your complete weekly schedule</p>
                    </a>
                    
                    <a href="{{ route('student.rooms') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                        <div class="text-3xl mb-4">🏫</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Room Availability</h3>
                        <p class="text-sm text-gray-600">Check available rooms</p>
                    </a>
                    
                    <a href="{{ route('student.timetable.print') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                        <div class="text-3xl mb-4">🖨️</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Print Timetable</h3>
                        <p class="text-sm text-gray-600">Download as PDF</p>
                    </a>
                    
                    <a href="{{ route('profile.edit') }}" class="block p-6 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                        <div class="text-3xl mb-4">👤</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">My Profile</h3>
                        <p class="text-sm text-gray-600">Update personal info</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time clock updates
function updateLiveTime() {
    const liveTimeElement = document.getElementById('live-time');
    if (liveTimeElement) {
        liveTimeElement.textContent = new Date().toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
}

function updateCurrentTime() {
    const timeDisplay = document.getElementById('current-time');
    if (timeDisplay) {
        timeDisplay.textContent = new Date().toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Start updates
updateLiveTime();
updateCurrentTime();
setInterval(() => {
    updateLiveTime();
    updateCurrentTime();
}, 1000);
</script>
@endsection
