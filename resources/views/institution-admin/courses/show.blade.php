@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Course Details') }}: {{ $course->name }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('institution-admin.courses.edit', $course) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                <a href="{{ route('institution-admin.courses.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back</a>
            </div>
        </div>
    </div>
</header>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $course->code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $course->department->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duration (years)</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $course->duration_years ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $course->created_at->format('F j, Y') }}</p>
                    </div>
                </div>
                @if($course->description)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $course->description }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection