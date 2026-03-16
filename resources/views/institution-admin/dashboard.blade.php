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
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap');
                
                .glass-admin {
                    background: rgba(255, 255, 255, 0.75);
                    backdrop-filter: blur(12px);
                    -webkit-backdrop-filter: blur(12px);
                    border: 1px solid rgba(255, 255, 255, 0.4);
                }
                
                .chat-bubble-admin-user {
                    background: linear-gradient(135deg, #4f46e5, #4338ca);
                    border-radius: 1.25rem 1.25rem 0.25rem 1.25rem;
                    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.2);
                    color: white;
                }
                
                .chat-bubble-admin-bot {
                    background: white;
                    border-radius: 1.25rem 1.25rem 1.25rem 0.25rem;
                    border: 1px solid rgba(0, 0, 0, 0.05);
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
                    color: #1e293b;
                }
                
                .chat-scroll-admin {
                    scrollbar-width: thin;
                    scrollbar-color: rgba(99, 102, 241, 0.2) transparent;
                }
                
                .chat-scroll-admin::-webkit-scrollbar {
                    width: 4px;
                }
                
                .chat-scroll-admin::-webkit-scrollbar-thumb {
                    background-color: rgba(99, 102, 241, 0.2);
                    border-radius: 20px;
                }

                @keyframes revealUp {
                    from { opacity: 0; transform: translateY(20px) scale(0.98); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }

                .msg-reveal {
                    animation: revealUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                }

                .typing-pulse {
                    width: 5px;
                    height: 5px;
                    background: #6366f1;
                    border-radius: 50%;
                    animation: pulseGrow 1.2s infinite ease-in-out both;
                }

                .typing-pulse:nth-child(2) { animation-delay: 0.15s; }
                .typing-pulse:nth-child(3) { animation-delay: 0.3s; }

                @keyframes pulseGrow {
                    0%, 80%, 100% { transform: scale(0.5); opacity: 0.4; }
                    40% { transform: scale(1.1); opacity: 1; }
                }
            </style>

            <div class="glass-admin overflow-hidden shadow-2xl rounded-[2.5rem] border-0 relative">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-3xl shadow-inner border border-indigo-100">
                                🌌
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-gray-900 tracking-tight" style="font-family: 'Outfit', sans-serif;">Institution Admin Assistant</h3>
                                <div class="flex items-center space-x-2">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    <span class="text-[10px] font-black text-green-600 uppercase tracking-widest">Live Support</span>
                                </div>
                            </div>
                        </div>
                        <button onclick="clearInstitutionChat()" class="p-3 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-2xl transition-all duration-300" title="Purge Conversation">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                    
                    <div id="institution-chat-container" class="h-96 overflow-y-auto chat-scroll-admin px-2 mb-6">
                        <div class="space-y-6" id="institution-chat-messages">
                            <div class="flex justify-start msg-reveal">
                                <div class="max-w-[85%] px-5 py-4 chat-bubble-admin-bot">
                                    <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">
                                        Hello! I'm your institution admin assistant. I can help you analyze **timetable metrics**, check **room availability**, and resolve **scheduling conflicts**. How can I help you today?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form id="institution-chat-form" class="relative mb-6 group">
                        <div class="flex items-center bg-white/50 backdrop-blur-md rounded-[1.5rem] p-2 border border-blue-50 focus-within:ring-4 focus-within:ring-indigo-100 focus-within:border-indigo-300 transition-all duration-500">
                            <input type="text" 
                                   id="institution-chat-input" 
                                   class="flex-1 bg-transparent border-0 focus:ring-0 text-sm py-4 px-5 text-gray-800 placeholder-indigo-300 font-medium" 
                                   placeholder="Initiate command or ask a question..."
                                   required autocomplete="off">
                            <button type="submit" 
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white p-4 rounded-2xl shadow-xl shadow-indigo-200 transition-all hover:-translate-y-1 active:scale-95">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </form>
                    
                    <div class="space-y-6 animate-pulse-slow">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Quick Directives</p>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="askInstitutionQuestion('Show available rooms')" class="px-4 py-2 bg-white text-indigo-600 border border-indigo-100 rounded-xl text-xs font-bold hover:bg-indigo-600 hover:text-white hover:shadow-lg hover:shadow-indigo-100 transition-all">🏠 Rooms</button>
                                <button onclick="askInstitutionQuestion('Generate new timetable')" class="px-4 py-2 bg-white text-emerald-600 border border-emerald-100 rounded-xl text-xs font-bold hover:bg-emerald-600 hover:text-white hover:shadow-lg hover:shadow-emerald-100 transition-all">📅 Generate</button>
                                <button onclick="askInstitutionQuestion('Show conflicts')" class="px-4 py-2 bg-white text-rose-600 border border-rose-100 rounded-xl text-xs font-bold hover:bg-rose-600 hover:text-white hover:shadow-lg hover:shadow-rose-100 transition-all">⚠️ Conflicts</button>
                                <button onclick="askInstitutionQuestion('Lecturer workload analysis')" class="px-4 py-2 bg-white text-cyan-600 border border-cyan-100 rounded-xl text-xs font-bold hover:bg-cyan-600 hover:text-white hover:shadow-lg hover:shadow-cyan-100 transition-all">👨‍🏫 Workload</button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100/50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">Advanced Queries</p>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="askInstitutionQuestion('Resource efficiency metrics')" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-bold hover:bg-indigo-100 transition-all">📊 System Efficiency</button>
                                <button onclick="askInstitutionQuestion('Peak hours analysis')" class="px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg text-[10px] font-bold hover:bg-purple-100 transition-all">📈 Load Patterns</button>
                                <button onclick="askInstitutionQuestion('Optimization suggestions')" class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-bold hover:bg-amber-100 transition-all">💡 Smart Tuning</button>
                            </div>
                        </div>
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
    const container = document.getElementById('institution-chat-container');
    const query = input.value.trim();
    
    if (!query) return;
    
    // Add user message
    addInstitutionMessage(query, 'user');
    input.value = '';
    
    // Show typing indicator
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'flex justify-start msg-reveal typing-indicator';
    typingIndicator.innerHTML = `
        <div class="px-5 py-4 chat-bubble-admin-bot flex items-center space-x-1">
            <div class="typing-pulse"></div>
            <div class="typing-pulse"></div>
            <div class="typing-pulse"></div>
        </div>
    `;
    messages.appendChild(typingIndicator);
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    
    try {
        const response = await fetch('{{ route("institution-admin.chatbot") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ q: query })
        });
        
        // Remove typing indicator
        if (messages.contains(typingIndicator)) {
            messages.removeChild(typingIndicator);
        }
        
        if (response.ok) {
            const data = await response.json();
            addInstitutionMessage(data.answer, 'bot');
        } else {
            addInstitutionMessage('Sorry, I encountered an error. Please try again.', 'bot');
        }
    } catch (error) {
        if (messages.contains(typingIndicator)) {
            messages.removeChild(typingIndicator);
        }
        console.error('Chatbot error:', error);
        addInstitutionMessage('Connection error. Please check your internet connection.', 'bot');
    }
});

function addInstitutionMessage(text, sender) {
    const messages = document.getElementById('institution-chat-messages');
    const container = document.getElementById('institution-chat-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'} msg-reveal`;
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="max-w-[85%] px-5 py-4 chat-bubble-admin-user">
                <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="max-w-[85%] px-5 py-4 chat-bubble-admin-bot">
                <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
            </div>
        `;
    }
    
    messages.appendChild(messageDiv);
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
}

function askInstitutionQuestion(question) {
    const input = document.getElementById('institution-chat-input');
    input.value = question;
    document.getElementById('institution-chat-form').dispatchEvent(new Event('submit'));
}

async function clearInstitutionChat() {
    if (!confirm('Are you sure you want to clear your chat history permanently?')) return;
    
    try {
        const response = await fetch('{{ route("institution-admin.chatbot.clear") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const messages = document.getElementById('institution-chat-messages');
            messages.innerHTML = `
                <div class="flex justify-start msg-reveal">
                    <div class="max-w-[85%] px-5 py-4 chat-bubble-admin-bot">
                        <p class="text-sm">Conversation history purged. Engaged and ready for new directives.</p>
                    </div>
                </div>
            `;
        } else {
            alert('Failed to clear chat history. Please try again.');
        }
    } catch (error) {
        console.error('Clear chat error:', error);
        alert('Failed to clear chat history. Please check your connection.');
    }
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