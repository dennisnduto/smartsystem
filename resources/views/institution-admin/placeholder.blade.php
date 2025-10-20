@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-purple-600 to-indigo-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $title }}</h1>
                <p class="text-purple-100 mt-1">{{ ucfirst($type) }} management for {{ auth()->user()->institution->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.dashboard') }}" class="bg-white text-purple-600 hover:bg-purple-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Dashboard
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }} Overview</h3>
            </div>
            
            @if($items->count() > 0)
                <div class="p-6">
                    <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                        <p><strong>Note:</strong> Full {{ strtolower($type) }} management interface is coming soon. Below is a preview of your {{ strtolower($title) }}.</p>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($items as $item)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                @if($type === 'lecturer' || $type === 'student')
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $item->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $item->email }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $type === 'lecturer' ? 'green' : 'blue' }}-100 text-{{ $type === 'lecturer' ? 'green' : 'blue' }}-800">
                                            {{ ucfirst($type) }}
                                        </span>
                                    </div>
                                @elseif($type === 'timetable')
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $item->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $item->department->name ?? 'No department' }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $items->links() }}
                    </div>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-6xl mb-4">
                        @if($type === 'lecturer')
                            👨‍🏫
                        @elseif($type === 'student')  
                            🎓
                        @elseif($type === 'timetable')
                            📅
                        @endif
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No {{ strtolower($title) }} found</h3>
                    <p class="text-gray-500 mb-6">
                        @if($type === 'lecturer')
                            No lecturers have been added to your institution yet.
                        @elseif($type === 'student')
                            No students have been enrolled in your institution yet.
                        @elseif($type === 'timetable')
                            No timetables have been created yet. Create departments and courses first.
                        @endif
                    </p>
                    
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6">
                        <p><strong>Coming Soon:</strong> Full management interface with create, edit, and delete functionality.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Setup Guide -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
            <h4 class="text-lg font-semibold text-blue-900 mb-4">🚀 Quick Setup Guide</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">1️⃣</div>
                    <strong class="text-blue-900">Create Departments</strong>
                    <p class="text-blue-700 mt-1">Set up your academic departments first.</p>
                    <a href="{{ route('institution-admin.departments.index') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">Manage Departments →</a>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">2️⃣</div>
                    <strong class="text-blue-900">Add Courses & Rooms</strong>
                    <p class="text-blue-700 mt-1">Create courses and assign rooms to departments.</p>
                    <div class="mt-2 space-x-2">
                        <a href="{{ route('institution-admin.courses.index') }}" class="text-blue-600 hover:text-blue-800">Courses</a>
                        <span class="text-gray-400">•</span>
                        <a href="{{ route('institution-admin.rooms.index') }}" class="text-blue-600 hover:text-blue-800">Rooms</a>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-2xl mb-2">3️⃣</div>
                    <strong class="text-blue-900">Generate Timetables</strong>
                    <p class="text-blue-700 mt-1">Create and publish your timetables.</p>
                    <a href="{{ route('institution-admin.dashboard') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">Back to Dashboard →</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection