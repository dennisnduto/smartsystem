@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Room Management') }}
            </h2>
            <a href="{{ route('institution-admin.rooms.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add New Room
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
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold">Rooms ({{ $rooms->total() }})</h3>
                        <p class="text-sm text-gray-600">Manage your institution's rooms and facilities</p>
                    </div>
                </div>

                @if($rooms->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facilities</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($rooms as $room)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $room->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $room->department->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($room->room_type === 'lab') bg-purple-100 text-purple-800
                                                @elseif($room->room_type === 'hall') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @if($room->room_type === 'lab') 🧪 Laboratory
                                                @elseif($room->room_type === 'hall') 🏛️ Hall/Auditorium
                                                @else 🏫 Normal Room
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $room->capacity }} people</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($room->facilities && count($room->facilities) > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach(array_slice($room->facilities, 0, 3) as $facility)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                            {{ $facility }}
                                                        </span>
                                                    @endforeach
                                                    @if(count($room->facilities) > 3)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                            +{{ count($room->facilities) - 3 }} more
                                                        </span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400">No facilities listed</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('institution-admin.rooms.show', $room) }}" 
                                                   class="text-blue-600 hover:text-blue-900">View</a>
                                                <a href="{{ route('institution-admin.rooms.edit', $room) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <form method="POST" action="{{ route('institution-admin.rooms.destroy', $room) }}" 
                                                      class="inline" onsubmit="return confirm('Are you sure you want to delete this room?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $rooms->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 text-6xl mb-4">🏫</div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No rooms found</h3>
                        <p class="text-gray-500 mb-4">Get started by adding your first room.</p>
                        <a href="{{ route('institution-admin.rooms.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add Room
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Room Type Legend -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 border-l-4 border-blue-400 bg-blue-50">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Room Types</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p><strong>🏫 Normal Room:</strong> Standard classrooms for regular lectures and seminars.</p>
                            <p><strong>🏛️ Hall/Auditorium:</strong> Large venues for presentations, ceremonies, and big gatherings.</p>
                            <p><strong>🧪 Laboratory:</strong> Specialized spaces equipped for practical work and experiments.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection