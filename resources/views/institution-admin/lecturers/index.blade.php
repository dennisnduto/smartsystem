@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Lecturers') }}
            </h2>
            <a href="{{ route('institution-admin.lecturers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add New Lecturer
            </a>
        </div>
    </div>
</header>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courses</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($lecturers as $lecturer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $lecturer->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lecturer->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lecturer->employee_id ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lecturer->department->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
@php($courseNames = $lecturer->courseUnitYears->pluck('course.name')->filter()->unique())
                                        @if($courseNames->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($courseNames->take(3) as $cname)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $cname }}
                                                    </span>
                                                @endforeach
                                                @if($courseNames->count() > 3)
                                                    <span class="text-xs text-gray-500">+{{ $courseNames->count() - 3 }} more</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">No courses assigned</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lecturer->phone ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($lecturer->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                                                Deactivated
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('institution-admin.lecturers.show', $lecturer) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            <a href="{{ route('institution-admin.lecturers.edit', $lecturer) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            @if($lecturer->is_active)
                                                <form method="POST" action="{{ route('institution-admin.lecturers.deactivate', $lecturer) }}" class="inline" onsubmit="return confirm('Deactivate this lecturer? They will not be able to log in.')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('institution-admin.lecturers.activate', $lecturer) }}" class="inline" onsubmit="return confirm('Activate this lecturer?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-700 hover:text-green-900">Activate</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        <div class="flex flex-col items-center py-8">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239" />
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No lecturers found</h3>
                                            <p class="text-gray-500 text-center mb-4">
                                                Get started by adding your first lecturer to the system.
                                            </p>
                                            <a href="{{ route('institution-admin.lecturers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                                Add First Lecturer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($lecturers->hasPages())
                    <div class="mt-4">
                        {{ $lecturers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection