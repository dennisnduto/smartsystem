@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-8">
      <div class="flex items-center justify-between">
        <div class="text-white">
          <h1 class="text-3xl font-bold mb-2">Real-time Room Availability</h1>
          <p class="text-indigo-100">Check which rooms are currently available for use</p>
        </div>
        <div class="flex space-x-3">
          <a href="{{ route('student.dashboard') }}" class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition duration-200 flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>Dashboard</span>
          </a>
          <a href="{{ route('student.timetable') }}" class="px-4 py-2 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 transition duration-200 flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>Timetable</span>
          </a>
        </div>
      </div>
    </div>

    <div class="p-6">

  @php
    $dayNames = [0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
    $timeSlots = [
      0 => 'Outside Class Hours',
      1 => '7:00am-10:00am',
      2 => '10:00am-1:00pm', 
      3 => '1:00pm-4:00pm',
      4 => '4:00pm-7:00pm'
    ];
    
    // Use standard PHP day format (0=Sunday, 1=Monday, ..., 6=Saturday)
    $now = $now->setTimezone('Africa/Nairobi'); // Set to East Africa Time (UTC+3)
    $phpDay = (int)$now->format('w');
    $dayName = isset($dayNames[$phpDay]) ? $dayNames[$phpDay] : 'Unknown Day';
    $timeSlot = isset($timeSlots[$activeSlot]) ? $timeSlots[$activeSlot] : 'Unknown';
    $isActiveSlot = $activeSlot > 0 && $phpDay >= 1 && $phpDay <= 5; // Monday=1 to Friday=5
    
    // DEBUG: Show actual values
    $debugInfo = "DEBUG: now=" . $now->format('Y-m-d H:i:s') . ", format(w)=" . $now->format('w') . ", phpDay=$phpDay, dayName=$dayName";
    
    // Generate intelligent timestamp message
    $currentHour = (int)$now->format('H');
    $timestampMessage = $now->format('H:i:s');
    
    if ($phpDay >= 6 && $phpDay <= 6) {
      // Saturday (6)
      if ($currentHour >= 0 && $currentHour < 6) {
        $timestampMessage .= ' (Weekend Night)';
      } elseif ($currentHour >= 6 && $currentHour < 12) {
        $timestampMessage .= ' (Weekend Morning)';
      } elseif ($currentHour >= 12 && $currentHour < 18) {
        $timestampMessage .= ' (Weekend Afternoon)';
      } elseif ($currentHour >= 18 && $currentHour < 22) {
        $timestampMessage .= ' (Weekend Evening)';
      } else {
        $timestampMessage .= ' (Late Night)';
      }
    } elseif ($phpDay == 0) {
      // Sunday (0)
      if ($currentHour >= 0 && $currentHour < 6) {
        $timestampMessage .= ' (Weekend Night)';
      } elseif ($currentHour >= 6 && $currentHour < 12) {
        $timestampMessage .= ' (Weekend Morning)';
      } elseif ($currentHour >= 12 && $currentHour < 18) {
        $timestampMessage .= ' (Weekend Afternoon)';
      } elseif ($currentHour >= 18 && $currentHour < 22) {
        $timestampMessage .= ' (Weekend Evening)';
      } else {
        $timestampMessage .= ' (Late Night)';
      }
    } else {
      // Weekday (1=Monday to 5=Friday)
      if ($currentHour >= 0 && $currentHour < 6) {
        $timestampMessage .= ' (Night Time)';
      } elseif ($currentHour >= 6 && $currentHour < 7) {
        $timestampMessage .= ' (Early Morning - Before Classes)';
      } elseif ($currentHour >= 7 && $currentHour < 10) {
        $timestampMessage .= ' (Morning Classes)';
      } elseif ($currentHour >= 10 && $currentHour < 13) {
        $timestampMessage .= ' (Late Morning Classes)';
      } elseif ($currentHour >= 13 && $currentHour < 16) {
        $timestampMessage .= ' (Afternoon Classes)';
      } elseif ($currentHour >= 16 && $currentHour < 19) {
        $timestampMessage .= ' (Evening Classes)';
      } elseif ($currentHour >= 19 && $currentHour < 22) {
        $timestampMessage .= ' (Evening - After Classes)';
      } else {
        $timestampMessage .= ' (Late Night)';
      }
    }
  @endphp

  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 mb-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="flex items-center space-x-2 mb-2">
          <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
          <div class="text-sm font-semibold text-blue-900">Current Time Slot</div>
        </div>
        <div class="text-lg font-medium text-blue-800 mb-1">
          {{ $dayName }}, {{ $timeSlot }}
          @if(!$isActiveSlot)
            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
              No scheduled classes
            </span>
          @endif
        </div>
        <div class="text-sm text-blue-600">
          Last updated: <span id="live-timestamp" class="font-mono font-medium">{{ $timestampMessage }}</span>
          <span id="refresh-indicator" class="ml-2 text-green-600 hidden flex items-center">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Refreshing...
          </span>
        </div>
      </div>
      <div class="text-right">
        <div class="text-xs text-blue-500 font-medium">Auto-refresh</div>
        <div class="text-sm text-blue-700 font-semibold">Every 30 seconds</div>
      </div>
    </div>
  </div>

  <div id="rooms-container" class="space-y-4">
    @forelse($availableRooms as $room)
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
        <div class="flex items-center justify-between p-6">
          <div class="flex-1">
            <div class="flex items-center gap-4 mb-3">
              <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center text-white text-xl">
                🏫
              </div>
              <div>
                <div class="text-xl font-bold text-gray-900">{{ $room->name }}</div>
                <div class="text-sm text-gray-600">
                  {{ ucfirst($room->room_type ?? 'Standard') }} Room • Capacity: {{ $room->capacity ?? 'Not specified' }} people
                </div>
              </div>
            </div>
            @if($room->facilities && is_array($room->facilities) && (count($room->facilities) > 0))
              <div class="flex flex-wrap gap-2 mb-3">
                @foreach($room->facilities as $facility)
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ $facility }}
                  </span>
                @endforeach
              </div>
            @endif
            @if($room->department)
              <div class="flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                {{ $room->department->name }}
              </div>
            @endif
          </div>
          <div class="text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-bold shadow-lg">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              AVAILABLE NOW
            </div>
            <div class="text-xs text-gray-500 mt-2">Free to use</div>
          </div>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
        <div class="text-xl font-semibold text-gray-900 mb-2">No Rooms Available</div>
        <div class="text-gray-600 mb-4">All rooms are currently in use or reserved for this time slot.</div>
        <div class="text-sm text-gray-500">Try checking back later or a different time slot.</div>
      </div>
    @endforelse
  </div>

  <div class="mt-8 text-center">
    <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full text-sm text-gray-600">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      Room availability is updated in real-time based on published timetables and active bookings.
    </div>
  </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate intelligent timestamp message
    function getTimestampMessage() {
        const now = new Date();
        const timestamp = now.toTimeString().split(' ')[0];
        const hour = now.getHours();
        const day = now.getDay(); // 0=Sunday, 1=Monday, ..., 6=Saturday
        
        let message = timestamp;
        
        if (day >= 6 && day <= 6) {
            // Saturday (6)
            if (hour >= 0 && hour < 6) {
                message += ' (Weekend Night)';
            } else if (hour >= 6 && hour < 12) {
                message += ' (Weekend Morning)';
            } else if (hour >= 12 && hour < 18) {
                message += ' (Weekend Afternoon)';
            } else if (hour >= 18 && hour < 22) {
                message += ' (Weekend Evening)';
            } else {
                message += ' (Late Night)';
            }
        } else if (day == 0) {
            // Sunday (0)
            if (hour >= 0 && hour < 6) {
                message += ' (Weekend Night)';
            } else if (hour >= 6 && hour < 12) {
                message += ' (Weekend Morning)';
            } else if (hour >= 12 && hour < 18) {
                message += ' (Weekend Afternoon)';
            } else if (hour >= 18 && hour < 22) {
                message += ' (Weekend Evening)';
            } else {
                message += ' (Late Night)';
            }
        } else {
            // Weekday (1=Monday to 5=Friday)
            if (hour >= 0 && hour < 6) {
                message += ' (Night Time)';
            } else if (hour >= 6 && hour < 7) {
                message += ' (Early Morning - Before Classes)';
            } else if (hour >= 7 && hour < 10) {
                message += ' (Morning Classes)';
            } else if (hour >= 10 && hour < 13) {
                message += ' (Late Morning Classes)';
            } else if (hour >= 13 && hour < 16) {
                message += ' (Afternoon Classes)';
            } else if (hour >= 16 && hour < 19) {
                message += ' (Evening Classes)';
            } else if (hour >= 19 && hour < 22) {
                message += ' (Evening - After Classes)';
            } else {
                message += ' (Late Night)';
            }
        }
        
        return message;
    }

    // Update timestamp every second
    function updateTimestamp() {
        const timestampElement = document.getElementById('live-timestamp');
        if (timestampElement) {
            timestampElement.textContent = getTimestampMessage();
        }
    }

    // Refresh room data
    async function refreshRooms() {
        const refreshIndicator = document.getElementById('refresh-indicator');
        const roomsContainer = document.getElementById('rooms-container');
        
        try {
            refreshIndicator.classList.remove('hidden');
            
            const response = await fetch('{{ route("student.rooms") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newRoomsContainer = doc.getElementById('rooms-container');
                const newTimeSlotInfo = doc.querySelector('.bg-blue-50');
                
                if (newRoomsContainer && roomsContainer) {
                    roomsContainer.innerHTML = newRoomsContainer.innerHTML;
                    
                    // Add fade-in animation
                    roomsContainer.style.opacity = '0';
                    setTimeout(() => {
                        roomsContainer.style.transition = 'opacity 0.3s ease-in';
                        roomsContainer.style.opacity = '1';
                    }, 100);
                }
                
                // Update time slot info if changed
                if (newTimeSlotInfo) {
                    const currentTimeSlotInfo = document.querySelector('.bg-blue-50');
                    if (currentTimeSlotInfo) {
                        currentTimeSlotInfo.innerHTML = newTimeSlotInfo.innerHTML;
                    }
                }
                
                updateTimestamp();
            }
        } catch (error) {
            console.error('Error refreshing rooms:', error);
        } finally {
            refreshIndicator.classList.add('hidden');
        }
    }

    // Update timestamp immediately
    updateTimestamp();
    
    // Update timestamp every second
    setInterval(updateTimestamp, 1000);
    
    // Refresh rooms every 30 seconds
    setInterval(refreshRooms, 30000);
    
    // Add manual refresh button functionality
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
            e.preventDefault();
            refreshRooms();
        }
    });
});
</script>
@endsection
