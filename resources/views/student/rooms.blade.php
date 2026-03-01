@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-bold">Real-time Room Availability</h1>
      <p class="text-sm text-gray-600 mt-1">Check which rooms are currently available</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('student.dashboard') }}" class="px-3 py-1.5 bg-gray-600 text-white rounded">Dashboard</a>
      <a href="{{ route('student.timetable') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Timetable</a>
    </div>
  </div>

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
    $timeSlot = isset($timeSlots[$slot]) ? $timeSlots[$slot] : 'Unknown';
    $isActiveSlot = $slot > 0 && $phpDay >= 1 && $phpDay <= 5; // Monday=1 to Friday=5
    
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

  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
    <div class="text-sm font-semibold text-blue-900">Current Time Slot</div>
    <div class="text-sm text-blue-700 mt-1">
      {{ $dayName }}, {{ $timeSlot }}
      @if(!$isActiveSlot)
        <span class="ml-2 text-orange-600 font-medium">• No scheduled classes</span>
      @endif
    </div>
    <div class="text-xs text-blue-600 mt-1">
      Last updated: <span id="live-timestamp">{{ $timestampMessage }}</span>
      <span id="refresh-indicator" class="ml-2 text-green-600 hidden">♻ Refreshing...</span>
    </div>
    <div class="text-xs text-red-600 mt-1">
      {{ $debugInfo }}
    </div>
    <div class="text-xs text-blue-600 mt-1">
      Auto-refresh every 30 seconds
    </div>
  </div>

  <div id="rooms-container" class="bg-white rounded-xl shadow divide-y">
    @forelse($availableRooms as $room)
      <div class="p-6 flex items-center justify-between hover:bg-gray-50 border-l-4 border-green-500 transition-all duration-300">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <div class="text-2xl">🏫</div>
            <div>
              <div class="font-bold text-xl text-gray-900">{{ $room->name }}</div>
              <div class="text-sm text-gray-600">
                {{ ucfirst($room->room_type ?? 'Normal') }} • Capacity: {{ $room->capacity ?? 'Not specified' }} people
              </div>
            </div>
          </div>
          @if($room->facilities && is_array($room->facilities) && (count($room->facilities) > 0))
            <div class="text-sm text-gray-600 mb-2">
              <strong>Facilities:</strong> {{ implode(', ', $room->facilities) }}
            </div>
          @endif
          @if($room->department)
            <div class="text-xs text-gray-500">
              <strong>Department:</strong> {{ $room->department->name }}
            </div>
          @endif
        </div>
        <div class="text-center">
          <span class="px-4 py-2 rounded-full bg-green-100 text-green-700 text-sm font-bold">AVAILABLE NOW</span>
          <div class="text-xs text-gray-500 mt-1">Free to use</div>
        </div>
      </div>
    @empty
      <div class="p-8 text-center text-gray-600">
        <div class="text-6xl mb-4">🏫</div>
        <div class="text-xl font-semibold mb-2">No Rooms Available</div>
        <div>All rooms are currently in use or reserved for this time slot.</div>
        <div class="text-sm text-gray-500 mt-2">Try checking back later or a different time slot.</div>
      </div>
    @endforelse
  </div>

  <div class="mt-4 text-sm text-gray-500 text-center">
    Room availability is updated in real-time based on published timetables and active bookings.
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
