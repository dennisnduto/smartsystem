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
                <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <form method="GET" action="{{ route('institution-admin.lecturers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="md:col-span-1">
                            <label for="query" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="query" id="query" value="{{ request('query') }}" placeholder="Name, Email, ID..." 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        
                        <div class="md:col-span-1">
                            <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                            <select name="department_id" id="department_id" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="md:col-span-1">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Filter
                            </button>
                            @if(request()->anyFilled(['query', 'department_id', 'status']))
                                <a href="{{ route('institution-admin.lecturers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse($lecturers as $lecturer)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col">
                            <!-- Card Header: Avatar & Status -->
                            <div class="p-5 border-b border-gray-50 bg-gray-50/30">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-lg shadow-sm">
                                        {{ strtoupper(substr($lecturer->name, 0, 1)) }}{{ @strtoupper(substr(explode(' ', $lecturer->name)[1] ?? '', 0, 1)) }}
                                    </div>
                                    <div>
                                        @if($lecturer->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <h3 class="text-base font-bold text-gray-900 leading-tight mb-1 truncate" title="{{ $lecturer->name }}">
                                    {{ $lecturer->name }}
                                </h3>
                                <div class="text-xs text-indigo-600 font-medium truncate">
                                    {{ $lecturer->department->name ?? 'No Department' }}
                                </div>
                            </div>

                            <!-- Card Body: Details -->
                            <div class="p-5 flex-grow space-y-3">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="truncate" title="{{ $lecturer->email }}">{{ $lecturer->email }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1" />
                                    </svg>
                                    <span class="truncate">ID: {{ $lecturer->employee_id ?? 'N/A' }}</span>
                                </div>
                                @if($lecturer->phone)
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span>{{ $lecturer->phone }}</span>
                                </div>
                                @endif

                                <!-- Assignments -->
                                <div class="pt-2">
                                    <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-2">Assignments</div>
                                    @php($courseNames = $lecturer->courseUnitYears->pluck('course.name')->filter()->unique())
                                    @if($courseNames->count() > 0)
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($courseNames->take(2) as $cname)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[11px] font-medium border border-blue-100">
                                                    {{ $cname }}
                                                </span>
                                            @endforeach
                                            @if($courseNames->count() > 2)
                                                <span class="text-[11px] text-gray-400 px-1">+{{ $courseNames->count() - 2 }} more</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs italic text-gray-400">No courses assigned</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Footer: Actions -->
                            <div class="p-4 bg-gray-50 flex items-center justify-between border-t border-gray-100">
                                <div class="flex space-x-3">
                                    <a href="{{ route('institution-admin.lecturers.show', $lecturer) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">View</a>
                                    <a href="{{ route('institution-admin.lecturers.edit', $lecturer) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Edit</a>
                                </div>
                                <div>
                                    @if($lecturer->is_active)
                                        <form method="POST" action="{{ route('institution-admin.lecturers.deactivate', $lecturer) }}" class="inline" onsubmit="return confirm('Deactivate this lecturer?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('institution-admin.lecturers.activate', $lecturer) }}" class="inline" onsubmit="return confirm('Activate this lecturer?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-green-600 hover:text-green-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">No lecturers found</h3>
                            <p class="text-gray-500 mt-1">Try adjusting your filters or search query.</p>
                            @if(request()->anyFilled(['query', 'department_id', 'status']))
                                <div class="mt-4">
                                    <a href="{{ route('institution-admin.lecturers.index') }}" class="text-indigo-600 font-medium hover:text-indigo-800">Clear all filters</a>
                                </div>
                            @endif
                        </div>
                    @endforelse
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