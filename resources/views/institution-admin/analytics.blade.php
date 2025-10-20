@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-purple-600 to-indigo-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">Analytics Dashboard</h1>
                <p class="text-purple-100 mt-1">{{ $institution->name }} - Comprehensive Analytics</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.dashboard') }}" class="bg-white text-purple-600 hover:bg-purple-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Back to Dashboard
                </a>
                <a href="{{ route('institution-admin.reports') }}" class="bg-purple-500 hover:bg-purple-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    📋 Generate Report
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Overview Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-400 to-blue-600 overflow-hidden shadow-lg rounded-xl text-white">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm uppercase tracking-wider">Departments</p>
                            <p class="text-3xl font-bold">{{ count($analytics['departments_overview']) }}</p>
                        </div>
                        <div class="text-4xl opacity-80">🏢</div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-400 to-green-600 overflow-hidden shadow-lg rounded-xl text-white">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm uppercase tracking-wider">Lecturers</p>
                            <p class="text-3xl font-bold">{{ $analytics['user_distribution']['lecturers'] }}</p>
                        </div>
                        <div class="text-4xl opacity-80">👨‍🏫</div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-400 to-orange-500 overflow-hidden shadow-lg rounded-xl text-white">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm uppercase tracking-wider">Students</p>
                            <p class="text-3xl font-bold">{{ $analytics['user_distribution']['students'] }}</p>
                        </div>
                        <div class="text-4xl opacity-80">🎓</div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-400 to-purple-600 overflow-hidden shadow-lg rounded-xl text-white">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm uppercase tracking-wider">Active Timetables</p>
                            <p class="text-3xl font-bold">{{ $analytics['timetable_status']['published'] }}</p>
                        </div>
                        <div class="text-4xl opacity-80">📅</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Department Overview Chart -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                        📊 Department Overview
                    </h3>
                    <div class="space-y-4">
                        @foreach($analytics['departments_overview'] as $department)
                            <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-medium text-gray-900">{{ $department->name }}</h4>
                                    <span class="text-sm text-gray-500">{{ $department->courses_count + $department->rooms_count }} total resources</span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div class="flex items-center">
                                        <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                                        <span>{{ $department->courses_count }} Courses</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                                        <span>{{ $department->rooms_count }} Rooms</span>
                                    </div>
                                </div>
                                <!-- Simple progress bar -->
                                <div class="mt-3">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        @php
                                            $total = max($department->courses_count + $department->rooms_count, 1);
                                            $coursePercent = ($department->courses_count / $total) * 100;
                                            $roomPercent = ($department->rooms_count / $total) * 100;
                                        @endphp
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $coursePercent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Timetable Status Chart -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                        📅 Timetable Status Distribution
                    </h3>
                    <div class="space-y-6">
                        <!-- Published Timetables -->
                        <div class="border rounded-lg p-4 bg-green-50 border-green-200">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center">
                                    <span class="w-4 h-4 bg-green-500 rounded-full mr-3"></span>
                                    <span class="font-medium text-green-800">Published</span>
                                </div>
                                <span class="text-2xl font-bold text-green-600">{{ $analytics['timetable_status']['published'] }}</span>
                            </div>
                            <p class="text-sm text-green-600 ml-7">Active and being used</p>
                        </div>

                        <!-- Draft Timetables -->
                        <div class="border rounded-lg p-4 bg-yellow-50 border-yellow-200">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center">
                                    <span class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></span>
                                    <span class="font-medium text-yellow-800">Draft</span>
                                </div>
                                <span class="text-2xl font-bold text-yellow-600">{{ $analytics['timetable_status']['draft'] }}</span>
                            </div>
                            <p class="text-sm text-yellow-600 ml-7">In development</p>
                        </div>

                        <!-- Total -->
                        <div class="border rounded-lg p-4 bg-gray-50 border-gray-200">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center">
                                    <span class="w-4 h-4 bg-gray-500 rounded-full mr-3"></span>
                                    <span class="font-medium text-gray-800">Total</span>
                                </div>
                                <span class="text-2xl font-bold text-gray-600">{{ $analytics['timetable_status']['published'] + $analytics['timetable_status']['draft'] }}</span>
                            </div>
                            <p class="text-sm text-gray-600 ml-7">All timetables</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Distribution & Room Utilization -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- User Distribution -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                        👥 User Distribution
                    </h3>
                    
                    @php
                        $totalUsers = $analytics['user_distribution']['lecturers'] + $analytics['user_distribution']['students'];
                        $lecturerPercent = $totalUsers > 0 ? ($analytics['user_distribution']['lecturers'] / $totalUsers) * 100 : 0;
                        $studentPercent = $totalUsers > 0 ? ($analytics['user_distribution']['students'] / $totalUsers) * 100 : 0;
                    @endphp
                    
                    <div class="space-y-6">
                        <!-- Lecturers -->
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-green-700">Lecturers</span>
                                <span class="text-sm font-medium text-green-700">{{ $analytics['user_distribution']['lecturers'] }} ({{ number_format($lecturerPercent, 1) }}%)</span>
                            </div>
                            <div class="bg-green-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full transition-all duration-300" style="width: {{ $lecturerPercent }}%"></div>
                            </div>
                        </div>

                        <!-- Students -->
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-medium text-blue-700">Students</span>
                                <span class="text-sm font-medium text-blue-700">{{ $analytics['user_distribution']['students'] }} ({{ number_format($studentPercent, 1) }}%)</span>
                            </div>
                            <div class="bg-blue-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full transition-all duration-300" style="width: {{ $studentPercent }}%"></div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-800">Total Users</span>
                                <span class="text-2xl font-bold text-gray-600">{{ $totalUsers }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Utilization -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                        🏠 Room Utilization
                    </h3>
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        @forelse($analytics['room_utilization'] as $room)
                            @php
                                $utilizationRate = min(($room->timetable_entries_count / 10) * 100, 100); // Assume max 10 entries = 100%
                                $utilizationClass = $utilizationRate >= 80 ? 'bg-red-500' : ($utilizationRate >= 50 ? 'bg-yellow-500' : 'bg-green-500');
                                $utilizationTextClass = $utilizationRate >= 80 ? 'text-red-700' : ($utilizationRate >= 50 ? 'text-yellow-700' : 'text-green-700');
                            @endphp
                            <div class="border rounded-lg p-3 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-gray-900">{{ $room->name ?? 'Room ' . $room->id }}</span>
                                    <span class="text-sm {{ $utilizationTextClass }} font-medium">{{ number_format($utilizationRate, 1) }}%</span>
                                </div>
                                <div class="bg-gray-200 rounded-full h-2">
                                    <div class="{{ $utilizationClass }} h-2 rounded-full transition-all duration-300" style="width: {{ $utilizationRate }}%"></div>
                                </div>
                                <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                                    <span>{{ $room->department->name ?? 'Unknown Dept' }}</span>
                                    <span>{{ $room->timetable_entries_count }} bookings</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <div class="text-4xl mb-4">🏠</div>
                                <p class="text-gray-500">No rooms found for analysis</p>
                                <a href="{{ route('institution-admin.rooms.create') }}" class="text-blue-600 hover:text-blue-800 text-sm">Add rooms to see utilization</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl mb-8">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">📈 Key Performance Indicators</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Lecturer-to-Student Ratio -->
                    <div class="text-center p-4 border rounded-lg">
                        <div class="text-3xl mb-2">⚖️</div>
                        <h4 class="font-medium text-gray-700 mb-2">Lecturer-to-Student Ratio</h4>
                        @php
                            $ratio = $analytics['user_distribution']['lecturers'] > 0 
                                ? round($analytics['user_distribution']['students'] / $analytics['user_distribution']['lecturers'], 1) 
                                : 0;
                        @endphp
                        <p class="text-2xl font-bold text-indigo-600">1:{{ $ratio }}</p>
                        <p class="text-sm text-gray-500">{{ $ratio <= 20 ? 'Excellent' : ($ratio <= 30 ? 'Good' : 'Needs attention') }}</p>
                    </div>

                    <!-- Timetable Completion Rate -->
                    <div class="text-center p-4 border rounded-lg">
                        <div class="text-3xl mb-2">✅</div>
                        <h4 class="font-medium text-gray-700 mb-2">Timetable Completion</h4>
                        @php
                            $totalTimetables = $analytics['timetable_status']['published'] + $analytics['timetable_status']['draft'];
                            $completionRate = $totalTimetables > 0 ? round(($analytics['timetable_status']['published'] / $totalTimetables) * 100, 1) : 0;
                        @endphp
                        <p class="text-2xl font-bold text-green-600">{{ $completionRate }}%</p>
                        <p class="text-sm text-gray-500">{{ $analytics['timetable_status']['published'] }}/{{ $totalTimetables }} published</p>
                    </div>

                    <!-- Average Courses per Department -->
                    <div class="text-center p-4 border rounded-lg">
                        <div class="text-3xl mb-2">📚</div>
                        <h4 class="font-medium text-gray-700 mb-2">Avg Courses/Department</h4>
                        @php
                            $totalCourses = $analytics['departments_overview']->sum('courses_count');
                            $avgCourses = count($analytics['departments_overview']) > 0 ? round($totalCourses / count($analytics['departments_overview']), 1) : 0;
                        @endphp
                        <p class="text-2xl font-bold text-blue-600">{{ $avgCourses }}</p>
                        <p class="text-sm text-gray-500">{{ $totalCourses }} total courses</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🚀 Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('institution-admin.departments.create') }}" class="bg-white hover:bg-gray-50 p-4 rounded-lg shadow-sm border text-center transition group">
                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">🏢</div>
                    <p class="font-medium text-gray-700">Add Department</p>
                </a>
                
                <a href="{{ route('institution-admin.lecturers.create') }}" class="bg-white hover:bg-gray-50 p-4 rounded-lg shadow-sm border text-center transition group">
                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">👨‍🏫</div>
                    <p class="font-medium text-gray-700">Add Lecturer</p>
                </a>
                
                <a href="{{ route('institution-admin.courses.create') }}" class="bg-white hover:bg-gray-50 p-4 rounded-lg shadow-sm border text-center transition group">
                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">📚</div>
                    <p class="font-medium text-gray-700">Add Course</p>
                </a>
                
                <a href="{{ route('institution-admin.rooms.create') }}" class="bg-white hover:bg-gray-50 p-4 rounded-lg shadow-sm border text-center transition group">
                    <div class="text-2xl mb-2 group-hover:scale-110 transition-transform">🏠</div>
                    <p class="font-medium text-gray-700">Add Room</p>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Add some interactivity for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars on load
    const progressBars = document.querySelectorAll('[style*="width"]');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
});
</script>
@endsection