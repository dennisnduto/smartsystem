@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $department->name }}</h1>
                <p class="text-blue-100 mt-1">Department Overview & Management</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.departments.index') }}" class="bg-white text-blue-600 hover:bg-blue-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Back to Departments
                </a>
                <a href="{{ route('institution-admin.departments.edit', $department) }}" class="bg-blue-500 hover:bg-blue-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    Edit Department
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Department Info -->
        <div class="mb-8 bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Department Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Name</h4>
                        <p class="mt-1 text-lg text-gray-900">{{ $department->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Created</h4>
                        <p class="mt-1 text-lg text-gray-900">{{ $department->created_at->format('F j, Y') }}</p>
                        <p class="text-sm text-gray-500">{{ $department->created_at->diffForHumans() }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Last Updated</h4>
                        <p class="mt-1 text-lg text-gray-900">{{ $department->updated_at->format('F j, Y') }}</p>
                        <p class="text-sm text-gray-500">{{ $department->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                
                @if($department->description)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Description</h4>
                        <p class="text-gray-700">{{ $department->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">📚</div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Courses</h3>
                        <p class="text-3xl font-bold text-blue-600">{{ $department->courses->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">🏠</div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Rooms</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $department->rooms->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">📅</div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Timetables</h3>
                        <p class="text-3xl font-bold text-purple-600">{{ $department->timetables->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div class="mb-8 bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Courses ({{ $department->courses->count() }})</h3>
                <a href="{{ route('institution-admin.courses.create') }}?department_id={{ $department->id }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    + Add Course
                </a>
            </div>
            
            @if($department->courses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credits</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($department->courses as $course)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $course->code }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $course->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $course->credits }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $course->year ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('institution-admin.courses.show', $course) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                        <a href="{{ route('institution-admin.courses.edit', $course) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-4xl mb-4">📚</div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No courses added yet</h4>
                    <p class="text-gray-500 mb-4">Add courses to this department to organize your curriculum.</p>
                    <a href="{{ route('institution-admin.courses.create') }}?department_id={{ $department->id }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        + Add First Course
                    </a>
                </div>
            @endif
        </div>

        <!-- Rooms Section -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Rooms ({{ $department->rooms->count() }})</h3>
                <a href="{{ route('institution-admin.rooms.create') }}?department_id={{ $department->id }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition">
                    + Add Room
                </a>
            </div>
            
            @if($department->rooms->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($department->rooms as $room)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $room->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $room->capacity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $room->type ?? 'Standard' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('institution-admin.rooms.show', $room) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                        <a href="{{ route('institution-admin.rooms.edit', $room) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-4xl mb-4">🏠</div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No rooms assigned yet</h4>
                    <p class="text-gray-500 mb-4">Add rooms to this department for timetable scheduling.</p>
                    <a href="{{ route('institution-admin.rooms.create') }}?department_id={{ $department->id }}" class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                        + Add First Room
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection