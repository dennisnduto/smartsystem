@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6">
  <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
    <div class="text-5xl mb-4">⏳</div>
    <h1 class="text-2xl font-bold text-yellow-900 mb-2">Account Pending Approval</h1>
    <p class="text-gray-700 mb-4">
      Thank you for registering, {{ $user->name }}! Your account is currently pending approval by an administrator.
    </p>
    <p class="text-sm text-gray-600 mb-6">
      You will be notified once your account has been approved. Please check back later or contact your institution administrator.
    </p>
    
    @if($user->institution)
      <div class="bg-white rounded-lg p-4 mb-4 text-left">
        <div class="text-sm font-semibold text-gray-700 mb-1">Registration Details:</div>
        <div class="text-sm text-gray-600">Institution: {{ $user->institution->name }}</div>
        <div class="text-sm text-gray-600">Email: {{ $user->email }}</div>
      </div>
    @endif

    <div class="flex gap-3 justify-center">
      <a href="{{ route('logout') }}" 
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
         class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded">
        Logout
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
      </form>
    </div>
  </div>
</div>
@endsection
