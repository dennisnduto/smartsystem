@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Course-Unit-Year Mapping') }}
            </h2>
            <a href="{{ route('institution-admin.course-unit-years.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Mapping
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

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                    <select name="department_id" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="course_id" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }} ({{ $course->department->name }})
                            </option>
                        @endforeach
                    </select>
                    <select name="academic_year" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Any Year</option>
                        @foreach(['Y1'=>'Year 1','Y2'=>'Year 2','Y3'=>'Year 3','Y4'=>'Year 4','Y5'=>'Year 5'] as $key=>$label)
                            <option value="{{ $key }}" {{ request('academic_year') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="semester" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Any Semester</option>
                        @foreach(['S1'=>'Semester 1','S2'=>'Semester 2'] as $key=>$label)
                            <option value="{{ $key }}" {{ request('semester') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="bg-gray-800 text-white rounded-md px-4 py-2">Filter</button>
                </form>

                @if($mappings->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($mappings as $m)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $m->course->department->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $m->course->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $m->unit->code }} — {{ $m->unit->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $m->academic_year }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $m->semester ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('institution-admin.course-unit-years.edit', $m) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                <form method="POST" action="{{ route('institution-admin.course-unit-years.destroy', $m) }}" onsubmit="return confirm('Delete this mapping?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $mappings->links() }}</div>
                @else
                    <div class="text-center py-12 text-gray-500 border-2 border-dashed rounded-lg">
                        No mappings found. Create one.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection