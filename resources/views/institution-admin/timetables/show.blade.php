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

  <!-- View Toggle -->
  <div class="mb-6 flex justify-center">
    <div class="bg-gray-100 rounded-lg p-1 inline-flex">
      <button id="standard-view" onclick="toggleView('standard')" class="px-4 py-2 rounded-md text-sm font-medium transition-colors bg-white text-gray-900 shadow-sm">
        Standard View
      </button>
      <button id="grouped-view" onclick="toggleView('grouped')" class="px-4 py-2 rounded-md text-sm font-medium transition-colors text-gray-500 hover:text-gray-900">
        Course Grouped View
      </button>
    </div>
  </div>

  <!-- Timetable Views -->
  <div id="standard-timetable">
    <x-timetable-grid :timetable="$timetable" />
  </div>
  
  <div id="grouped-timetable" style="display: none;">
    <x-timetable-grid-grouped :timetable="$timetable" />
  </div>
</div>
@endsection

<script>
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
</script>
