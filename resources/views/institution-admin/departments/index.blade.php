@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">Departments</h1>
                <p class="text-blue-100 mt-1">Manage departments for {{ $institution->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.dashboard') }}" class="bg-white text-blue-600 hover:bg-blue-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Dashboard
                </a>
                <a href="{{ route('institution-admin.departments.create') }}" class="bg-blue-500 hover:bg-blue-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    + Add Department
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
                <h3 class="text-lg font-semibold text-gray-900">All Departments ({{ $departments->total() }})</h3>
            </div>
            
            @if($departments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courses</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($departments as $department)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $department->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">
                                            {{ Str::limit($department->description, 50) ?: 'No description' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $department->courses_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $department->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('institution-admin.departments.show', $department) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            <a href="{{ route('institution-admin.departments.edit', $department) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('institution-admin.departments.destroy', $department) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this department?')">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $departments->links() }}
                </div>
                
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-6xl mb-4">🏢</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No departments created yet</h3>
                    <p class="text-gray-500 mb-6">Create your first department to start organizing courses and resources.</p>
                    <a href="{{ route('institution-admin.departments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        + Create First Department
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection