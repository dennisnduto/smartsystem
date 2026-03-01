@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-2xl font-bold">Available Rooms & Booking</h1>
    <button onclick="window.location.reload()" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition flex items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
      </svg>
      Refresh Now
    </button>
  </div>
  <div class="mb-3 text-sm text-gray-600 flex items-center justify-between">
    <span>Live as of: <span id="live-time">{{ now()->format('H:i:s') }}</span> ({{ now()->format('l, F j, Y') }})</span>
    <span class="flex items-center text-green-600">
      <span class="w-2 h-2 bg-green-600 rounded-full mr-2 animate-pulse"></span>
      Live Updates • Refresh in <span id="refresh-countdown">15</span>s
    </span>
  </div>
  
  @php
    // Calculate current time slot for context
    $h = (int)now()->format('H');
    $currentSlot = $h < 10 ? 1 : ($h < 13 ? 2 : ($h < 16 ? 3 : 4));
    $slotTimes = [1=>'7:00-10:00', 2=>'10:00-13:00', 3=>'13:00-16:00', 4=>'16:00-19:00'];
    $currentSlotTime = $slotTimes[$currentSlot] ?? 'Outside hours';
  @endphp
  
  <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
    <div class="flex items-center text-blue-800">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span class="text-sm font-medium">Current Time Slot: <span id="current-time-slot">{{ now()->format('H:i') }}</span></span>
      <span id="weekend-indicator" class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-semibold hidden">Weekend</span>
    </div>
    <div class="text-xs text-blue-600 mt-1">Rooms are only marked "Occupied" during ongoing classes or active bookings</div>
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-2xl">🏠</div>
      <div class="text-sm text-gray-500">Total Rooms</div>
      <div class="text-xl font-bold">{{ $allRooms->count() }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-2xl">✅</div>
      <div class="text-sm text-gray-500">Available Now</div>
      <div class="text-xl font-bold">{{ $availableRooms->count() }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-2xl">📋</div>
      <div class="text-sm text-gray-500">My Bookings</div>
      <div class="text-xl font-bold">{{ $myBookings->count() }}</div>
    </div>
  </div>

  <!-- Quick Booking Form -->
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <h2 class="font-semibold mb-3">Quick Room Booking</h2>
    <form id="booking-form" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Room</label>
        <select name="room_id" required class="w-full border rounded px-3 py-2">
          <option value="">Choose a room...</option>
          @foreach($availableRooms as $r)
            <option value="{{ $r->id }}">{{ $r->name }} - {{ $r->room_type ?? 'Standard' }} ({{ $r->capacity ?? 'N/A' }})</option>
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
        <input type="text" name="purpose" placeholder="e.g., Study session" required class="w-full border rounded px-3 py-2">
      </div>
      <div class="flex items-end">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition w-full">
          Book Room
        </button>
      </div>
    </form>
  </div>

  <!-- Available Rooms List -->
  <div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4 border-b">
      <h2 class="font-semibold">Available Rooms Right Now</h2>
      <p class="text-sm text-gray-600">Rooms not currently in use</p>
    </div>
    <div class="divide-y">
      @forelse($availableRooms as $r)
        <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
          <div class="flex-1">
            <div class="font-semibold text-lg">{{ $r->name }}</div>
            <div class="text-sm text-gray-600">
              Type: {{ $r->room_type ?? 'Standard' }} • 
              Capacity: {{ $r->capacity ?? 'N/A' }}
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

  <!-- All Rooms List -->
  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
      <h2 class="font-semibold">All Rooms</h2>
      <p class="text-sm text-gray-600">Complete room inventory</p>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($allRooms as $room)
            @php
              $isAvailable = $availableRooms->contains('id', $room->id);
            @endphp
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ $room->name }}</div>
                @if($room->description)
                  <div class="text-xs text-gray-500">{{ $room->description }}</div>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $room->room_type ?? 'Standard' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $room->capacity ?? 'N/A' }}</div>
              </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                @if($isAvailable)
                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Available
                  </span>
                @else
                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                    Occupied
                  </span>
                @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                @if($isAvailable)
                  <button onclick="quickBook({{ $room->id }}, '{{ $room->name }}')" class="text-blue-600 hover:text-blue-900">
                    Book Now
                  </button>
                @else
                  <span class="text-gray-400">Not Available</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrf = '{{ csrf_token() }}';
    
    // Update live time every second
    function updateLiveTime() {
        const liveTimeElement = document.getElementById('live-time');
        if (liveTimeElement) {
            liveTimeElement.textContent = new Date().toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }
    
    // Update immediately and then every second
    updateLiveTime();
    setInterval(updateLiveTime, 1000);
    
    // Update time slot in real-time
    function updateTimeSlot() {
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const currentTime = `${hours}:${minutes}`;
        
        // Calculate current time slot
        let currentSlot;
        if (hours < 7 || hours >= 19) {
            currentSlot = 'Outside hours (19:00-7:00)';
        } else if (hours < 10) {
            currentSlot = '7:00-10:00';
        } else if (hours < 13) {
            currentSlot = '10:00-13:00';
        } else if (hours < 16) {
            currentSlot = '13:00-16:00';
        } else {
            currentSlot = '16:00-19:00';
        }
        
        const slotElement = document.getElementById('current-time-slot');
        if (slotElement) {
            slotElement.textContent = currentSlot;
        }
    }
    
    // Update time slot immediately and every second
    updateTimeSlot();
    setInterval(updateTimeSlot, 1000);
    
    // Update weekend indicator
    function updateWeekendIndicator() {
        const now = new Date();
        const dayOfWeek = now.getDay(); // 0=Sunday, 6=Saturday
        const weekendIndicator = document.getElementById('weekend-indicator');
        
        if (weekendIndicator) {
            if (dayOfWeek === 0 || dayOfWeek === 6) { // Sunday or Saturday
                weekendIndicator.classList.remove('hidden');
                weekendIndicator.textContent = dayOfWeek === 0 ? 'Sunday' : 'Saturday';
            } else {
                weekendIndicator.classList.add('hidden');
            }
        }
    }
    
    // Update weekend indicator immediately and every second
    updateWeekendIndicator();
    setInterval(updateWeekendIndicator, 1000);
    
    // Countdown timer for refresh
    let refreshCountdown = 15;
    const countdownElement = document.getElementById('refresh-countdown');
    
    function updateCountdown() {
        if (countdownElement) {
            countdownElement.textContent = refreshCountdown;
        }
        refreshCountdown--;
        
        if (refreshCountdown < 0) {
            refreshCountdown = 15; // Reset countdown
        }
    }
    
    // Update countdown every second
    updateCountdown();
    setInterval(updateCountdown, 1000);
    
    // Auto-refresh page every 15 seconds for truly live data
    setInterval(() => window.location.reload(), 15000);
    
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
                // Refresh page to update availability
                setTimeout(() => window.location.reload(), 1000);
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
</script>
@endsection
