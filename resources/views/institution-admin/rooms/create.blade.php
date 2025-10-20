@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Room') }}
            </h2>
            <a href="{{ route('institution-admin.rooms.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Rooms
            </a>
        </div>
    </div>
</header>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <strong>Please fix the following errors:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form method="POST" action="{{ route('institution-admin.rooms.store') }}">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Room Name -->
                        <div class="md:col-span-1">
                            <label for="name" class="block text-sm font-medium text-gray-700">Room Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                placeholder="e.g., LR-101, Chemistry Lab A, Main Auditorium"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>


                        <!-- Room Type -->
                        <div class="md:col-span-1">
                            <label for="room_type" class="block text-sm font-medium text-gray-700">Room Type *</label>
                            <select name="room_type" id="room_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Room Type</option>
                                @foreach(\App\Models\Room::getRoomTypes() as $type => $label)
                                    <option value="{{ $type }}" {{ old('room_type') == $type ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Choose the type that best describes this room's primary use.</p>
                        </div>

                        <!-- Capacity -->
                        <div class="md:col-span-1">
                            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity *</label>
                            <input type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" required
                                min="1" max="1000" placeholder="e.g., 50"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Maximum number of people this room can accommodate.</p>
                        </div>
                    </div>

                    <!-- Facilities -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-gray-700">Facilities & Equipment</label>
                            <button type="button" id="add-facility" class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-1 px-3 rounded">
                                + Add Facility
                            </button>
                        </div>
                        
                        <div id="facilities-container">
                            <!-- Dynamic facility fields will be added here -->
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-500">
                            List the key facilities and equipment available in this room (e.g., Projector, Whiteboard, Computer Lab, Sound System, etc.)
                        </p>
                    </div>

                    <!-- Room Type Information -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">Room Type Guidelines</h4>
                        <div class="text-xs text-blue-700 space-y-1">
                            <p><strong>🏫 Normal Room:</strong> Standard classrooms (capacity: 10-100) for lectures, tutorials, and seminars.</p>
                            <p><strong>🏛️ Hall/Auditorium:</strong> Large venues (capacity: 100+) for presentations, events, and big gatherings.</p>
                            <p><strong>🧪 Laboratory:</strong> Specialized rooms for practical work, experiments, and hands-on learning.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <a href="{{ route('institution-admin.rooms.index') }}" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addFacilityBtn = document.getElementById('add-facility');
    const container = document.getElementById('facilities-container');
    let facilityIndex = 0;

    // Common facilities suggestions
    const commonFacilities = [
        'Projector', 'Whiteboard', 'Blackboard', 'Computer Lab', 'Sound System',
        'Air Conditioning', 'WiFi', 'Interactive Board', 'Microphone',
        'Laboratory Equipment', 'Workbenches', 'Safety Equipment', 'Fume Hood',
        'Microscopes', 'Chemicals Storage', 'Stage', 'Podium', 'Audio/Visual Equipment'
    ];

    function createFacilityField(index = facilityIndex) {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2 mb-2';
        div.dataset.index = index;

        let datalistOptions = '';
        commonFacilities.forEach(facility => {
            datalistOptions += `<option value="${facility}">`;
        });

        div.innerHTML = `
            <input type="text" name="facilities[]" 
                placeholder="Enter facility or equipment name" 
                list="facility-suggestions-${index}"
                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <datalist id="facility-suggestions-${index}">
                ${datalistOptions}
            </datalist>
            <button type="button" class="remove-facility bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded" 
                title="Remove Facility">
                ×
            </button>
        `;

        return div;
    }

    // Add facility
    addFacilityBtn.addEventListener('click', function() {
        const field = createFacilityField();
        container.appendChild(field);
        facilityIndex++;
        
        // Add event listener for remove button
        field.querySelector('.remove-facility').addEventListener('click', function() {
            field.remove();
        });
        
        // Focus on the new input
        field.querySelector('input').focus();
    });

    // Add initial facility field
    addFacilityBtn.click();

    // Room type specific suggestions
    const roomTypeSelect = document.getElementById('room_type');
    roomTypeSelect.addEventListener('change', function() {
        const roomType = this.value;
        
        // Clear existing facilities and add type-specific ones
        container.innerHTML = '';
        facilityIndex = 0;
        
        let suggestions = [];
        if (roomType === 'lab') {
            suggestions = ['Laboratory Equipment', 'Workbenches', 'Safety Equipment', 'Fume Hood', 'Microscopes'];
        } else if (roomType === 'hall') {
            suggestions = ['Stage', 'Podium', 'Sound System', 'Projector', 'Microphone'];
        } else if (roomType === 'normal') {
            suggestions = ['Projector', 'Whiteboard', 'Air Conditioning'];
        }
        
        // Add suggested facilities
        suggestions.forEach(suggestion => {
            const field = createFacilityField();
            field.querySelector('input').value = suggestion;
            container.appendChild(field);
            facilityIndex++;
            
            // Add event listener for remove button
            field.querySelector('.remove-facility').addEventListener('click', function() {
                field.remove();
            });
        });
        
        // Add one empty field
        if (suggestions.length > 0) {
            addFacilityBtn.click();
        }
    });
});
</script>
@endsection