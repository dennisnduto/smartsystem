@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Institutions</h1>
    <a href="{{ route('admin.institutions.create') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">Add Institution</a>
  </div>

  @if (session('created_institution'))
    <div class="bg-green-50 text-green-800 p-3 rounded">
      <div><strong>Institution created:</strong> {{ session('created_institution') }}</div>
      <div><strong>Admin email:</strong> {{ session('created_admin_email') }}</div>
      <div><strong>Admin password (copy now):</strong> {{ session('created_admin_password') }}</div>
    </div>
  @endif

  <div class="bg-white shadow rounded">
    <table class="min-w-full">
      <thead>
        <tr class="text-left border-b">
          <th class="p-3">Name</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($institutions as $inst)
        <tr class="border-b">
          <td class="p-3">{{ $inst->name }}</td>
          <td class="p-3">
            <a href="{{ route('admin.institution.timetables', $inst) }}" class="text-indigo-600 hover:underline">View Timetables</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
