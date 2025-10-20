@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-indigo-600 to-purple-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">📅 Create Timetable</h1>
                <p class="text-indigo-100 mt-1">Create a new teaching timetable for {{ $institution->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.timetables.index') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Back to Timetables
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Please fix the following errors:</strong>
                <ul class="mt-2">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Timetable Information</h3>
            </div>
            
            <form action="{{ route('institution-admin.timetables.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Timetable Name *</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="e.g., Institution-wide Timetable 2025-2026"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">This timetable will cover all courses, rooms, and lecturer-unit mappings in your institution.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700 mb-2">Semester *</label>
                        <select name="semester" 
                                id="semester" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('semester') border-red-500 @enderror"
                                required>
                            <option value="">Select Semester</option>
                            <option value="Semester 1" {{ old('semester') == 'Semester 1' ? 'selected' : '' }}>Semester 1</option>
                            <option value="Semester 2" {{ old('semester') == 'Semester 2' ? 'selected' : '' }}>Semester 2</option>
                            <option value="Summer Session" {{ old('semester') == 'Summer Session' ? 'selected' : '' }}>Summer Session</option>
                        </select>
                        @error('semester')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-2">Academic Year *</label>
                        <input type="text" 
                               name="academic_year" 
                               id="academic_year" 
                               value="{{ old('academic_year', date('Y') . '-' . (date('Y') + 1)) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('academic_year') border-red-500 @enderror"
                               placeholder="e.g., 2025-2026"
                               required>
                        @error('academic_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="week_start" class="block text-sm font-medium text-gray-700 mb-2">Week Start Date *</label>
                    <input type="date" 
                           name="week_start" 
                           id="week_start" 
                           value="{{ old('week_start', date('Y-m-d', strtotime('next monday'))) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('week_start') border-red-500 @enderror"
                           required>
                    @error('week_start')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Select the Monday when this timetable will start.</p>
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('institution-admin.timetables.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Create Timetable
                    </button>
                </div>
            </form>
        </div>

        <!-- Prerequisites Info -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
            <h4 class="text-lg font-semibold text-blue-900 mb-4">📋 Timetable Coverage</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">📚</div>
                    <strong class="text-blue-900">All Courses</strong>
                    <p class="text-blue-700 mt-1">This timetable will include all courses across your institution.</p>
                    <a href="{{ route('institution-admin.courses.index') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">Manage Courses →</a>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">🏫</div>
                    <strong class="text-blue-900">All Rooms</strong>
                    <p class="text-blue-700 mt-1">All available rooms will be used for scheduling.</p>
                    <a href="{{ route('institution-admin.rooms.index') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">Manage Rooms →</a>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">👨‍🏫</div>
                    <strong class="text-blue-900">Lecturer Assignments</strong>
                    <p class="text-blue-700 mt-1">Uses existing lecturer-unit mappings for scheduling.</p>
                    <a href="{{ route('institution-admin.lecturers.index') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">Manage Lecturers →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-format academic year input
document.getElementById('academic_year').addEventListener('blur', function() {
    let value = this.value.replace(/[^\d]/g, '');
    if (value.length >= 4) {
        let year1 = value.substring(0, 4);
        let year2 = (parseInt(year1) + 1).toString();
        this.value = year1 + '-' + year2;
    }
});

// Set week start to next Monday by default
document.addEventListener('DOMContentLoaded', function() {
    const weekStartInput = document.getElementById('week_start');
    if (!weekStartInput.value) {
        const today = new Date();
        const dayOfWeek = today.getDay();
        const daysUntilMonday = (dayOfWeek === 0) ? 1 : (8 - dayOfWeek);
        const nextMonday = new Date(today);
        nextMonday.setDate(today.getDate() + daysUntilMonday);
        
        weekStartInput.value = nextMonday.toISOString().split('T')[0];
    }
});
</script>
@endsection