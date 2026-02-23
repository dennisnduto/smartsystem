@extends('layouts.app')

@section('content')
<!-- Header Section -->
<header class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">{{ $institution->name }}</h1>
                <p class="text-blue-100 mt-1">Institution Admin Dashboard</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.analytics') }}" class="bg-blue-500 hover:bg-blue-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    📊 Analytics
                </a>
                <a href="{{ route('institution-admin.reports') }}" class="bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    📋 Reports
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border-l-4 border-indigo-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <span class="text-2xl">🏫</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Schools</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_schools'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border-l-4 border-blue-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-2xl">🏢</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Departments</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_departments'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border-l-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-2xl">👨‍🏫</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Lecturers</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_lecturers'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border-l-4 border-yellow-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <span class="text-2xl">🎓</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Students</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_students'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border-l-4 border-purple-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-2xl">📦</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Units</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_units'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl border-l-4 border-purple-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-2xl">🏠</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Rooms</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_rooms'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Courses</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_courses'] }}</p>
                        </div>
                        <div class="text-3xl">📚</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Timetables</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['active_timetables'] }}</p>
                        </div>
                        <div class="text-3xl">✅</div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Draft Timetables</p>
                            <p class="text-2xl font-bold text-orange-600">{{ $stats['draft_timetables'] }}</p>
                        </div>
                        <div class="text-3xl">📝</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        ⚡ Quick Actions
                    </h3>
                    <div class="space-y-3">
                        <a href="{{ route('institution-admin.schools.index') }}" class="block w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            🏫 Manage Schools
                        </a>
                        <a href="{{ route('institution-admin.departments.index') }}" class="block w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            🏢 Manage Departments
                        </a>
                        <a href="{{ route('institution-admin.lecturers.index') }}" class="block w-full bg-green-50 hover:bg-green-100 text-green-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            👨‍🏫 Manage Lecturers
                        </a>
                        <a href="{{ route('institution-admin.courses.index') }}" class="block w-full bg-yellow-50 hover:bg-yellow-100 text-yellow-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            📚 Manage Courses
                        </a>
                        <a href="{{ route('institution-admin.units.index') }}" class="block w-full bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            📦 Manage Units
                        </a>
                        <a href="{{ route('institution-admin.rooms.index') }}" class="block w-full bg-purple-50 hover:bg-purple-100 text-purple-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            🏠 Manage Rooms
                        </a>
                        <a href="{{ route('institution-admin.students.index') }}" class="block w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            🎓 Manage Students
                        </a>
                        <a href="{{ route('institution-admin.timetables.index') }}" class="block w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-3 px-4 rounded-lg transition text-center">
                            📅 View Timetables
                        </a>
                    </div>
                </div>
            </div>

            <!-- Department Overview -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                        🏢 Departments Overview
                        <a href="{{ route('institution-admin.departments.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                    </h3>
                    <div class="space-y-3">
                        @forelse($departments as $department)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $department->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $department->courses_count }} courses • {{ $department->rooms_count }} rooms
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Active
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500">No departments found.</p>
                                <a href="{{ route('institution-admin.departments.create') }}" class="text-blue-600 hover:text-blue-800 text-sm">Create your first department</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Room Availability -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                        🏠 Room Availability
                        <button onclick="checkRoomAvailability()" class="text-sm text-green-600 hover:text-green-800">Refresh</button>
                    </h3>
                    <div class="space-y-3" id="room-availability">
                        @forelse($available_rooms as $room)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $room->name ?? 'Room ' . $room->id }}</p>
                                    <p class="text-sm text-gray-500">{{ $room->department->name ?? 'Department' }}</p>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Available
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500">No rooms available or no rooms created.</p>
                                <a href="{{ route('institution-admin.rooms.create') }}" class="text-blue-600 hover:text-blue-800 text-sm">Add rooms</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & AI Chatbot -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            
            <!-- Recent Timetables -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                        📅 Recent Timetables
                        <a href="{{ route('institution-admin.timetables.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                    </h3>
                    <div class="space-y-3">
                        @forelse($recent_timetables as $timetable)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $timetable->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        Institution-wide timetable
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $timetable->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($timetable->status) }}
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">{{ $timetable->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500">No timetables created yet.</p>
                                <button onclick="openGenerateTimetableModal()" class="text-blue-600 hover:text-blue-800 text-sm">Generate your first timetable</button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- AI Assistant Chatbot -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-between">
                        🤖 AI Assistant
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                Online
                            </span>
                            <button onclick="clearInstitutionChat()" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </h3>
                    
                    <div id="institution-chat-container" class="h-64 overflow-y-auto bg-gray-50 rounded-lg p-4 mb-4 border">
                        <div class="space-y-3" id="institution-chat-messages">
                            <div class="flex justify-start">
                                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-blue-500 text-white shadow-sm">
                                    <p class="text-sm">👋 Hello! I'm your institution AI assistant. I can help you with:</p>
                                    <ul class="text-sm mt-2 space-y-1">
                                        <li>• Generate new timetables</li>
                                        <li>• Show available rooms</li>
                                        <li>• Department statistics</li>
                                        <li>• Lecturer availability</li>
                                        <li>• Conflict resolution</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form id="institution-chat-form" class="flex space-x-2 mb-4">
                        <input type="text" 
                               id="institution-chat-input" 
                               class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               placeholder="Ask about rooms, timetables, conflicts..."
                               required>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </button>
                    </form>
                    
                    <!-- Quick Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="askInstitutionQuestion('Show available rooms in CS Department')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-xs transition">
                            Available Rooms
                        </button>
                        <button onclick="askInstitutionQuestion('Generate new timetable')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-xs transition">
                            Generate Timetable
                        </button>
                        <button onclick="askInstitutionQuestion('Show conflicts')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-xs transition">
                            Check Conflicts
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users Activity -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">👥 Recent User Activity</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3">Recent Lecturers</h4>
                        <div class="space-y-2">
                            @foreach($recent_users->where('role', 'lecturer')->take(3) as $lecturer)
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-green-600">{{ substr($lecturer->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $lecturer->name }}</p>
                                        <p class="text-xs text-gray-500">Joined {{ $lecturer->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3">Recent Students</h4>
                        <div class="space-y-2">
                            @foreach($recent_users->where('role', 'student')->take(3) as $student)
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">{{ substr($student->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                                        <p class="text-xs text-gray-500">Joined {{ $student->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Timetable Modal -->
<div id="generateTimetableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🚀 Generate New Timetable</h3>
            <form method="POST" action="{{ route('institution-admin.generate-timetable') }}">
                @csrf
                <div class="mb-4">
                    <label for="timetable_name" class="block text-sm font-medium text-gray-700">Timetable Name</label>
                    <input type="text" name="name" id="timetable_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="timetable_academic_year" class="block text-sm font-medium text-gray-700">Academic Year (optional)</label>
                    <select name="academic_year" id="timetable_academic_year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Any</option>
                        <option value="Y1">Year 1</option>
                        <option value="Y2">Year 2</option>
                        <option value="Y3">Year 3</option>
                        <option value="Y4">Year 4</option>
                        <option value="Y5">Year 5</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="timetable_semester" class="block text-sm font-medium text-gray-700">Semester (optional)</label>
                    <select name="semester" id="timetable_semester" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Any</option>
                        <option value="S1">Semester 1</option>
                        <option value="S2">Semester 2</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeGenerateTimetableModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Generate Timetable Modal Functions
function openGenerateTimetableModal() {
    document.getElementById('generateTimetableModal').classList.remove('hidden');
}

function closeGenerateTimetableModal() {
    document.getElementById('generateTimetableModal').classList.add('hidden');
}

// Room Availability Check
function checkRoomAvailability() {
    // Simulate room availability check
    showNotification('Room availability updated!', 'success');
}

// Institution Chat Functions
document.getElementById('institution-chat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const input = document.getElementById('institution-chat-input');
    const messages = document.getElementById('institution-chat-messages');
    const query = input.value.trim();
    
    if (!query) return;
    
    // Add user message
    addInstitutionMessage(query, 'user');
    input.value = '';
    
    // Simulate AI response for institution-specific queries
    setTimeout(() => {
        let response = '';
        const lowerQuery = query.toLowerCase();
        
        if (lowerQuery.includes('room') && lowerQuery.includes('available')) {
            response = "🏠 Available rooms:\n• Room 101 (CS Department)\n• Room 205 (Math Department)\n• Lab 301 (Physics Department)";
        } else if (lowerQuery.includes('generate') && lowerQuery.includes('timetable')) {
            response = "🚀 To generate a timetable:\n1. Click the 'Generate Timetable' button\n2. Select department\n3. Enter timetable name\n4. Configure courses and lecturers";
        } else if (lowerQuery.includes('conflict')) {
            response = "⚠️ Current conflicts:\n• None found in active timetables\n• All lecturers have clear schedules\n• Room assignments are optimized";
        } else if (lowerQuery.includes('department')) {
            response = "🏢 Departments overview:\n• {{ $stats['total_departments'] }} total departments\n• {{ $stats['total_courses'] }} courses across all departments\n• All departments are active";
        } else {
            response = "I can help you with:\n• Room availability checks\n• Timetable generation\n• Conflict resolution\n• Department statistics\n• Lecturer management";
        }
        
        addInstitutionMessage(response, 'bot');
    }, 1000);
});

function addInstitutionMessage(text, sender) {
    const messages = document.getElementById('institution-chat-messages');
    const messageDiv = document.createElement('div');
    
    if (sender === 'user') {
        messageDiv.className = 'flex justify-end';
        messageDiv.innerHTML = `
            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-gray-200 text-gray-800 shadow-sm">
                <p class="text-sm">${text}</p>
            </div>
        `;
    } else {
        messageDiv.className = 'flex justify-start';
        messageDiv.innerHTML = `
            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-blue-500 text-white shadow-sm">
                <p class="text-sm">${text}</p>
            </div>
        `;
    }
    
    messages.appendChild(messageDiv);
    document.getElementById('institution-chat-container').scrollTop = document.getElementById('institution-chat-container').scrollHeight;
}

function askInstitutionQuestion(question) {
    const input = document.getElementById('institution-chat-input');
    input.value = question;
    document.getElementById('institution-chat-form').dispatchEvent(new Event('submit'));
}

function clearInstitutionChat() {
    const messages = document.getElementById('institution-chat-messages');
    messages.innerHTML = `
        <div class="flex justify-start">
            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-blue-500 text-white shadow-sm">
                <p class="text-sm">👋 Hello! I'm your institution AI assistant. I can help you with:</p>
                <ul class="text-sm mt-2 space-y-1">
                    <li>• Generate new timetables</li>
                    <li>• Show available rooms</li>
                    <li>• Department statistics</li>
                    <li>• Lecturer availability</li>
                    <li>• Conflict resolution</li>
                </ul>
            </div>
        </div>
    `;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        document.body.removeChild(notification);
    }, 3000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('generateTimetableModal');
    if (event.target == modal) {
        modal.classList.add('hidden');
    }
}
</script>
@endsection