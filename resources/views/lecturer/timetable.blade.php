<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lecturer Dashboard') }}
        </h2>
    </x-slot>

<div class="max-w-7xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Lecturer Dashboard</h1>
      <p class="text-sm text-gray-600">Welcome back, {{ $user->name }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('lecturer.assigned') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Assigned</a>
      <a href="{{ route('lecturer.rooms') }}" class="px-3 py-1.5 bg-green-600 text-white rounded">Rooms</a>
      <button onclick="window.print()" class="px-3 py-1.5 bg-gray-700 text-white rounded">Print</button>
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
      <div class="text-xl font-bold">{{ $entries->filter(fn($e) => optional($e->unit)->is_lab_only ?? false)->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🎯</div>
      <div class="text-sm text-gray-500">Upcoming Next</div>
      @php
        $next = $entries->sortBy(['day_of_week','slot'])->first();
      @endphp
      <div class="text-sm">
        @if($next)
            {{ optional($next->unit)->code ?? '—' }} in {{ optional($next->room)->name ?? 'TBA' }}
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
      @php $today = max(1, min(5, (int)now()->dayOfWeekIso)); @endphp
      @php $todayEntries = $entries->where('day_of_week', $today)->sortBy('slot'); @endphp
      @forelse($todayEntries as $e)
        <div class="p-3 border rounded mb-2">
          <div class="text-sm text-gray-500">Slot {{ $e->slot }}</div>
          <div class="font-semibold">{{ optional($e->unit)->code }} — {{ optional($e->course)->name }}</div>
          <div class="text-sm text-gray-600">Room: {{ optional($e->room)->name ?? 'TBA' }}</div>
        </div>
      @empty
        <div class="text-gray-500">No classes today.</div>
      @endforelse
    </div>
    <div id="lecturer-availability" class="bg-white rounded-xl shadow p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">Availability</h2>
        <div class="flex items-center gap-2 text-[11px] text-gray-500">
          <span class="inline-flex items-center gap-1"><span class="legend legend-free"></span> Free</span>
          <span class="inline-flex items-center gap-1"><span class="legend legend-busy"></span> Busy</span>
        </div>
      </div>
      <form method="POST" action="{{ route('lecturer.availability.update') }}">
        @csrf
        <div class="overflow-auto">
          <table class="min-w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
            @php $daysFull = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri']; @endphp
            <thead>
              <tr class="bg-gray-50">
                <th class="p-2 text-left font-semibold text-gray-600 border-b border-r">Slot</th>
                @foreach($daysFull as $d)
                  <th class="p-2 text-left font-semibold text-gray-600 border-b border-r">{{ $d }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @for($slot=1;$slot<=4;$slot++)
                <tr class="border-t">
                  <td class="p-2 font-medium text-gray-700 border-r">{{ ['7-10','10-13','13-16','16-19'][$slot-1] }}</td>
                  @for($day=1;$day<=5;$day++)
                    @php $isFree = ($availability[$day][$slot] ?? false) ? true : false; @endphp
                    <td class="p-2 border-r">
                      <label class="avail-cell {{ $isFree ? 'is-free' : 'is-busy' }}">
                        <input type="checkbox" name="availability[{{ $day }}][{{ $slot }}]" value="1" {{ $isFree ? 'checked' : '' }} class="avail-checkbox">
                        <span class="avail-label">{{ $isFree ? 'Free' : 'Busy' }}</span>
                      </label>
                    </td>
                  @endfor
                </tr>
              @endfor
            </tbody>
          </table>
        </div>
        <div class="mt-3 flex items-center justify-between">
          <div class="text-[10px] text-gray-500">Tip: Click a cell to toggle. Your scheduled classes are auto-marked Busy.</div>
          <button class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Week Grid -->
  <div class="bg-white rounded-xl shadow p-4">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold">Week at a glance</h2>
      <div class="text-xs text-gray-500">Time blocks and classes by day</div>
    </div>
    @php
      $timeSlots = [1=>'7:00-10:00',2=>'10:00-13:00',3=>'13:00-16:00',4=>'16:00-19:00'];
      $dayNames = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'];
    @endphp
    <div class="overflow-auto">
      <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left p-2 font-semibold text-gray-600 border-b border-r">Time</th>
            @foreach($dayNames as $dn)
              <th class="text-left p-2 font-semibold text-gray-600 border-b border-r">{{ $dn }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($timeSlots as $slotNum => $label)
            <tr class="align-top">
              <td class="p-2 font-medium text-gray-700 border-t border-r bg-gray-50">{{ $label }}</td>
              @foreach($dayNames as $dayNum => $dn)
                @php $cell = ($entriesByDay[$dayNum] ?? collect())->filter(fn($e) => (int)$e->slot === $slotNum); @endphp
                <td class="p-2 border-t border-r">
                  @forelse($cell as $e)
                    <div class="border rounded p-2 mb-2 bg-blue-50 border-blue-200">
                      <div class="font-semibold text-blue-900">{{ optional($e->unit)->code }}</div>
                      <div class="text-[12px] text-gray-700">{{ optional($e->course)->name }}</div>
                      <div class="text-[12px] text-green-700"><span class="px-2 py-0.5 bg-green-100 rounded-full font-semibold">{{ optional($e->room)->name ?? 'TBA' }}</span></div>
                    </div>
                  @empty
                    <div class="text-[12px] text-gray-300">—</div>
                  @endforelse
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Chatbot -->
  <div class="bg-white rounded-xl shadow p-4">
    <h2 class="font-semibold mb-3">Ask</h2>
    <form method="POST" action="{{ route('lecturer.chatbot') }}" class="flex gap-2">
      @csrf
      <input type="text" name="q" placeholder="When is my next class?" class="flex-1 border rounded px-3 py-2">
      <button class="px-3 py-2 bg-gray-800 text-white rounded">Ask</button>
    </form>
  </div>

  <style>
    /* Availability styles (scoped) */
    #lecturer-availability .legend { width: 10px; height: 10px; border-radius: 9999px; display: inline-block; }
    #lecturer-availability .legend-free { background: #ecfdf5; border: 1px solid #10b98155; }
    #lecturer-availability .legend-busy { background: #fef2f2; border: 1px solid #ef444455; }

    #lecturer-availability .avail-cell { display: inline-flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.08); cursor: pointer; transition: background .15s, border-color .15s, box-shadow .15s; user-select: none; }
    #lecturer-availability .avail-cell.is-free { background: #ecfdf5; border-color: #10b98155; color: #065f46; }
    #lecturer-availability .avail-cell.is-busy { background: #fef2f2; border-color: #ef444455; color: #7f1d1d; }
    #lecturer-availability .avail-cell:hover { box-shadow: 0 1px 0 rgba(0,0,0,0.04); }
    #lecturer-availability .avail-checkbox { height: 16px; width: 16px; accent-color: #10b981; }
    #lecturer-availability .avail-label { font-size: 11px; font-weight: 700; }
  </style>
</div>
</x-app-layout>
