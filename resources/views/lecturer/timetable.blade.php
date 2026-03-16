<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Timetable') }}
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
      <h1 class="text-2xl font-bold">My Timetable</h1>
      <p class="text-sm text-gray-600">Your scheduled classes for the week</p>
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
            <th class="text-left p-1.5 font-semibold text-gray-600 border-b border-r text-xs">Time</th>
            @foreach($dayNames as $dn)
              <th class="text-left p-1.5 font-semibold text-gray-600 border-b border-r text-xs">{{ $dn }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($timeSlots as $slotNum => $label)
            <tr class="align-top">
              <td class="p-1.5 font-medium text-gray-700 border-t border-r bg-gray-50 text-[11px]">{{ $label }}</td>
              @foreach($dayNames as $dayNum => $dn)
                @php $cell = ($entriesByDay[$dayNum] ?? collect())->filter(fn($e) => (int)$e->slot === $slotNum); @endphp
                <td class="p-1 border-t border-r">
                  @forelse($cell as $e)
                    <div class="border rounded p-1 mb-1 bg-blue-50 border-blue-200">
                      <div class="font-semibold text-blue-900 text-[11px] leading-tight">{{ optional($e->unit)->code }}</div>
                      <div class="text-[10px] text-gray-700 leading-tight truncate" title="{{ optional($e->unit)->name }}">{{ optional($e->unit)->name }}</div>
                      <div class="text-[10px] text-gray-600 leading-tight truncate">{{ optional($e->course)->name }}</div>
                      <div class="text-[9px] text-gray-600">Yr {{ str_replace('Year', '', $e->year_of_study ?? '—') }}</div>
                      <div class="text-[10px] text-green-700 mt-0.5"><span class="px-1.5 py-0 bg-green-100 rounded-full font-semibold">{{ optional($e->room)->name ?? 'TBA' }}</span></div>
                    </div>
                  @empty
                    <div class="text-[10px] text-gray-300 text-center">—</div>
                  @endforelse
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
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

      // Export functions
      window.exportCSV = function() {
        window.location.href = '{{ route("lecturer.export.csv") }}';
      };

      window.exportPDF = function() {
        window.location.href = '{{ route("lecturer.export.pdf") }}';
      };
    });
  </script>
</div>
</x-app-layout>