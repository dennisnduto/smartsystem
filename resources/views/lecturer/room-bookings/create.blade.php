@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Create Room Booking</h1>
    <a href="{{ route('lecturer.room-bookings.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
      Back to Bookings
    </a>
  </div>

  <div class="bg-white rounded-lg shadow">
    <form action="{{ route('lecturer.room-bookings.store') }}" method="POST" class="p-6">
      @csrf
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Room Selection -->
        <div>
          <label for="room_id" class="block text-sm font-medium text-gray-700 mb-2">
            Select Room <span class="text-red-500">*</span>
          </label>
          <select id="room_id" name="room_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Choose a room...</option>
            @foreach($rooms as $room)
              <option value="{{ $room->id }}">
                {{ $room->name }} ({{ $room->room_type ?? 'Standard' }}, {{ $room->capacity ?? 'N/A' }} capacity)
              </option>
            @endforeach
          </select>
        </div>

        <!-- Booking Date -->
        <div>
          <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">
            Booking Date <span class="text-red-500">*</span>
          </label>
          <input type="date" id="booking_date" name="booking_date" required 
                 min="{{ now()->toDateString() }}"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Start Time -->
        <div>
          <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
            Start Time <span class="text-red-500">*</span>
          </label>
          <select id="start_time" name="start_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select start time...</option>
            <option value="07:00">7:00 AM</option>
            <option value="08:00">8:00 AM</option>
            <option value="09:00">9:00 AM</option>
            <option value="10:00">10:00 AM</option>
            <option value="11:00">11:00 AM</option>
            <option value="12:00">12:00 PM</option>
            <option value="13:00">1:00 PM</option>
            <option value="14:00">2:00 PM</option>
            <option value="15:00">3:00 PM</option>
            <option value="16:00">4:00 PM</option>
            <option value="17:00">5:00 PM</option>
            <option value="18:00">6:00 PM</option>
          </select>
        </div>

        <!-- End Time -->
        <div>
          <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
            End Time <span class="text-red-500">*</span>
          </label>
          <select id="end_time" name="end_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select end time...</option>
            <option value="08:00">8:00 AM</option>
            <option value="09:00">9:00 AM</option>
            <option value="10:00">10:00 AM</option>
            <option value="11:00">11:00 AM</option>
            <option value="12:00">12:00 PM</option>
            <option value="13:00">1:00 PM</option>
            <option value="14:00">2:00 PM</option>
            <option value="15:00">3:00 PM</option>
            <option value="16:00">4:00 PM</option>
            <option value="17:00">5:00 PM</option>
            <option value="18:00">6:00 PM</option>
            <option value="19:00">7:00 PM</option>
          </select>
        </div>

        <!-- Purpose -->
        <div class="md:col-span-2">
          <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
            Purpose <span class="text-red-500">*</span>
          </label>
          <input type="text" id="purpose" name="purpose" required 
                 placeholder="e.g., Study session, Meeting, Consultation"
                 maxlength="255"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Course (Optional) -->
        <div>
          <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
            Related Course (Optional)
          </label>
          <select id="course_id" name="course_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">No specific course</option>
            @foreach($courses as $course)
              <option value="{{ $course->id }}">{{ $course->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Notes (Optional) -->
        <div>
          <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
            Additional Notes (Optional)
          </label>
          <textarea id="notes" name="notes" rows="3" maxlength="1000"
                    placeholder="Any additional information..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex items-center justify-between mt-6 pt-6 border-t">
        <div class="text-sm text-gray-500">
          <p>* Required fields</p>
          <p>Bookings cannot extend beyond 7:00 PM</p>
        </div>
        <div class="flex gap-3">
          <a href="{{ route('lecturer.room-bookings.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
            Cancel
          </a>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Create Booking
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    
    // Update end time options when start time changes
    startTime.addEventListener('change', function() {
        const startValue = this.value;
        const endOptions = endTime.options;
        
        // Clear current selection
        endTime.value = '';
        
        // Enable and disable options based on start time
        for (let i = 0; i < endOptions.length; i++) {
            if (endOptions[i].value && endOptions[i].value <= startValue) {
                endOptions[i].disabled = true;
                endOptions[i].style.display = 'none';
            } else {
                endOptions[i].disabled = false;
                endOptions[i].style.display = '';
            }
        }
    });
    
    // Validate end time is after start time
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (startTime.value && endTime.value && endTime.value <= startTime.value) {
            e.preventDefault();
            alert('End time must be after start time');
            return false;
        }
    });
});
</script>
@endsection
