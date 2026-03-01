@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">My Room Bookings</h1>
    <a href="{{ route('lecturer.room-bookings.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
      New Booking
    </a>
  </div>

  <div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
      <h2 class="font-semibold">Active & Recent Bookings</h2>
      <p class="text-sm text-gray-600">Your room reservations and their status</p>
    </div>
    
    @if($bookings->isEmpty())
      <div class="p-8 text-center text-gray-600">
        <div class="text-lg font-semibold mb-2">No bookings found</div>
        <div class="text-sm mb-4">You haven't made any room bookings yet.</div>
        <a href="{{ route('lecturer.room-bookings.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Create Your First Booking
        </a>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($bookings as $booking)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ $booking->room->name }}</div>
                  <div class="text-sm text-gray-500">{{ $booking->room->room_type ?? 'Standard' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ $booking->purpose }}</div>
                  @if($booking->course)
                    <div class="text-xs text-gray-500">{{ $booking->course->name }}</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $booking->booking_date->format('M d, Y') }}</div>
                  <div class="text-xs text-gray-500">{{ $booking->booking_date->format('l') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $booking->start_time }} - {{ $booking->end_time }}</div>
                  <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->end_time)->diffInHours(\Carbon\Carbon::parse($booking->start_time)) }} hours</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @php
                    $canCancel = $booking->status === 'active' && 
                                 ($booking->booking_date->isFuture() || 
                                  ($booking->booking_date->isToday() && now()->format('H:i:s') < $booking->end_time->format('H:i:s')));
                  @endphp
                  
                  @if($booking->status === 'active')
                    @if($booking->booking_date->isFuture())
                      <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        Upcoming
                      </span>
                    @elseif($booking->booking_date->isToday() && now()->format('H:i:s') < $booking->end_time->format('H:i:s'))
                      <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Active Now
                      </span>
                    @else
                      <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                        Completed
                      </span>
                    @endif
                  @elseif($booking->status === 'cancelled')
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                      Cancelled
                    </span>
                  @elseif($booking->status === 'expired')
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                      Expired
                    </span>
                  @else
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                      {{ $booking->status }}
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  @if($canCancel)
                    <button onclick="cancelBooking({{ $booking->id }})" class="text-red-600 hover:text-red-900">
                      Cancel
                    </button>
                  @else
                    <span class="text-gray-400">—</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-2xl">📅</div>
      <div class="text-sm text-gray-500">Total Bookings</div>
      <div class="text-xl font-bold">{{ $bookings->count() }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-2xl">✅</div>
      <div class="text-sm text-gray-500">Active Bookings</div>
      <div class="text-xl font-bold">
        {{ $bookings->where('status', 'active')->where('booking_date', '>=', now()->toDateString())->count() }}
      </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <div class="text-2xl">🏠</div>
      <div class="text-sm text-gray-500">Rooms Used</div>
      <div class="text-xl font-bold">{{ $bookings->pluck('room_id')->unique()->count() }}</div>
    </div>
  </div>
</div>

<script>
function cancelBooking(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
    }
    
    fetch(`/lecturer/room-bookings/${bookingId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            // Reload the page to show updated booking status
            window.location.reload();
        } else {
            return response.json().then(data => {
                alert('Error cancelling booking: ' + (data.message || 'Unknown error'));
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error cancelling booking. Please try again.');
    });
}
</script>
@endsection
