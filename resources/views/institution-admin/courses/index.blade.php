@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-green-600 to-teal-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">📚 Courses</h1>
                <p class="text-green-100 mt-1">Manage courses for {{ $institution->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.dashboard') }}" class="bg-white text-green-600 hover:bg-green-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Dashboard
                </a>
                <a href="{{ route('institution-admin.courses.create') }}" class="bg-green-500 hover:bg-green-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    + Add Course
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">All Courses ({{ $courses->total() }})</h3>
            </div>
            
            @if($courses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration (Years)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($courses as $course)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $course->code }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $course->name }}</div>
                                        @if($course->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($course->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $course->department->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $course->duration_years ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $course->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('institution-admin.courses.show', $course) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            <a href="{{ route('institution-admin.courses.edit', $course) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('institution-admin.courses.destroy', $course) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this course?')">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $courses->links() }}
                </div>
                
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-6xl mb-4">📚</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No courses created yet</h3>
                    <p class="text-gray-500 mb-6">Create your first course to build your academic curriculum.</p>
                    
                    <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200 max-w-md mx-auto">
                        <h4 class="font-semibold text-green-800 mb-2">💡 Course Information</h4>
                        <p class="text-sm text-green-700">Courses need to be assigned to departments and can include credits, year levels, and descriptions.</p>
                    </div>
                    
                    <a href="{{ route('institution-admin.courses.create') }}" class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                        + Create First Course
                    </a>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-6">
            <h4 class="text-lg font-semibold text-green-900 mb-4">🚀 Course Management</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">🏢</div>
                    <strong class="text-green-900">By Department</strong>
                    <p class="text-green-700 mt-1">Organize courses by academic departments.</p>
                    <a href="{{ route('institution-admin.departments.index') }}" class="mt-2 inline-block text-green-600 hover:text-green-800">View Departments →</a>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">📅</div>
                    <strong class="text-green-900">Timetable Ready</strong>
                    <p class="text-green-700 mt-1">Add courses to create comprehensive timetables.</p>
                    <a href="{{ route('institution-admin.timetables.index') }}" class="mt-2 inline-block text-green-600 hover:text-green-800">View Timetables →</a>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">🏠</div>
                    <strong class="text-green-900">Room Assignment</strong>
                    <p class="text-green-700 mt-1">Ensure rooms are available for courses.</p>
                    <a href="{{ route('institution-admin.rooms.index') }}" class="mt-2 inline-block text-green-600 hover:text-green-800">Manage Rooms →</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection