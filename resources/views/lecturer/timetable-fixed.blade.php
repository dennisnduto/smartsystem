@extends('layouts.app')

@section('content')
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

  <!-- Week Grid -->
  <div class="bg-white rounded-xl shadow p-4">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h2 class="font-semibold">Week at a glance</h2>
        <div class="text-xs text-gray-500">Time blocks and classes by day</div>
      </div>
      <div class="flex gap-2">
        <button onclick="exportCSV()" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">Export CSV</button>
        <button onclick="exportPDF()" class="px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700 transition">Export PDF</button>
      </div>
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
                      <div class="text-[12px] text-gray-700">{{ optional($e->unit)->name }}</div>
                      <div class="text-[12px] text-gray-700">{{ optional($e->course)->name }}</div>
                      <div class="text-[12px] text-gray-600">Year {{ $e->year_of_study ?? '—' }}</div>
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

</div>

<style>
    /* Availability styles (scoped) */
    #lecturer-availability .legend { width: 10px; height: 10px; border-radius: 9999px; display: inline-block; }
    #lecturer-availability .legend-free { background: #ecfdf5; border: 1px solid #10b98155; }
    #lecturer-availability .legend-busy { background: #fef2f2; border: 1px solid #ef444455; }
    #lecturer-availability .legend-auto { background: #e5e7eb; border: 1px solid #9ca3af55; }

    #lecturer-availability .avail-cell { display: inline-flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.08); cursor: pointer; transition: background .15s, border-color .15s, box-shadow .15s; user-select: none; }
    #lecturer-availability .avail-cell.is-free { background: #ecfdf5; border-color: #10b98155; color: #065f46; }
    #lecturer-availability .avail-cell.is-busy { background: #fef2f2; border-color: #ef444455; color: #7f1d1d; }
    #lecturer-availability .avail-cell.is-auto-busy { background: #e5e7eb; border-color: #9ca3af55; color: #374151; cursor: not-allowed; opacity: 0.8; }
    #lecturer-availability .avail-cell:hover { box-shadow: 0 1px 0 rgba(0,0,0,0.04); }
    #lecturer-availability .avail-label { font-size: 11px; font-weight: 700; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
      const csrf = '{{ csrf_token() }}';
      
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

      // Export functions
      window.exportCSV = function() {
        window.location.href = '{{ route("lecturer.export.csv") }}';
      };

      window.exportPDF = function() {
        window.location.href = '{{ route("lecturer.export.pdf") }}';
      };
    });
  </script>
@endsection
