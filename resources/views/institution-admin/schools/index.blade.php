@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Schools') }}
            </h2>
            <a href="{{ route('institution-admin.schools.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add School
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
                @if($schools->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departments</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($schools as $school)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $school->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $school->code ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $school->departments_count }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('institution-admin.schools.edit', $school) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                <form method="POST" action="{{ route('institution-admin.schools.destroy', $school) }}" onsubmit="return confirm('Delete this school?')">
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
                    <div class="mt-4">{{ $schools->links() }}</div>
                @else
                    <div class="text-center py-12 text-gray-500 border-2 border-dashed rounded-lg">
                        No schools found. Create one.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection