@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold">Student Dashboard</h1>
      <p class="text-sm text-gray-600">Welcome back, {{ $user->name }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('student.timetable') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">View Timetable</a>
      <a href="{{ route('student.rooms') }}" class="px-3 py-1.5 bg-green-600 text-white rounded">Room Availability</a>
      <a href="{{ route('student.timetable.print') }}" class="px-3 py-1.5 bg-gray-700 text-white rounded">Print Timetable</a>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">📚</div>
      <div class="text-sm text-gray-500">My Courses</div>
      <div class="text-xl font-bold">{{ $courses->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🏫</div>
      <div class="text-sm text-gray-500">Today's Classes</div>
      <div class="text-xl font-bold">{{ $todayEntries->count() }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">📅</div>
      <div class="text-sm text-gray-500">Next Lecture</div>
      @if($nextLecture)
        <div class="text-sm">{{ optional($nextLecture->unit)->code ?? '—' }}</div>
      @else
        <div class="text-sm text-gray-400">None scheduled</div>
      @endif
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <div class="text-2xl">🎓</div>
      <div class="text-sm text-gray-500">Institution</div>
      <div class="text-sm">{{ optional($user->institution)->name ?? 'Not assigned' }}</div>
    </div>
  </div>

  <!-- Today's Classes -->
  <div class="bg-white rounded-xl shadow p-4">
    <h2 class="font-semibold mb-3">Today's Classes</h2>
    @php
      $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
      $timeSlots = [1=>'7:00am-10:00am',2=>'10:00am-1:00pm',3=>'1:00pm-4:00pm',4=>'4:00pm-7:00pm'];
    @endphp
    <div class="text-sm text-gray-600 mb-3">{{ $dayNames[$today] ?? 'Today' }}</div>
    @forelse($todayEntries as $entry)
      <div class="p-3 border rounded mb-2 hover:bg-gray-50">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm text-gray-500">Slot {{ $entry->slot }} ({{ $timeSlots[$entry->slot] ?? 'Unknown' }})</div>
            <div class="font-semibold">{{ optional($entry->unit)->code }} — {{ optional($entry->unit)->name }}</div>
            <div class="text-sm text-gray-600">
              Course: {{ optional($entry->course)->name ?? '—' }} • 
              Room: {{ optional($entry->room)->name ?? 'TBA' }}
              @if($entry->lecturer)
                • Lecturer: {{ $entry->lecturer->name }}
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="text-gray-500 py-4">No classes scheduled for today.</div>
    @endforelse
  </div>

  <!-- Next Lecture -->
  @if($nextLecture)
  <div class="bg-white rounded-xl shadow p-4">
    <h2 class="font-semibold mb-3">Next Lecture</h2>
    <div class="p-3 border rounded bg-blue-50 border-blue-200">
      <div class="font-semibold text-blue-900">{{ optional($nextLecture->unit)->code }} — {{ optional($nextLecture->unit)->name }}</div>
      <div class="text-sm text-gray-700 mt-1">
        {{ $dayNames[$nextLecture->day_of_week] ?? 'Day ' . $nextLecture->day_of_week }}, 
        Slot {{ $nextLecture->slot }} ({{ $timeSlots[$nextLecture->slot] ?? 'Unknown' }})
      </div>
      <div class="text-sm text-gray-600 mt-1">
        Room: {{ optional($nextLecture->room)->name ?? 'TBA' }}
        @if($nextLecture->lecturer)
          • Lecturer: {{ $nextLecture->lecturer->name }}
        @endif
      </div>
    </div>
  </div>
  @endif

  <!-- Chatbot -->
  <div class="bg-white rounded-xl shadow p-4">
    <h2 class="font-semibold mb-3">Ask a Question</h2>
    <form method="POST" action="{{ route('student.chatbot') }}" id="chatbot-form" class="space-y-3">
      @csrf
      <div class="flex gap-2">
        <input type="text" name="q" placeholder="When is my next lecture?" class="flex-1 border rounded px-3 py-2" required>
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">Ask</button>
      </div>
      <div id="chatbot-response" class="hidden p-3 bg-gray-50 rounded border">
        <div class="text-sm font-semibold mb-1">Answer:</div>
        <div id="chatbot-answer" class="text-sm text-gray-700"></div>
      </div>
    </form>
  </div>

  <!-- My Courses -->
  @if($courses->isNotEmpty())
  <div class="bg-white rounded-xl shadow p-4">
    <h2 class="font-semibold mb-3">My Courses</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
      @foreach($courses as $course)
        <div class="p-3 border rounded">
          <div class="font-semibold">{{ $course->name }}</div>
          <div class="text-sm text-gray-600">{{ optional($course->department)->name ?? '—' }}</div>
        </div>
      @endforeach
    </div>
  </div>
  @endif
</div>

<script>
document.getElementById('chatbot-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const responseDiv = document.getElementById('chatbot-response');
  const answerDiv = document.getElementById('chatbot-answer');
  
  responseDiv.classList.add('hidden');
  answerDiv.textContent = 'Loading...';
  
  try {
    const response = await fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      }
    });
    
    const data = await response.json();
    answerDiv.textContent = data.answer || 'No answer received.';
    responseDiv.classList.remove('hidden');
  } catch (error) {
    answerDiv.textContent = 'Error: Could not get response.';
    responseDiv.classList.remove('hidden');
  }
});
</script>
@endsection
