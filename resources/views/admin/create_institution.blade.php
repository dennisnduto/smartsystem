@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 space-y-6">
  <h1 class="text-2xl font-semibold">Create Institution</h1>

  @if ($errors->any())
    <div class="bg-red-50 text-red-700 p-3 rounded">
      <ul class="list-disc ml-6">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.institutions.store') }}" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium mb-1">Institution name</label>
      <input name="institution_name" value="{{ old('institution_name') }}" class="border rounded w-full p-2" required />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Admin name</label>
        <input name="admin_name" value="{{ old('admin_name') }}" class="border rounded w-full p-2" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Admin email</label>
        <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="border rounded w-full p-2" required />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Admin password (optional)</label>
      <input type="text" name="admin_password" value="{{ old('admin_password') }}" placeholder="Leave blank to auto-generate" class="border rounded w-full p-2" />
    </div>

    <div class="flex gap-3">
      <a href="{{ route('admin.index') }}" class="px-4 py-2 rounded border">Cancel</a>
      <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white">Create</button>
    </div>
  </form>
</div>
@endsection
