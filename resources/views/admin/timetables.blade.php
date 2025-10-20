@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">
  <h1 class="text-2xl font-semibold">{{ $institution->name }} — Timetables</h1>
  <div class="bg-white shadow rounded">
    <table class="min-w-full">
      <thead>
        <tr class="text-left border-b">
          <th class="p-3">ID</th>
          <th class="p-3">Name</th>
          <th class="p-3">Department</th>
          <th class="p-3">Created</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($timetables as $tt)
        <tr class="border-b">
          <td class="p-3">{{ $tt->id }}</td>
          <td class="p-3">{{ $tt->name }}</td>
          <td class="p-3">{{ $tt->department->name ?? '—' }}</td>
          <td class="p-3">{{ $tt->created_at->diffForHumans() }}</td>
          <td class="p-3">
            <a href="{{ route('timetables.show', $tt) }}" class="text-indigo-600 hover:underline">Open</a>
          </td>
        </tr>
        @empty
        <tr><td class="p-3" colspan="5">No timetables yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>
    {{ $timetables->links() }}
  </div>
</div>
@endsection
