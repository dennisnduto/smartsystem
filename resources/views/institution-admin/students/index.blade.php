@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold">Student Management</h1>
      <p class="text-sm text-gray-600">{{ $institution->name }}</p>
    </div>
    <a href="{{ route('institution-admin.dashboard') }}" class="px-3 py-1.5 bg-gray-600 text-white rounded">← Dashboard</a>
  </div>

  @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
  @endif

  @if($pendingCount > 0)
    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold text-yellow-900">Pending Approvals</div>
          <div class="text-sm text-yellow-700">You have {{ $pendingCount }} student{{ $pendingCount > 1 ? 's' : '' }} waiting for approval.</div>
        </div>
      </div>
    </div>
  @endif

  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">All Students</h3>
    </div>

    @if($students->count() > 0)
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courses</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($students as $student)
              <tr class="{{ !$student->is_approved ? 'bg-yellow-50' : '' }}">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ $student->name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ $student->email }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-500">
                    @if($student->courses->count() > 0)
                      {{ $student->courses->pluck('name')->implode(', ') }}
                    @else
                      <span class="text-gray-400">No courses assigned</span>
                    @endif
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if($student->school_id_path)
                    <a href="{{ Storage::url($student->school_id_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm">
                      <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      View ID
                    </a>
                  @else
                    <span class="text-gray-400 text-sm">Not uploaded</span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if($student->is_approved)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Approved
                    </span>
                  @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                      Pending Approval
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $student->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    @if(!$student->is_approved)
                      <form action="{{ route('institution-admin.students.approve', $student) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                      </form>
                    @else
                      <form action="{{ route('institution-admin.students.reject', $student) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">Revoke</button>
                      </form>
                    @endif
                    <form action="{{ route('institution-admin.students.destroy', $student) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this student?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="px-6 py-4 border-t border-gray-200">
        {{ $students->links() }}
      </div>
    @else
      <div class="px-6 py-12 text-center">
        <div class="text-6xl mb-4">🎓</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
        <p class="text-gray-500">No students have registered for your institution yet.</p>
      </div>
    @endif
  </div>
</div>
@endsection
