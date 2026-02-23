@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-indigo-600 to-purple-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">📅 Timetables</h1>
                <p class="text-indigo-100 mt-1">Manage teaching timetables for {{ $institution->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.dashboard') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Dashboard
                </a>
                <a href="{{ route('institution-admin.timetables.create') }}" class="bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    + Create Timetable
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
                <h3 class="text-lg font-semibold text-gray-900">All Timetables ({{ $timetables->total() }})</h3>
            </div>
            
            @if($timetables->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timetable</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($timetables as $timetable)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-indigo-500 rounded-full mr-3"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $timetable->name }}</div>
                                                @if($timetable->academic_year)
                                                    <div class="text-sm text-gray-500">{{ $timetable->academic_year }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $timetable->semester ?? 'N/A' }}</div>
                                        @if($timetable->week_start)
                                            <div class="text-sm text-gray-500">Starting {{ $timetable->week_start->format('M d, Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($timetable->status === 'draft')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                        @elseif($timetable->status === 'pending_approval')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending Approval</span>
                                        @elseif($timetable->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Approved</span>
                                        @elseif($timetable->status === 'published')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $timetable->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('institution-admin.timetables.show', $timetable) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            <a href="{{ route('institution-admin.timetables.edit', $timetable) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <form action="{{ route('institution-admin.timetables.destroy', $timetable) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this timetable?')">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $timetables->links() }}
                </div>
                
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-6xl mb-4">📅</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No timetables created yet</h3>
                    <p class="text-gray-500 mb-6">Create your first timetable to start organizing class schedules.</p>
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-semibold text-blue-800 mb-2">📋 Prerequisites for Timetable Creation</h4>
                        <ul class="text-sm text-blue-700 space-y-1 text-left max-w-md mx-auto">
                            <li>✅ Departments must be created first</li>
                            <li>✅ Courses must be added to departments</li>
                            <li>✅ Rooms must be assigned to departments</li>
                            <li>⚠️ Lecturers should be registered (coming soon)</li>
                        </ul>
                    </div>
                    
                    <a href="{{ route('institution-admin.timetables.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 transition">
                        + Create First Timetable
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection