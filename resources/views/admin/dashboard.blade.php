@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-8">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Super Admin Dashboard</h1>
    <a href="{{ route('admin.institutions.create') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">Add Institution</a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">Institutions</div><div class="text-2xl font-bold">{{ $stats['institutions'] }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">Institution Admins</div><div class="text-2xl font-bold">{{ $stats['admins'] }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">Lecturers</div><div class="text-2xl font-bold">{{ $stats['lecturers'] }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">Students</div><div class="text-2xl font-bold">{{ $stats['students'] }}</div></div>
    <div class="bg-white p-4 rounded shadow"><div class="text-sm text-gray-500">Timetables</div><div class="text-2xl font-bold">{{ $stats['timetables'] }}</div></div>
  </div>

  @if (session('created_institution'))
  <div class="bg-green-50 text-green-800 p-3 rounded">
    <div><strong>Institution created:</strong> {{ session('created_institution') }}</div>
    <div><strong>Admin email:</strong> {{ session('created_admin_email') }}</div>
    <div><strong>Admin password (copy now):</strong> {{ session('created_admin_password') }}</div>
  </div>
  @endif

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow">
      <div class="p-4 border-b font-medium">Recent Institutions</div>
      <ul>
        @forelse($recentInstitutions as $inst)
        <li class="p-4 border-b flex items-center justify-between">
          <span>{{ $inst->name }}</span>
          <div class="flex gap-3">
            <a class="text-indigo-600 hover:underline" href="{{ route('admin.institution.timetables', $inst) }}">View</a>
            <a class="text-slate-700 hover:underline" href="{{ route('admin.institutions.edit', $inst) }}">Edit</a>
            <form method="POST" action="{{ route('admin.institutions.destroy', $inst) }}" onsubmit="return confirm('Delete institution {{ $inst->name }}?');">
              @csrf @method('DELETE')
              <button class="text-red-600 hover:underline" type="submit">Delete</button>
            </form>
          </div>
        </li>
        @empty
        <li class="p-4">No institutions yet.</li>
        @endforelse
      </ul>
    </div>

    <div class="bg-white rounded shadow">
      <div class="p-4 border-b font-medium">Recent Timetables</div>
      <ul>
        @forelse($recentTimetables as $tt)
        <li class="p-4 border-b flex items-center justify-between">
          <div>
            <div class="font-medium">{{ $tt->name }}</div>
            <div class="text-sm text-gray-500">{{ $tt->department->name ?? '—' }}</div>
          </div>
          <a class="text-indigo-600 hover:underline" href="{{ route('timetables.show', $tt) }}">Open</a>
        </li>
        @empty
        <li class="p-4">No timetables yet.</li>
        @endforelse
      </ul>
    </div>
  </div>

  <div class="bg-white rounded shadow">
    <div class="p-4 border-b font-medium">All Institutions</div>
    <table class="min-w-full">
      <thead>
        <tr class="text-left border-b">
          <th class="p-3">Name</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($allInstitutions as $inst)
        <tr class="border-b">
          <td class="p-3">{{ $inst->name }}</td>
          <td class="p-3 flex items-center gap-3">
            <a class="text-indigo-600 hover:underline" href="{{ route('admin.institution.timetables', $inst) }}">View</a>
            <a class="text-slate-700 hover:underline" href="{{ route('admin.institutions.edit', $inst) }}">Edit</a>
            <form method="POST" action="{{ route('admin.institutions.destroy', $inst) }}" onsubmit="return confirm('Delete institution {{ $inst->name }}?');">
              @csrf @method('DELETE')
              <button class="text-red-600 hover:underline" type="submit">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="p-4">
      {{ $allInstitutions->links() }}
    </div>
  </div>
</div>
@endsection
