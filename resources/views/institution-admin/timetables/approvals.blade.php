@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold">Timetable Approvals</h1>
      <p class="text-sm text-gray-600">{{ $institution->name }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('institution-admin.timetables.index') }}" class="px-3 py-1.5 bg-gray-600 text-white rounded">← All Timetables</a>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
  @endif

  <!-- Pending Approvals -->
  @if($pendingTimetables->count() > 0)
    <div class="bg-white rounded-xl shadow mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Pending Approval ({{ $pendingTimetables->count() }})</h2>
        <p class="text-sm text-gray-600 mt-1">Review and approve timetables to make them visible to students and lecturers</p>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timetable</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entries</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($pendingTimetables as $timetable)
              <tr class="hover:bg-yellow-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ $timetable->name }}</div>
                  @if($timetable->academic_year)
                    <div class="text-sm text-gray-500">{{ $timetable->academic_year }}</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $timetable->department?->name ?? 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $timetable->semester ?? 'N/A' }}</div>
                  @if($timetable->week_start)
                    <div class="text-sm text-gray-500">Starts {{ $timetable->week_start->format('M d, Y') }}</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $timetable->entries->count() }} entries</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ $timetable->updated_at->format('M d, Y H:i') }}</div>
                  @if($timetable->publishedBy)
                    <div class="text-xs text-gray-400">by {{ $timetable->publishedBy->name }}</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('institution-admin.timetables.show', $timetable) }}" class="text-indigo-600 hover:text-indigo-900">Review</a>
                    <form action="{{ route('institution-admin.timetables.approve', $timetable) }}" method="POST" class="inline">
                      @csrf
                      <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                    </form>
                    <form action="{{ route('institution-admin.timetables.reject', $timetable) }}" method="POST" class="inline">
                      @csrf
                      <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @else
    <div class="bg-white rounded-xl shadow p-6 mb-6 text-center">
      <div class="text-4xl mb-2">✅</div>
      <p class="text-gray-600">No timetables pending approval</p>
    </div>
  @endif

  <!-- Approved Timetables -->
  <div class="bg-white rounded-xl shadow">
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900">Approved & Published Timetables</h2>
      <p class="text-sm text-gray-600 mt-1">These timetables are visible to students and lecturers</p>
    </div>
    @if($approvedTimetables->count() > 0)
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timetable</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($approvedTimetables as $timetable)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ $timetable->name }}</div>
                  @if($timetable->academic_year)
                    <div class="text-sm text-gray-500">{{ $timetable->academic_year }} • {{ $timetable->semester ?? 'N/A' }}</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $timetable->department?->name ?? 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if($timetable->status === 'approved')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Approved</span>
                  @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if($timetable->approved_at)
                    <div class="text-sm text-gray-900">{{ $timetable->approved_at->format('M d, Y') }}</div>
                    @if($timetable->approvedBy)
                      <div class="text-xs text-gray-500">by {{ $timetable->approvedBy->name }}</div>
                    @endif
                  @else
                    <div class="text-sm text-gray-400">N/A</div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <a href="{{ route('institution-admin.timetables.show', $timetable) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="px-6 py-4 border-t border-gray-200">
        {{ $approvedTimetables->links() }}
      </div>
    @else
      <div class="px-6 py-12 text-center">
        <div class="text-4xl mb-2">📅</div>
        <p class="text-gray-600">No approved timetables yet</p>
      </div>
    @endif
  </div>
</div>
@endsection
