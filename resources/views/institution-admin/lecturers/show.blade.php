@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lecturer Details: ') }} {{ $lecturer->name }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('institution-admin.lecturers.edit', $lecturer) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Lecturer
                </a>
                <a href="{{ route('institution-admin.lecturers.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Lecturers
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Lecturer Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                            Personal Information
                        </h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $lecturer->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $lecturer->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $lecturer->email }}
                                </a>
                            </p>
                        </div>

                        @if($lecturer->phone)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $lecturer->phone }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                            Professional Information
                        </h3>

@if($lecturer->employee_id)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Employee ID</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $lecturer->employee_id }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($lecturer->department)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $lecturer->department->name }}
                                    </span>
                                @else
                                    <span class="text-gray-500">Not assigned</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($lecturer->role) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Account Status</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Courses Taught -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Courses Taught ({{ $stats['total_courses'] }})
                    </h3>

                    @php($assignments = $lecturer->courseUnitYears)
                    @if($assignments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($assignments as $m)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <h4 class="font-semibold text-gray-900 mb-1">{{ $m->course->name ?? 'Course' }}</h4>
                                    <div class="space-y-1 text-sm text-gray-600">
                                        <p><strong>Unit:</strong> {{ ($m->unit->code ?? '—') . ' ' . ($m->unit->name ?? '') }}</p>
                                        <p><strong>Year:</strong> {{ $m->academic_year ?? '—' }} <strong class="ml-2">Sem:</strong> {{ $m->semester ?? '—' }}</p>
                                        @if(optional($m->course->department)->name)
                                            <p><strong>Department:</strong> {{ $m->course->department->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No courses assigned</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    This lecturer is not currently assigned to teach any courses.
                                </p>
                                <div class="mt-6">
                                    <a href="{{ route('institution-admin.lecturers.edit', $lecturer) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Assign Courses
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Account Information -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Account Created</label>
                            <p class="mt-1 text-gray-900">{{ $lecturer->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                            <p class="mt-1 text-gray-900">{{ $lecturer->updated_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="flex items-center justify-between">
                        <div class="space-x-3">
                            <a href="{{ route('institution-admin.lecturers.edit', $lecturer) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Edit Lecturer
                            </a>
                            <a href="{{ route('institution-admin.lecturers.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Back to List
                            </a>
                        </div>
                        
                        <form method="POST" action="{{ route('institution-admin.lecturers.destroy', $lecturer) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this lecturer? This action cannot be undone.')" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Delete Lecturer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection