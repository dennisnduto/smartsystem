<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lecturer Dashboard') }}
        </h2>
    </x-slot>

@if(!empty($renderError))
<div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
  {{ $renderError }}
</div>
@endif

<div class="max-w-7xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Lecturer Dashboard</h1>
      <p class="text-sm text-gray-600">Welcome back, {{ $user->name }}</p>
    </div>
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('lecturer.timetable.full') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all font-semibold flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
        Institution Timetable
      </a>
      <a href="{{ route('lecturer.assigned') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition-all font-semibold flex items-center">
        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        My Assignments
      </a>
      <a href="{{ route('lecturer.rooms') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all font-semibold flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Free Rooms
      </a>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">📚</div>
      <div class="text-sm text-gray-500">This Week Classes</div>
      <div class="text-xl font-bold">{{ $entries->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🏫</div>
      <div class="text-sm text-gray-500">Rooms Used</div>
      <div class="text-xl font-bold">{{ $entries->pluck('room')->unique('id')->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🧪</div>
      <div class="text-sm text-gray-500">Lab Sessions</div>
      <div class="text-xl font-bold">{{ $labCount ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🎯</div>
      <div class="text-sm text-gray-500">Upcoming Next</div>
      @php
        $now = now();
        $currentDay = max(1, min(5, (int)$now->dayOfWeekIso));
        
        // Simple time to slot calculation
        $h = (int)$now->format('H');
        $currentSlot = $h < 10 ? 1 : ($h < 13 ? 2 : ($h < 16 ? 3 : 4));
        
        $next = $entries->filter(function($e) use ($currentDay, $currentSlot) {
            return $e->day_of_week > $currentDay || 
                   ($e->day_of_week == $currentDay && $e->slot > $currentSlot);
        })->sortBy(['day_of_week','slot'])->first();
        
        if (!$next) {
            // If no upcoming class today, check next week
            $next = $entries->sortBy(['day_of_week','slot'])->first();
        }
      @endphp
      <div class="text-sm">
        @if($next)
            <div class="font-semibold">{{ optional($next->unit)->code ?? '—' }}</div>
            <div class="text-xs text-gray-600">{{ optional($next->unit)->name ?? '—' }}</div>
            <div class="text-xs text-gray-600">{{ optional($next->course)->name ?? '—' }}</div>
            <div class="text-xs text-gray-600">Year {{ $next->year_of_study ?? '—' }}</div>
            <div class="text-xs text-green-600">{{ optional($next->room)->name ?? 'TBA' }}</div>
        @else
            —
        @endif
      </div>
    </div>
  </div>

  <!-- Today List + Availability -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-xl shadow p-4">
      <h2 class="font-semibold mb-3">Today</h2>
      @php 
        $currentDayOfWeek = (int)now()->dayOfWeekIso;
        $isWeekend = $currentDayOfWeek > 5; // Saturday (6) or Sunday (7)
        
        if (!$isWeekend) {
          $today = max(1, min(5, $currentDayOfWeek));
          $todayEntries = $entries->where('day_of_week', $today)->sortBy('slot');
        }
      @endphp
      
      @if($isWeekend)
        <div class="text-center py-8">
          <div class="text-2xl mb-2">🏖️</div>
          <div class="text-lg font-semibold text-gray-700">Weekend</div>
          <div class="text-sm text-gray-500">No classes scheduled on weekends</div>
        </div>
      @else
        @forelse($todayEntries as $e)
          <div class="p-3 border rounded mb-2">
            <div class="text-sm text-gray-500">Slot {{ $e->slot }} ({{ ['7-10','10-13','13-16','16-19'][$e->slot-1] }})</div>
            <div class="font-semibold">{{ optional($e->unit)->code }} — {{ optional($e->unit)->name }}</div>
            <div class="text-sm text-gray-600">{{ optional($e->course)->name }}</div>
            <div class="text-sm text-gray-600">Year {{ $e->year_of_study ?? '—' }}</div>
            <div class="text-sm text-gray-600">Room: {{ optional($e->room)->name ?? 'TBA' }}</div>
          </div>
        @empty
          <div class="text-gray-500 text-center py-4">
            <div class="text-sm">No classes scheduled for today.</div>
            <div class="text-xs text-gray-400 mt-1">Enjoy your free time!</div>
          </div>
        @endforelse
      @endif
    </div>
    <div id="lecturer-availability" class="bg-white rounded-xl shadow p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Availability</h2>
        <div class="flex items-center gap-2 text-[11px] text-gray-500">
          <span class="inline-flex items-center gap-1"><span class="legend legend-free"></span> Free</span>
          <span class="inline-flex items-center gap-1"><span class="legend legend-busy"></span> Busy</span>
          <span class="inline-flex items-center gap-1"><span class="legend legend-auto"></span> Class</span>
        </div>
      </div>
      <div class="overflow-auto">
        <table class="min-w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
          @php $daysFull = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri']; @endphp
          <thead>
            <tr class="bg-gray-50">
              <th class="p-1 px-2 text-left font-semibold text-gray-600 border-b border-r">Slot</th>
              @foreach($daysFull as $d)
                <th class="p-1 px-2 text-left font-semibold text-gray-600 border-b border-r text-[10px]">{{ $d }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @for($slot=1;$slot<=4;$slot++)
              <tr class="border-t">
                <td class="p-1 px-2 font-medium text-gray-700 border-r text-[10px]">{{ ['7-10','10-13','13-16','16-19'][$slot-1] }}</td>
                @for($day=1;$day<=5;$day++)
                  @php
                    $status = $availability[$day][$slot] ?? 'busy'; // default: unavailable/busy
                    $isFree = $status === 'available';
                    $isBusy = $status === 'busy';
                    $isAuto = $status === 'auto_busy';
                  @endphp
                  <td class="p-2 border-r">
                    <button
                      type="button"
                      class="avail-cell {{ $isFree ? 'is-free' : '' }} {{ $isBusy ? 'is-busy' : '' }} {{ $isAuto ? 'is-auto-busy' : '' }}"
                      data-day="{{ $day }}"
                      data-slot="{{ $slot }}"
                      data-status="{{ $status }}"
                      @if($isAuto) disabled @endif
                    >
                      <span class="avail-label">
                        @if($isAuto)
                          Class
                        @elseif($isFree)
                          Free
                        @else
                          Busy
                        @endif
                      </span>
                    </button>
                  </td>
                @endfor
              </tr>
            @endfor
          </tbody>
        </table>
      </div>
      <div class="mt-3 text-[10px] text-gray-500">
        Click a cell to toggle between Free and Busy. Slots with an active class are locked.
      </div>
    </div>
  </div>

  <!-- Quick Rooms Preview -->
  <div class="bg-white rounded-xl shadow p-4" id="rooms-preview-container">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold">Available Rooms</h2>
      <div class="flex items-center gap-2">
        <button onclick="refreshDashboardData()" id="manual-refresh-btn" class="px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 transition flex items-center">
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Refresh
        </button>
        <a href="{{ route('lecturer.rooms') }}" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
      </div>
    </div>
    <div class="text-sm text-gray-600 mb-3 flex items-center justify-between">
    <span>Live as of: <span id="dashboard-live-time">{{ now()->format('H:i:s') }}</span></span>
    <span class="flex items-center text-green-600 text-xs">
      <span id="refresh-pulse" class="w-2 h-2 bg-green-600 rounded-full mr-1 animate-pulse"></span>
      <span id="refresh-status">Auto-refresh every 30s</span>
    </span>
  </div>
    
    <div class="mb-3 p-2 bg-blue-50 rounded border border-blue-200">
      <div class="flex items-center text-blue-800">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="text-xs font-medium">Current Slot: <span id="dashboard-time-slot">{{ now()->format('H:i') }}</span></span>
        <span id="dashboard-weekend-indicator" class="ml-2 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-semibold hidden">Weekend</span>
      </div>
    </div>
    
    @php
      // Get precise current timestamp for live availability
      $currentDateTime = now();
      $currentTime = $currentDateTime->format('H:i:s');
      $currentDate = $currentDateTime->toDateString();
      $currentDay = (int)$currentDateTime->dayOfWeekIso; // 1=Monday, 7=Sunday
      $h = (int)$currentDateTime->format('H');
      $currentSlot = $h < 10 ? 1 : ($h < 13 ? 2 : ($h < 16 ? 3 : 4));
      
      // Get rooms occupied by timetable entries for current slot (weekdays only)
      $occupiedRoomIds = [];
      if ($currentDay >= 1 && $currentDay <= 5) { // Weekdays only
        $occupiedRoomIds = $entries
          ->where('day_of_week', $currentDay)
          ->where('slot', $currentSlot)
          ->pluck('room_id')
          ->toArray();
      }
      
      // Get rooms occupied by active bookings (precise timestamp - only currently active)
      $bookingBusyRoomIds = \App\Models\RoomBooking::where('institution_id', $user->institution_id ?? 1)
        ->where('status', 'active')
        ->where('booking_date', $currentDate)
        ->where('start_time', '<=', $currentTime)
        ->where('end_time', '>', $currentTime) // Only currently active bookings
        ->pluck('room_id')
        ->toArray();
      
      $allBusyRoomIds = array_merge($occupiedRoomIds, $bookingBusyRoomIds);
      
      $availableRooms = \App\Models\Room::whereNotIn('id', $allBusyRoomIds)
        ->where('institution_id', $user->institution_id ?? 1)
        ->orderBy('name')
        ->take(3)
        ->get();
    @endphp
    
    @forelse($availableRooms as $room)
      <div class="flex items-center justify-between p-3 border rounded mb-2 hover:bg-gray-50">
        <div>
          <div class="font-medium">{{ $room->name }}</div>
          <div class="text-xs text-gray-600">{{ $room->room_type ?? 'Standard' }} ({{ $room->capacity ?? 'N/A' }})</div>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Available</span>
          <button onclick="quickBookRoom({{ $room->id }}, '{{ $room->name }}')" class="px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
            Book
          </button>
        </div>
      </div>
    @empty
      <div class="text-center py-4 text-gray-500">
        <div class="text-sm">No rooms available at this time</div>
        <div class="text-xs text-gray-400 mt-1">All rooms are currently in use</div>
      </div>
    @endforelse
  </div>

  <!-- My Bookings -->
  <div class="bg-white rounded-xl shadow p-4" id="my-bookings-container">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold">My Bookings</h2>
      <a href="{{ route('lecturer.room-bookings.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
    </div>
    
    @php
      // Get current user's active bookings with precise timestamp
      $currentDateTime = now();
      $currentTime = $currentDateTime->format('H:i:s');
      $currentDate = $currentDateTime->toDateString();
      
      $myBookings = \App\Models\RoomBooking::where('lecturer_id', $user->id ?? 0)
        ->where('status', 'active')
        ->where('booking_date', '>=', $currentDate)
        ->with('room')
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->take(3)
        ->get();
    @endphp
    
    @forelse($myBookings as $booking)
      <div class="flex items-center justify-between p-3 border rounded mb-2 hover:bg-gray-50">
        <div>
          <div class="font-medium">{{ $booking->room->name }}</div>
          <div class="text-xs text-gray-600">{{ $booking->purpose }}</div>
          <div class="text-xs text-gray-600">{{ $booking->booking_date->format('M d') }} • {{ $booking->start_time }} - {{ $booking->end_time }}</div>
        </div>
        <div class="flex items-center gap-2">
          @if($booking->booking_date->format('Y-m-d') === $currentDate && $currentTime >= $booking->start_time && $currentTime < $booking->end_time)
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Active Now</span>
          @elseif($booking->booking_date->format('Y-m-d') === $currentDate && $currentTime < $booking->start_time)
            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">Today {{ $booking->start_time }}</span>
          @elseif($booking->booking_date->isFuture())
            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Upcoming</span>
          @else
            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">Completed</span>
          @endif
        </div>
      </div>
    @empty
      <div class="text-center py-4 text-gray-500">
        <div class="text-sm">No active bookings</div>
        <div class="text-xs text-gray-400 mt-1">Book a room for your sessions</div>
      </div>
    @endforelse
  </div>



  <!-- Chatbot -->
  <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap');
      
      .glass-panel-chat {
          background: rgba(255, 255, 255, 0.7);
          backdrop-filter: blur(10px);
          -webkit-backdrop-filter: blur(10px);
          border: 1px solid rgba(255, 255, 255, 0.3);
      }
      
      .chat-bubble-user {
          background: linear-gradient(135deg, #2563eb, #1d4ed8);
          border-radius: 1.2rem 1.2rem 0.2rem 1.2rem;
          box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
          color: white;
      }
      
      .chat-bubble-bot {
          background: white;
          border-radius: 1.2rem 1.2rem 1.2rem 0.2rem;
          border: 1px solid rgba(0, 0, 0, 0.05);
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
          color: #1e293b;
      }
      
      .chat-scroll-area {
          scrollbar-width: thin;
          scrollbar-color: rgba(0, 0, 0, 0.1) transparent;
      }
      
      .chat-scroll-area::-webkit-scrollbar {
          width: 5px;
      }
      
      .chat-scroll-area::-webkit-scrollbar-thumb {
          background-color: rgba(0, 0, 0, 0.1);
          border-radius: 20px;
      }

      @keyframes slideUpFade {
          from { opacity: 0; transform: translateY(15px); }
          to { opacity: 1; transform: translateY(0); }
      }

      .message-enter {
          animation: slideUpFade 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
      }

      .dot-pulse {
          width: 5px;
          height: 5px;
          background: #64748b;
          border-radius: 50%;
          animation: dotBounce 1.4s infinite ease-in-out both;
      }

      .dot-pulse:nth-child(2) { animation-delay: 0.2s; }
      .dot-pulse:nth-child(3) { animation-delay: 0.4s; }

      @keyframes dotBounce {
          0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
          40% { transform: scale(1); opacity: 1; }
      }
  </style>

  <div class="glass-panel-chat overflow-hidden shadow-xl rounded-3xl mb-12 border-0 relative">
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
      <div class="p-6">
          <div class="flex items-center justify-between mb-6">
              <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-xl shadow-inner">
                      🧠
                  </div>
                  <div>
                      <h3 class="text-lg font-bold text-gray-800" style="font-family: 'Outfit', sans-serif;">Lecturer Assistant</h3>
                      <div class="flex items-center space-x-1.5 text-[10px] font-bold text-green-600 uppercase tracking-tighter">
                          <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                          <span>AI Core Active</span>
                      </div>
                  </div>
              </div>
              <button onclick="clearLecturerChat()" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Clear Chat">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
              </button>
          </div>
          
          <div id="chatbot-container" class="h-64 overflow-y-auto chat-scroll-area px-1 mb-4">
              <div id="chatbot-messages" class="space-y-4">
                  <div class="flex justify-start message-enter">
                      <div class="max-w-[85%] px-4 py-2.5 chat-bubble-bot">
                          <p class="text-sm" style="font-family: 'Inter', sans-serif;">Hello <span class="font-semibold text-indigo-600">{{ $user->name }}</span>. I'm ready to assist with your teaching schedule or room availability.</p>
                      </div>
                  </div>
              </div>
          </div>
          
          <form id="chatbot-form" class="relative group">
              @csrf
              <div class="flex items-center bg-white/60 backdrop-blur-sm border border-gray-100 rounded-2xl p-1.5 focus-within:ring-2 focus-within:ring-indigo-100 focus-within:border-indigo-200 transition-all">
                  <input type="text" name="q" id="chatbot-input" 
                         placeholder="e.g., When is my next lab?" 
                         class="flex-1 bg-transparent border-0 focus:ring-0 text-sm py-2 px-3 text-gray-700">
                  <button type="submit" 
                          class="bg-indigo-600 hover:bg-indigo-700 text-white p-2 rounded-xl shadow-md shadow-indigo-100 transition-transform active:scale-95">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                      </svg>
                  </button>
              </div>
          </form>
      </div>
  </div>
  <a href="{{ route('lecturer.timetable.full') }}" class="flex items-center gap-2 group border border-indigo-100 bg-indigo-50/50 px-4 py-2 rounded-xl hover:bg-white hover:shadow-md transition-all duration-300">
    <div class="bg-indigo-600 p-1.5 rounded-lg shadow-indigo-100 shadow-lg group-hover:scale-110 transition-transform">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
    </div>
    <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600">Institution Wide Timetable</span>
  </a>

  <style>
    /* Availability styles (scoped) */
    #lecturer-availability .legend { width: 10px; height: 10px; border-radius: 9999px; display: inline-block; }
    #lecturer-availability .legend-free { background: #ecfdf5; border: 1px solid #10b98155; }
    #lecturer-availability .legend-busy { background: #fef2f2; border: 1px solid #ef444455; }
    #lecturer-availability .legend-auto { background: #e5e7eb; border: 1px solid #9ca3af55; }

    #lecturer-availability .avail-cell { display: inline-flex; align-items: center; gap: 4px; padding: 4px 6px; border-radius: 6px; border: 1px solid rgba(0,0,0,0.08); cursor: pointer; transition: background .15s, border-color .15s, box-shadow .15s; user-select: none; }
    #lecturer-availability .avail-cell.is-free { background: #ecfdf5; border-color: #10b98155; color: #065f46; }
    #lecturer-availability .avail-cell.is-busy { background: #fef2f2; border-color: #ef444455; color: #7f1d1d; }
    #lecturer-availability .avail-cell.is-auto-busy { background: #e5e7eb; border-color: #9ca3af55; color: #374151; cursor: not-allowed; opacity: 0.8; }
    #lecturer-availability .avail-cell:hover { box-shadow: 0 1px 0 rgba(0,0,0,0.04); }
    #lecturer-availability .avail-label { font-size: 10px; font-weight: 700; }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const csrf = '{{ csrf_token() }}';
      
      // Update live time on dashboard
      function updateDashboardLiveTime() {
        const liveTimeElement = document.getElementById('dashboard-live-time');
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
      updateDashboardLiveTime();
      setInterval(updateDashboardLiveTime, 1000);
      
      // Update time slot in real-time
      function updateDashboardTimeSlot() {
        const now = new Date();
        const hours = now.getHours();
        
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
        
        const slotElement = document.getElementById('dashboard-time-slot');
        if (slotElement) {
          slotElement.textContent = currentSlot;
        }
      }
      
      // Update time slot immediately and every second
      updateDashboardTimeSlot();
      setInterval(updateDashboardTimeSlot, 1000);
      
      // Update weekend indicator
      function updateDashboardWeekendIndicator() {
        const now = new Date();
        const dayOfWeek = now.getDay(); // 0=Sunday, 6=Saturday
        const weekendIndicator = document.getElementById('dashboard-weekend-indicator');
        
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
      updateDashboardWeekendIndicator();
      setInterval(updateDashboardWeekendIndicator, 1000);
      
      // AJAX refresh dashboard data every 30 seconds
      let isBookingInProgress = false;

      async function refreshDashboardData() {
        if (isBookingInProgress) return; // Don't refresh if user is interacting with booking prompts

        const refreshPulse = document.getElementById('refresh-pulse');
        const refreshStatus = document.getElementById('refresh-status');
        const roomsContainer = document.getElementById('rooms-preview-container');
        const bookingsContainer = document.getElementById('my-bookings-container');
        const refreshBtn = document.getElementById('manual-refresh-btn');
 
        try {
          if (refreshPulse) refreshPulse.classList.add('bg-blue-600');
          if (refreshStatus) refreshStatus.textContent = 'Refreshing...';
          if (refreshBtn) refreshBtn.disabled = true;
 
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
            
            // Selective updates
            const newRooms = doc.getElementById('rooms-preview-container');
            const newBookings = doc.getElementById('my-bookings-container');
            
            if (newRooms && roomsContainer) {
              roomsContainer.innerHTML = newRooms.innerHTML;
            }
            if (newBookings && bookingsContainer) {
              bookingsContainer.innerHTML = newBookings.innerHTML;
            }
            
            updateDashboardLiveTime();
          }
        } catch (error) {
          console.error('Error refreshing dashboard:', error);
        } finally {
          if (refreshPulse) refreshPulse.classList.remove('bg-blue-600');
          if (refreshStatus) refreshStatus.textContent = 'Auto-refresh every 30s';
          if (refreshBtn) refreshBtn.disabled = false;
        }
      }
 
      setInterval(refreshDashboardData, 30000);
      
      // Availability toggle functionality
      const cells = document.querySelectorAll('#lecturer-availability .avail-cell');
 
      cells.forEach(cell => {
        cell.addEventListener('click', async function () {
          if (this.disabled || this.classList.contains('is-auto-busy')) {
            return;
          }
 
          const day = this.getAttribute('data-day');
          const slot = this.getAttribute('data-slot');
 
          try {
            const res = await fetch('{{ route('lecturer.availability.toggle') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
              },
              body: JSON.stringify({ day, slot }),
            });
 
            if (!res.ok) return;
            const data = await res.json();
            const status = data.status;
 
            this.classList.remove('is-free', 'is-busy', 'is-auto-busy');
            this.removeAttribute('disabled');
 
            if (status === 'available') {
              this.classList.add('is-free');
              this.querySelector('.avail-label').textContent = 'Free';
            } else if (status === 'busy') {
              this.classList.add('is-busy');
              this.querySelector('.avail-label').textContent = 'Busy';
            } else if (status === 'auto_busy') {
              this.classList.add('is-auto-busy');
              this.querySelector('.avail-label').textContent = 'Class';
              this.setAttribute('disabled', 'disabled');
            }
          } catch (e) {
            console.error('Availability update failed', e);
          }
        });
      });
 
      // Chatbot functionality
      const chatbotForm = document.getElementById('chatbot-form');
      const chatbotInput = document.getElementById('chatbot-input');
      const chatbotMessages = document.getElementById('chatbot-messages');
      const chatbotContainer = document.getElementById('chatbot-container');
 
      function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'} message-enter`;
        
        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="max-w-[85%] px-4 py-2.5 chat-bubble-user shadow-md">
                    <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="max-w-[85%] px-4 py-2.5 chat-bubble-bot">
                    <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
                </div>
            `;
        }
        
        chatbotMessages.appendChild(messageDiv);
        chatbotContainer.scrollTo({ top: chatbotContainer.scrollHeight, behavior: 'smooth' });
      }

      chatbotForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const question = chatbotInput.value.trim();
        if (!question) return;
 
        addMessage(question, 'user');
        chatbotInput.value = '';
 
        // Add typing indicator
        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'flex justify-start message-enter typing-indicator';
        typingIndicator.innerHTML = `
            <div class="px-4 py-2.5 chat-bubble-bot flex items-center space-x-1">
                <div class="dot-pulse"></div>
                <div class="dot-pulse"></div>
                <div class="dot-pulse"></div>
            </div>
        `;
        chatbotMessages.appendChild(typingIndicator);
        chatbotContainer.scrollTo({ top: chatbotContainer.scrollHeight, behavior: 'smooth' });
 
        try {
          const formData = new FormData(chatbotForm);
          formData.set('q', question);
 
          const res = await fetch('{{ route('lecturer.chatbot') }}', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
            },
            body: formData,
          });
 
          const data = await res.json();
 
          if (chatbotMessages.contains(typingIndicator)) {
            chatbotMessages.removeChild(typingIndicator);
          }
          addMessage(data.answer || 'Sorry, I could not process your request.', 'bot');
 
        } catch (error) {
          if (chatbotMessages.contains(typingIndicator)) {
            chatbotMessages.removeChild(typingIndicator);
          }
          addMessage('Sorry, something went wrong. Please try again.', 'bot');
          console.error('Chatbot error:', error);
        }
      });

      // Export functions
      window.exportCSV = function() {
        window.location.href = '{{ route("lecturer.export.csv") }}';
      };

      window.exportPDF = function() {
        window.location.href = '{{ route("lecturer.export.pdf") }}';
      };

      // Clear Chat Function
      window.clearLecturerChat = async function() {
        if (!confirm('Are you sure you want to clear your chat history permanently?')) return;
        
        try {
          const res = await fetch('{{ route('lecturer.chatbot.clear') }}', {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
            }
          });
          
          if (res.ok) {
            chatbotMessages.innerHTML = '<div class="text-sm text-gray-500 italic border-l-2 border-blue-500 pl-2">Chat history cleared. Asked me about your schedule, classes, or rooms...</div>';
          } else {
            alert('Failed to clear chat history. Please try again.');
          }
        } catch (error) {
          console.error('Clear chat error:', error);
          alert('Failed to clear chat history. Please check your connection.');
        }
      };

      // Quick room booking function
      window.quickBookRoom = function(roomId, roomName) {
        isBookingInProgress = true;
        
        try {
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
        } finally {
          isBookingInProgress = false;
        }
      };
    });
  </script>
</div>
</x-app-layout>