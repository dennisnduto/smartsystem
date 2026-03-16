@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Available Rooms & Booking</h1>
  <div class="mb-3 text-sm text-gray-600">Current time: {{ now()->format('H:i') }} (Day {{ $day }}, Slot {{ $slot }})</div>

  <!-- Booking Form -->
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <h2 class="font-semibold mb-3">Quick Room Booking</h2>
    <form id="booking-form" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Room</label>
        <select name="room_id" required class="w-full border rounded px-3 py-2">
          <option value="">Choose a room...</option>
          @foreach($availableRooms as $r)
            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->room_type ?? 'Standard' }}, {{ $r->capacity ?? 'N/A' }} capacity)</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
        <select name="duration" required class="w-full border rounded px-3 py-2">
          <option value="1">1 hour</option>
          <option value="2">2 hours</option>
          <option value="3">3 hours</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
        <input type="text" name="purpose" placeholder="e.g., Study session, Meeting" required class="w-full border rounded px-3 py-2">
      </div>
      <div class="md:col-span-3">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
          Book Room
        </button>
      </div>
    </form>
  </div>

  <!-- Available Rooms List -->
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
      <h2 class="font-semibold">Available Rooms Right Now</h2>
      <p class="text-sm text-gray-600">Rooms not currently in use for this time slot</p>
    </div>
    <div class="divide-y">
      @forelse($availableRooms as $r)
        <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
          <div class="flex-1">
            <div class="font-semibold text-lg">{{ $r->name }}</div>
            <div class="text-sm text-gray-600">
              Type: {{ $r->room_type ?? 'Standard' }} • 
              Capacity: {{ $r->capacity ?? 'N/A' }} • 
              Floor: {{ $r->floor_number ?? 'N/A' }}
            </div>
            @if($r->description)
              <div class="text-xs text-gray-500 mt-1">{{ $r->description }}</div>
            @endif
          </div>
          <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-semibold">Available</span>
            <button onclick="quickBook({{ $r->id }}, '{{ $r->name }}')" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">
              Quick Book
            </button>
          </div>
        </div>
      @empty
        <div class="p-8 text-center text-gray-600">
          <div class="text-lg font-semibold mb-2">No available rooms</div>
          <div class="text-sm">All rooms are currently in use for this time slot.</div>
        </div>
      @endforelse
    </div>
  </div>

  <!-- My Active Bookings -->
  <div class="bg-white rounded-lg shadow mt-6">
    <div class="p-4 border-b">
      <h2 class="font-semibold">My Active Bookings</h2>
      <p class="text-sm text-gray-600">Your current room reservations</p>
    </div>
    <div id="my-bookings" class="divide-y">
      <div class="p-4 text-center text-gray-600">
        <div class="text-sm">Loading your bookings...</div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = '{{ csrf_token() }}';
    
    // Load active bookings
    loadBookings();
    
    // Booking form submission
    document.getElementById('booking-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route('lecturer.room-bookings.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert('Room booked successfully!');
                this.reset();
                loadBookings();
                // Refresh rooms list via AJAX instead of page reload
                refreshAvailableRooms();
            } else {
                alert('Booking failed: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });
});

function quickBook(roomId, roomName) {
    const purpose = prompt(`Quick booking for ${roomName}. What is the purpose?`);
    if (!purpose) return;
    
    const duration = prompt('Duration in hours (1-3):', '1');
    if (!duration || duration < 1 || duration > 3) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('lecturer.room-bookings.store') }}';
    
    form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="room_id" value="${roomId}">
        <input type="hidden" name="duration" value="${duration}">
        <input type="hidden" name="purpose" value="${purpose}">
    `;
    
    document.body.appendChild(form);
    form.submit();
}

async function loadBookings() {
    try {
        const response = await fetch('{{ route('lecturer.room-bookings.index') }}', {
            headers: {
                'Accept': 'application/json',
            }
        });
        
        const bookings = await response.json();
        const container = document.getElementById('my-bookings');
        
        if (bookings.length === 0) {
            container.innerHTML = '<div class="p-4 text-center text-gray-600 text-sm">No active bookings</div>';
            return;
        }
        
        container.innerHTML = bookings.map(booking => `
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="font-semibold">${booking.room.name}</div>
                    <div class="text-sm text-gray-600">
                        ${booking.purpose} • 
                        ${booking.start_time} - ${booking.end_time} • 
                        ${booking.booking_date}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-semibold">
                        ${booking.status}
                    </span>
                    ${booking.can_cancel ? `
                        <button onclick="cancelBooking(${booking.id})" class="px-2 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                            Cancel
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('Error loading bookings:', error);
    }
}

async function cancelBooking(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking?')) return;
    
    try {
        const response = await fetch(`/lecturer/room-bookings/${bookingId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });
        
        if (response.ok) {
            alert('Booking cancelled successfully');
            loadBookings();
            refreshAvailableRooms();
        } else {
            alert('Failed to cancel booking');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function refreshAvailableRooms() {
    try {
        const response = await fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        });
        
        if (response.ok) {
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newRoomsList = doc.querySelector('.divide-y'); // Available rooms container
            const currentRoomsList = document.querySelector('.divide-y');
            
            if (newRoomsList && currentRoomsList) {
                currentRoomsList.innerHTML = newRoomsList.innerHTML;
            }
        }
    } catch (error) {
        console.error('Error refreshing rooms:', error);
    }
}
</script>
@endsection


