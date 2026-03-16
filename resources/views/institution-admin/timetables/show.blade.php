@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-bold">{{ $timetable->name }}</h1>
      <div class="text-sm text-gray-600">{{ $timetable->academic_year ?? '—' }} • {{ $timetable->semester ?? 'N/A' }}</div>
    </div>
    <div class="space-x-3">
      <a href="{{ route('institution-admin.timetables.index') }}" class="text-indigo-600 hover:underline">← Back to timetables</a>
      <form action="{{ route('institution-admin.timetables.generate-entries', $timetable) }}" method="POST" class="inline">
        @csrf
        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">Generate Entries</button>
      </form>
      @if($timetable->status !== 'published' && $timetable->entries->isNotEmpty())
        <form action="{{ route('institution-admin.timetables.approve-and-publish', $timetable) }}" method="POST" class="inline">
          @csrf
          <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700">
            Approve &amp; Publish
          </button>
        </form>
      @endif
      @if($timetable->status === 'published')
        <form action="{{ route('institution-admin.timetables.toggle-status', $timetable) }}" method="POST" class="inline">
          @csrf
          <button type="submit" class="px-3 py-1.5 bg-gray-600 text-white rounded hover:bg-gray-700">Unpublish</button>
        </form>
      @endif
      <a href="{{ route('institution-admin.timetables.export-pdf', $timetable) }}" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Export PDF
      </a>
      <button onclick="window.print()" class="px-3 py-1.5 bg-gray-600 text-white rounded hover:bg-gray-700 inline-flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        Print
      </button>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
  @endif

  <!-- Status Badge -->
  <div class="mb-4">
    @if($timetable->status === 'draft')
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Draft</span>
    @elseif($timetable->status === 'pending_approval')
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Pending Approval</span>
    @elseif($timetable->status === 'approved')
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Approved</span>
      @if($timetable->approved_at)
        <span class="ml-2 text-sm text-gray-600">Approved on {{ $timetable->approved_at->format('M d, Y') }} by {{ $timetable->approvedBy->name ?? 'Admin' }}</span>
      @endif
    @elseif($timetable->status === 'published')
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Published</span>
      @if($timetable->published_at)
        <span class="ml-2 text-sm text-gray-600">Published on {{ $timetable->published_at->format('M d, Y') }}</span>
      @endif
    @endif
  </div>

  @if($timetable->entries->isEmpty())
    <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded">
      No entries yet. Add lecturer-unit assignments for {{ $timetable->academic_year ?? 'this year' }} / {{ $timetable->semester ?? 'this semester' }}, then click "Generate Entries".
    </div>
  @endif

  <!-- Diagnostic Summary -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Conflict Count Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Active Conflicts</div>
            <div class="text-2xl font-black text-slate-900">
                {{ $conflicts['lecturer_conflicts']->count() + $conflicts['room_conflicts']->count() + $conflicts['availability_violations']->count() }}
            </div>
        </div>
        @if($conflicts['lecturer_conflicts']->count() + $conflicts['room_conflicts']->count() + $conflicts['availability_violations']->count() > 0)
            <button onclick="document.getElementById('conflict-diagnostic-panel').scrollIntoView({behavior: 'smooth'})" class="ml-auto text-rose-600 hover:text-rose-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
        @endif
    </div>

    <!-- Utilization Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </div>
        <div>
            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Utilization</div>
            <div class="text-2xl font-black text-slate-900">{{ $conflicts['statistics']['utilization_percentage'] }}%</div>
        </div>
    </div>

    <!-- Room Status Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
        <div>
            <div class="text-xs font-black text-slate-400 uppercase tracking-widest">Free Rooms</div>
            <div class="text-2xl font-black text-slate-900">{{ \App\Models\Room::where('institution_id', $timetable->institution_id)->count() - $conflicts['statistics']['room_utilization']->count() }}</div>
        </div>
    </div>
  </div>

  <!-- Detailed Diagnostic Panel -->
  <div id="conflict-diagnostic-panel" class="bg-white rounded-2xl shadow-xl border border-rose-100 overflow-hidden mb-8">
      <div class="bg-rose-50 px-6 py-4 border-b border-rose-100 flex justify-between items-center">
          <h3 class="font-black text-rose-700 uppercase tracking-widest text-sm flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
              Conflict Diagnostic Report
          </h3>
          <span class="text-xs text-rose-400 font-bold bg-white px-2 py-1 rounded-full border border-rose-100 uppercase">
              {{ $conflicts['lecturer_conflicts']->count() + $conflicts['room_conflicts']->count() + $conflicts['availability_violations']->count() }} Issues Found
          </span>
      </div>
      <div class="p-0 max-h-[400px] overflow-y-auto">
          @if($conflicts['lecturer_conflicts']->isEmpty() && $conflicts['room_conflicts']->isEmpty() && $conflicts['availability_violations']->isEmpty())
              <div class="p-12 text-center text-slate-400">
                  <svg class="w-12 h-12 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  <p class="font-bold">No critical conflicts detected.</p>
                  <p class="text-sm">This timetable looks clean and ready for publishing!</p>
              </div>
          @else
              <table class="min-w-full divide-y divide-slate-100">
                  <thead class="bg-slate-50 sticky top-0 z-10">
                      <tr>
                          <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                          <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Description</th>
                          <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Slot</th>
                          <th class="px-6 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                      </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50">
                      @foreach($conflicts['lecturer_conflicts'] as $conflict)
                          <tr class="hover:bg-rose-50/30 transition-colors">
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <span class="px-2 py-0.5 rounded text-[8px] font-black bg-rose-100 text-rose-700 uppercase">Lecturer Double-Book</span>
                              </td>
                              <td class="px-6 py-4">
                                  <div class="text-sm font-bold text-slate-900">{{ $conflict['lecturer_name'] }}</div>
                                  <div class="text-[10px] text-slate-500 font-medium">
                                      @if($conflict['subtype'] == 'external')
                                          Conflict with <b>published</b> entries in other timetables.
                                      @else
                                          Multiple entries in this timetable.
                                      @endif
                                  </div>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600 font-bold">
                                  {{ ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'][$conflict['day']] }} • {{ [1=>'Slot 1', 2=>'Slot 2', 3=>'Slot 3', 4=>'Slot 4'][$conflict['slot']] }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <button onclick="openResolveModal('{{ $conflict['entries']->first()->id }}')" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">Resolve</button>
                              </td>
                          </tr>
                      @endforeach

                      @foreach($conflicts['room_conflicts'] as $conflict)
                          <tr class="hover:bg-rose-50/30 transition-colors">
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <span class="px-2 py-0.5 rounded text-[8px] font-black bg-amber-100 text-amber-700 uppercase">Room Collission</span>
                              </td>
                              <td class="px-6 py-4">
                                  <div class="text-sm font-bold text-slate-900">{{ $conflict['room_name'] }}</div>
                                  <div class="text-[10px] text-slate-500 font-medium">Occupied by multiple units at the same time.</div>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600 font-bold">
                                  {{ ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'][$conflict['day']] }} • {{ [1=>'Slot 1', 2=>'Slot 2', 3=>'Slot 3', 4=>'Slot 4'][$conflict['slot']] }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <button onclick="openResolveModal('{{ $conflict['entries']->first()->id }}')" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">Resolve</button>
                              </td>
                          </tr>
                      @endforeach

                      @foreach($conflicts['availability_violations'] as $v)
                          <tr class="hover:bg-orange-50/30 transition-colors">
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <span class="px-2 py-0.5 rounded text-[8px] font-black bg-orange-100 text-orange-700 uppercase">Assigned Unavailable</span>
                              </td>
                              <td class="px-6 py-4">
                                  <div class="text-sm font-bold text-slate-900">{{ $v['lecturer_name'] }}</div>
                                  <div class="text-[10px] text-slate-500 font-medium font-bold italic">Status marked as: {{ strtoupper($v['status']) }}</div>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600 font-bold">
                                  {{ ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'][$v['day']] }} • {{ [1=>'Slot 1', 2=>'Slot 2', 3=>'Slot 3', 4=>'Slot 4'][$v['slot']] }}
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                  <button onclick="openResolveModal('{{ $v['entry']->id }}')" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">Reschedule</button>
                              </td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
          @endif
      </div>
  </div>

  <!-- View Toggle -->
  <div class="mb-6 flex justify-center">
    <div class="bg-gray-100 rounded-lg p-1 inline-flex">
      <button id="standard-view" onclick="toggleView('standard')" class="px-4 py-2 rounded-md text-sm font-medium transition-colors text-gray-500 hover:text-gray-900">
        Standard View
      </button>
      <button id="grouped-view" onclick="toggleView('grouped')" class="px-4 py-2 rounded-md text-sm font-medium transition-colors bg-white text-gray-900 shadow-sm">
        Course & Year Grouped View
      </button>
    </div>
  </div>

  <!-- Timetable Views -->
  <div id="standard-timetable" style="display: none;">
    @foreach($programChunks as $chunkIndex => $chunkData)
        @php
            $chunkPrograms = $chunkData['programs'];
            $currentCourse = $chunkData['course'];
        @endphp
        <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-slate-200 mb-8 mt-4">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="font-black text-slate-700 uppercase tracking-widest text-sm flex items-center gap-2">
                    <span class="bg-indigo-600 text-white px-2 py-1 rounded-md flex items-center justify-center text-[10px]">{{ strtoupper($currentCourse) }}</span>
                    Matrix View (Chunk {{ $chunkIndex + 1 }})
                </h3>
                <span class="text-xs text-slate-400 font-medium">Side-by-side comparison of {{ count($chunkPrograms) }} programs</span>
            </div>
            
            <div class="p-0 overflow-x-auto overflow-y-auto max-h-[750px] relative custom-scrollbar">
                <table class="min-w-full border-separate border-spacing-0 border border-slate-400">
                    <thead>
                        <tr>
                            <th class="sticky top-0 left-0 z-50 bg-slate-200 border-r border-b border-slate-400 p-2 text-[10px] font-black text-slate-700 uppercase tracking-widest text-center min-w-[60px]">
                                DAY
                            </th>
                            <th class="sticky top-0 left-[60px] z-50 bg-slate-200 border-r border-b border-slate-400 p-2 text-[10px] font-black text-slate-700 uppercase tracking-widest text-center min-w-[90px]">
                                TIME
                            </th>
                            
                            @foreach($chunkPrograms as $key => $program)
                                <th class="bg-indigo-600 text-white border-r border-b border-indigo-500 p-2 text-[10px] font-bold uppercase tracking-wider text-center min-w-[130px]">
                                    <div class="truncate">{{ $program['course'] }}</div>
                                    <div class="text-[9px] text-indigo-200 font-medium mt-0.5">
                                        ({{ str_ireplace('Year ', 'Y', $program['year']) }})
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $dayNum => $dayName)
                            @if(!$loop->first)
                                <tr class="bg-slate-700 h-1.5 ">
                                    <td colspan="{{ 2 + count($chunkPrograms) }}"></td>
                                </tr>
                            @endif
                            @foreach($slots as $slotNum => $slotTime)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    @if($loop->first)
                                        <td rowspan="{{ count($slots) }}" class="sticky left-0 z-40 bg-white border-r border-b border-slate-400 p-2 text-center align-middle">
                                            <div class="[writing-mode:vertical-lr] [transform:rotate(180deg)] text-indigo-600 font-black text-sm uppercase tracking-tighter">
                                                {{ $dayName }}
                                            </div>
                                        </td>
                                    @endif
                                    
                                    <td class="sticky left-[60px] z-30 bg-slate-50 border-r-2 border-b border-slate-400 p-1.5 text-center align-middle">
                                        <div class="text-[10px] font-bold text-slate-600 whitespace-nowrap">
                                            {{ $slotTime }}
                                        </div>
                                    </td>
                                    
                                    @foreach($chunkPrograms as $key => $program)
                                        @php
                                            $entry = $matrix[$dayNum][$slotNum][$key] ?? null;
                                            $hasLecConflict = $entry && $conflicts['lecturer_conflicts']->contains(fn($c) => $c['lecturer_id'] == $entry->lecturer_id && $c['day'] == $dayNum && $c['slot'] == $slotNum);
                                            $hasRoomConflict = $entry && $conflicts['room_conflicts']->contains(fn($c) => $c['room_id'] == $entry->room_id && $c['day'] == $dayNum && $c['slot'] == $slotNum);
                                            $hasViolation = $entry && $conflicts['availability_violations']->contains(fn($c) => $c['lecturer_id'] == $entry->lecturer_id && $c['day'] == $dayNum && $c['slot'] == $slotNum);
                                            
                                            $borderClass = 'border-slate-300';
                                            $bgClass = '';
                                            if ($hasLecConflict) { $borderClass = 'border-rose-500 shadow-[inset_0_0_10px_rgba(244,63,94,0.1)]'; $bgClass = 'bg-rose-50/30'; }
                                            elseif ($hasRoomConflict) { $borderClass = 'border-amber-500 shadow-[inset_0_0_10px_rgba(245,158,11,0.1)]'; $bgClass = 'bg-amber-50/30'; }
                                            elseif ($hasViolation) { $borderClass = 'border-orange-400'; $bgClass = 'bg-orange-50/30'; }
                                        @endphp
                                        <td class="border-r border-b {{ $borderClass }} {{ $bgClass }} p-1.5 text-center align-top min-h-[60px] relative group">
                                            @if($entry)
                                                @if($hasLecConflict || $hasRoomConflict || $hasViolation)
                                                    <div class="absolute top-0.5 right-0.5">
                                                        <svg class="w-3 h-3 text-rose-500 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="flex flex-col gap-0.5 h-full justify-center">
                                                    <div class="font-black text-slate-900 text-[10px] leading-tight mb-0.5">
                                                        {{ $entry->unit->code ?? '—' }}
                                                    </div>
                                                    <div class="text-[9px] text-slate-500 font-medium leading-[1.1]">
                                                        {{ $entry->lecturer->name ?? '—' }}
                                                    </div>
                                                    <div class="mt-1">
                                                        <span class="bg-emerald-50 text-emerald-700 text-[8px] font-black px-1.5 py-0.2 rounded border border-emerald-100 uppercase tracking-tighter">
                                                            {{ $entry->room->name ?? 'TBA' }}
                                                        </span>
                                                    </div>
                                                    
                                                    @if($hasLecConflict || $hasRoomConflict || $hasViolation)
                                                        <button onclick="openResolveModal('{{ $entry->id }}')" class="mt-1.5 opacity-0 group-hover:opacity-100 transition-opacity bg-indigo-600 text-white text-[8px] font-black px-2 py-0.5 rounded uppercase tracking-tighter shadow-sm">
                                                            Fix
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="flex items-center justify-center p-2 opacity-5">
                                                    <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
  </div>
  
  <div id="grouped-timetable">
    <x-timetable-grid-grouped :timetable="$timetable" />
  </div>
</div>

  <!-- Smart Resolve Modal -->
  <div id="resolve-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeResolveModal()"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
              <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                  <h3 class="text-xs font-black text-white uppercase tracking-widest" id="modal-title">Smart Reschedule</h3>
                  <button onclick="closeResolveModal()" class="text-indigo-200 hover:text-white transition-colors">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                  </button>
              </div>
              <div class="bg-white px-6 pt-5 pb-4">
                  <div id="modal-entry-info" class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
                      <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Entry</div>
                      <div id="modal-unit-code" class="text-lg font-black text-slate-900">CS 101</div>
                      <div id="modal-lecturer-room" class="text-xs text-slate-500 font-bold">Dr. Smith • Room 402</div>
                  </div>

                  <div id="suggestions-container">
                      <div class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                          Recommended Free Slots
                      </div>
                      <div id="suggestions-list" class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                          <!-- Suggestions will be injected here -->
                          <div class="animate-pulse flex flex-col gap-2">
                              <div class="h-10 bg-slate-100 rounded-lg"></div>
                              <div class="h-10 bg-slate-100 rounded-lg"></div>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
                  <button onclick="closeResolveModal()" class="px-4 py-2 text-xs font-black text-slate-500 uppercase tracking-widest hover:text-slate-700">Cancel</button>
              </div>
          </div>
      </div>
  </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        height: 10px;
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 5px;
        border: 2px solid #f8fafc;
    }
    
    table { border-spacing: 0; border-collapse: separate; }
    
    .sticky.left-\[60px\]::after {
        content: '';
        position: absolute;
        top: 0;
        right: -8px;
        bottom: 0;
        width: 8px;
        pointer-events: none;
        background: linear-gradient(to right, rgba(0,0,0,0.03), transparent);
    }
</style>
@endsection

<script>
let currentEntryId = null;

function toggleView(viewType) {
    const standardView = document.getElementById('standard-view');
    const groupedView = document.getElementById('grouped-view');
    const standardTimetable = document.getElementById('standard-timetable');
    const groupedTimetable = document.getElementById('grouped-timetable');
    
    if (viewType === 'standard') {
        standardView.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
        standardView.classList.remove('text-gray-500');
        groupedView.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        groupedView.classList.add('text-gray-500');
        
        standardTimetable.style.display = 'block';
        groupedTimetable.style.display = 'none';
    } else {
        groupedView.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
        groupedView.classList.remove('text-gray-500');
        standardView.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        standardView.classList.add('text-gray-500');
        
        groupedTimetable.style.display = 'block';
        standardTimetable.style.display = 'none';
    }
}

function openResolveModal(entryId) {
    currentEntryId = entryId;
    const modal = document.getElementById('resolve-modal');
    modal.classList.remove('hidden');
    
    // Fetch suggestions
    const list = document.getElementById('suggestions-list');
    list.innerHTML = `
        <div class="animate-pulse flex flex-col gap-2">
            <div class="h-10 bg-slate-100 rounded-lg"></div>
            <div class="h-10 bg-slate-100 rounded-lg"></div>
            <div class="h-10 bg-slate-100 rounded-lg"></div>
        </div>
    `;

    fetch(`/institution-admin/timetable-entries/${entryId}/suggestions`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modal-unit-code').textContent = data.entry.unit_code;
            document.getElementById('modal-lecturer-room').textContent = `${data.entry.lecturer} • ${data.entry.room}`;
            
            if (data.suggestions.length === 0) {
                list.innerHTML = '<p class="text-xs text-slate-500 italic p-4 text-center">No free slots found. You may need to manualy override or check room/lecturer availability.</p>';
                return;
            }

            list.innerHTML = data.suggestions.map(s => `
                <button onclick="resolveConflict(${s.day}, ${s.slot})" class="w-full p-3 bg-white border border-slate-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all flex items-center justify-between group">
                    <div class="text-left">
                        <div class="text-xs font-black text-slate-900 uppercase tracking-tighter">${s.day_name}</div>
                        <div class="text-[10px] text-slate-500 font-bold">${s.time}</div>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            `).join('');
        });
}

function closeResolveModal() {
    document.getElementById('resolve-modal').classList.add('hidden');
    currentEntryId = null;
}

function resolveConflict(day, slot) {
    if (!currentEntryId) return;

    fetch(`/institution-admin/timetable-entries/${currentEntryId}/resolve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ day, slot })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload(); // Reload to refresh matrix and diagnostics
        }
    });
}
</script>
