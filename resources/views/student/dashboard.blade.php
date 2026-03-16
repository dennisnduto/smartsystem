@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 premium-font">Student Dashboard</h1>
                <p class="text-gray-500 mt-2 font-medium">Welcome back, {{ $user->name }} • Live Overview</p>
            </div>
            <div class="text-right">
                <div id="live-time" class="text-2xl font-black text-indigo-600 premium-font">{{ now()->setTimezone('Africa/Nairobi')->format('H:i:s') }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ now()->setTimezone('Africa/Nairobi')->format('l, jS F') }}</div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-slate-200">
                <div class="p-5">
                    <div class="text-2xl mb-1">📚</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">My Courses</div>
                    <div class="text-2xl font-black text-slate-900 premium-font">{{ $courses->count() }}</div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-slate-200">
                <div class="p-5">
                    <div class="text-2xl mb-1">🏫</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Today's Classes</div>
                    <div class="text-2xl font-black text-slate-900 premium-font" id="today-classes-count">{{ $todayEntries->count() }}</div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-slate-200">
                <div class="p-5">
                    <div class="text-2xl mb-1">📅</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Next Lecture</div>
                    <div id="next-lecture-display" class="mt-1">
                        @if($nextLecture)
                            <div class="text-sm font-black text-slate-900 truncate">{{ $nextLecture->unit->code ?? 'Unknown' }}</div>
                            <div class="text-[10px] text-slate-500 truncate mt-0.5">{{ $nextLecture->unit->name ?? 'Unknown' }}</div>
                            <div class="text-[9px] font-bold text-indigo-600 mt-1 flex items-center gap-1">
                                <span class="bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">👨‍🏫 {{ $nextLecture->lecturer->name ?? 'TBA' }}</span>
                            </div>
                        @else
                            <div class="text-sm font-bold text-slate-300">None Scheduled</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-slate-200">
                <div class="p-5">
                    <div class="text-2xl mb-1">🏫</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Free Rooms</div>
                    <div class="text-2xl font-black text-slate-900 premium-font" id="available-rooms-count">{{ $availableRoomsCount ?? '...' }}</div>
                    <div class="text-[9px] font-bold text-emerald-600 mt-1 uppercase tracking-tighter flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Available Now
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-slate-200">
                <div class="p-5">
                    <div class="text-2xl mb-1">🎓</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Year</div>
                    <div class="text-2xl font-black text-slate-900 premium-font">{{ $user->year_of_study ? substr($user->year_of_study, 1) : '?' }}</div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-all rounded-2xl border border-slate-200">
                <div class="p-5">
                    <div class="text-2xl mb-1">🏛️</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Institution</div>
                    <div class="text-lg font-black text-slate-900 premium-font">{{ $user->institution->name ?? 'None' }}</div>
                </div>
            </div>
        </div>

        <!-- Today's Sessions -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Today's Sessions</h2>
            </div>
            <div class="p-6">
                @php
                    $now = now()->setTimezone('Africa/Nairobi');
                    $currentDayOfWeek = (int)$now->dayOfWeekIso;
                    $isWeekend = $currentDayOfWeek >= 6;
                    $currentTime = $now->format('H:i');
                    // Sync with controller logic
                    if ($currentTime < '07:00') $currentSlot = 0;
                    elseif ($currentTime < '10:00') $currentSlot = 1;
                    elseif ($currentTime < '13:00') $currentSlot = 2;
                    elseif ($currentTime < '16:00') $currentSlot = 3;
                    elseif ($currentTime < '19:00') $currentSlot = 4;
                    else $currentSlot = 5;

                    $timeSlots = [1=>'7:00am-10:00am',2=>'10:00am-1:00pm',3=>'1:00pm-4:00pm',4=>'4:00pm-7:00pm'];
                    $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
                @endphp
                
                @if($isWeekend)
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🌴</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Weekend - No Classes</h3>
                        <p class="text-gray-600">Enjoy your weekend! Classes resume on Monday.</p>
                    </div>
                @else
                <div class="mb-4">
                    <span class="text-sm text-gray-500">{{ $now->format('l, F j, Y') }} • Current Time: <span id="current-time-status" class="font-bold text-indigo-600">{{ $now->format('H:i') }}</span></span>
                </div>
                
                <div id="today-sessions-container" class="space-y-4">
                    @forelse($todayEntries as $entry)
                        <div class="border-l-4 {{ $entry->slot == $currentSlot ? 'border-green-500 bg-green-50' : ($entry->slot < $currentSlot ? 'border-gray-300 bg-gray-50' : 'border-blue-500 bg-blue-50') }} pl-4 py-3 rounded-r-lg transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $entry->unit->code ?? 'Unknown' }} — {{ $entry->unit->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ $timeSlots[$entry->slot] }} • Room: {{ $entry->room->name ?? 'TBA' }}
                                        @if($entry->lecturer) • Lecturer: {{ $entry->lecturer->name }} @endif
                                    </div>
                                </div>
                                <div class="text-sm">
                                    @if($entry->slot == $currentSlot)
                                        <span class="px-3 py-1 bg-green-600 text-white text-xs rounded-full animate-pulse">NOW</span>
                                    @elseif($entry->slot < $currentSlot)
                                        <span class="px-3 py-1 bg-gray-400 text-white text-xs rounded-full">COMPLETED</span>
                                    @else
                                        <span class="px-3 py-1 bg-blue-600 text-white text-xs rounded-full">UPCOMING</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">📚</div>
                            <p>No classes scheduled for today</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

        <!-- Week Grid -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Weekly Timetable</h2>
                    <div class="text-xs text-gray-500">All your classes for the week</div>
                </div>
            </div>
            <div class="p-6 overflow-auto">
                <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3 font-semibold text-gray-600 border-b border-r">Time</th>
                            @foreach($dayNames as $dn)
                                <th class="text-left p-3 font-semibold text-gray-600 border-b border-r">{{ $dn }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeSlots as $slotNum => $label)
                            <tr class="align-top">
                                <td class="p-3 font-medium text-gray-700 border-t border-r bg-gray-50">{{ $label }}</td>
                                @foreach($dayNames as $dayNum => $dn)
                                    @php $cell = ($entriesByDay[$dayNum] ?? collect())->filter(fn($e) => (int)$e->slot === $slotNum); @endphp
                                    <td class="p-3 border-t border-r">
                                        @forelse($cell as $e)
                                            <div class="border rounded p-2 mb-2 bg-blue-50 border-blue-200">
                                                <div class="font-semibold text-blue-900">{{ optional($e->unit)->code }}</div>
                                                <div class="text-xs text-gray-700 mt-1">{{ optional($e->unit)->name }}</div>
                                                <div class="text-xs text-gray-600 mt-1">Course: {{ optional($e->course)->name ?? '—' }}</div>
                                                <div class="text-xs text-green-700 mt-1">
                                                    <span class="px-2 py-0.5 bg-green-100 rounded-full font-semibold">{{ optional($e->room)->name ?? 'TBA' }}</span>
                                                </div>
                                                @if($e->lecturer)
                                                    <div class="text-xs text-gray-600 mt-1">Lecturer: {{ $e->lecturer->name }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-xs text-gray-300">—</div>
                                        @endforelse
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AI Assistant Chatbot -->
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap');
            
            .glass-panel {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
            }
            
            .chat-bubble-user {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                border-radius: 1.2rem 1.2rem 0.2rem 1.2rem;
                box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
            }
            
            .chat-bubble-bot {
                background: white;
                border-radius: 1.2rem 1.2rem 1.2rem 0.2rem;
                border: 1px solid rgba(0, 0, 0, 0.05);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
                color: #1e293b;
            }
            
            .chat-container-scroll {
                scrollbar-width: thin;
                scrollbar-color: rgba(0, 0, 0, 0.1) transparent;
            }
            
            .chat-container-scroll::-webkit-scrollbar {
                width: 5px;
            }
            
            .chat-container-scroll::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.1);
                border-radius: 20px;
            }

            @keyframes slideIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .message-animation {
                animation: slideIn 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
            }

            .typing-dot {
                width: 6px;
                height: 6px;
                background: #64748b;
                border-radius: 50%;
                animation: bounce 1.4s infinite ease-in-out both;
            }

            .typing-dot:nth-child(1) { animation-delay: -0.32s; }
            .typing-dot:nth-child(2) { animation-delay: -0.16s; }

            @keyframes bounce {
                0%, 80%, 100% { transform: scale(0); }
                40% { transform: scale(1.0); }
            }
        </style>

        <div class="glass-panel overflow-hidden shadow-2xl rounded-3xl mb-12 relative border-0">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600"></div>
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
                            🤖
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900" style="font-family: 'Outfit', sans-serif;">Student Assistant</h3>
                            <div class="flex items-center space-x-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                <span class="text-xs font-semibold text-green-700 uppercase tracking-wider">Live Support</span>
                            </div>
                        </div>
                    </div>
                    <button onclick="clearStudentChat()" class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all duration-200" title="Clear Chat">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
                
                <div id="student-chat-container" class="h-80 overflow-y-auto chat-container-scroll px-2 mb-6">
                    <div class="space-y-6" id="student-chat-messages">
                        <div class="flex justify-start message-animation">
                            <div class="max-w-[85%] lg:max-w-md px-5 py-3 chat-bubble-bot">
                                <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">
                                    👋 Hi <strong>{{ $user->name }}</strong>! I'm your advanced campus assistant. I've analyzed your schedule and I'm ready to help. Try asking about your next lecture or free rooms!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative group">
                    <form id="student-chat-form" class="flex items-center space-x-3 bg-white/50 backdrop-blur-md p-2 rounded-2xl border border-gray-100 focus-within:border-blue-300 focus-within:ring-4 focus-within:ring-blue-100 transition-all duration-300">
                        <input type="text" 
                               id="student-chat-input" 
                               class="flex-1 bg-transparent border-0 focus:ring-0 text-gray-700 placeholder-gray-400 py-3 px-4" 
                               placeholder="Type your question here..."
                               required autocomplete="off">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-xl shadow-lg shadow-blue-200 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button onclick="askStudentQuestion('What is my next class?')" class="group flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 text-sm border border-indigo-100 rounded-xl hover:bg-indigo-600 hover:text-white transition-all duration-200 font-bold shadow-sm">
                        <span class="mr-2">📅</span> Next Class?
                    </button>
                    <button onclick="askStudentQuestion('Show my classes for today')" class="group flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 text-sm border border-indigo-100 rounded-xl hover:bg-indigo-600 hover:text-white transition-all duration-200 font-bold shadow-sm">
                        <span class="mr-2">🕒</span> Today's Schedule
                    </button>
                    <button onclick="askStudentQuestion('Are there any free rooms?')" class="group flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-sm border border-emerald-100 rounded-xl hover:bg-emerald-600 hover:text-white transition-all duration-200 font-bold shadow-sm">
                        <span class="mr-2">🏫</span> Free Rooms?
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Quick Actions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('student.timetable') }}" class="block p-6 bg-slate-50 border border-slate-200 rounded-2xl hover:bg-white hover:shadow-xl hover:border-indigo-200 transition-all group">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform">📅</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 premium-font tracking-tight">View Timetable</h3>
                        <p class="text-sm text-slate-500 font-medium">See your complete weekly schedule</p>
                    </a>
                    
                    <a href="{{ route('student.rooms') }}" class="block p-6 bg-slate-50 border border-slate-200 rounded-2xl hover:bg-white hover:shadow-xl hover:border-emerald-200 transition-all group">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform">🏫</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 premium-font tracking-tight">Room Availability</h3>
                        <p class="text-sm text-slate-500 font-medium">Check currently available rooms</p>
                    </a>
                    
                    <a href="{{ route('student.timetable.print') }}" class="block p-6 bg-slate-50 border border-slate-200 rounded-2xl hover:bg-white hover:shadow-xl hover:border-rose-200 transition-all group">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform">🖨️</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 premium-font tracking-tight">Print Timetable</h3>
                        <p class="text-sm text-slate-500 font-medium">Download your schedule as PDF</p>
                    </a>
                    
                    <a href="{{ route('student.timetable.full') }}" class="block p-6 bg-slate-50 border border-slate-200 rounded-2xl hover:bg-white hover:shadow-xl hover:border-violet-200 transition-all group">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform">🌐</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 premium-font tracking-tight">Full Timetable</h3>
                        <p class="text-sm text-slate-500 font-medium">View entire institution-wide schedule</p>
                    </a>
                    
                    <a href="{{ route('profile.edit') }}" class="block p-6 bg-slate-50 border border-slate-200 rounded-2xl hover:bg-white hover:shadow-xl hover:border-amber-200 transition-all group">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform">👤</div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 premium-font tracking-tight">My Profile</h3>
                        <p class="text-sm text-slate-500 font-medium">Update account information</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time clock updates
function updateLiveTime() {
    const liveTimeElement = document.getElementById('live-time');
    if (liveTimeElement) {
        liveTimeElement.textContent = new Date().toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
}

function updateCurrentTime() {
    const timeDisplay = document.getElementById('current-time');
    if (timeDisplay) {
        timeDisplay.textContent = new Date().toLocaleTimeString('en-US', { 
            hour12: false,
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Start updates
updateLiveTime();
updateCurrentTime();
setInterval(() => {
    updateLiveTime();
    updateCurrentTime();
}, 1000);

// Student Chat Functions
document.getElementById('student-chat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const input = document.getElementById('student-chat-input');
    const messages = document.getElementById('student-chat-messages');
    const query = input.value.trim();
    
    if (!query) return;
    
    // Add user message
    addStudentMessage(query, 'user');
    input.value = '';
    
    // Show typing indicator
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'flex justify-start message-animation typing-indicator';
    typingIndicator.innerHTML = `
        <div class="px-5 py-3 chat-bubble-bot flex items-center space-x-1">
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        </div>
    `;
    messages.appendChild(typingIndicator);
    const container = document.getElementById('student-chat-container');
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    
    try {
        const response = await fetch('{{ route("student.chatbot") }}', {
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
            addStudentMessage(data.answer, 'bot');
        } else {
            addStudentMessage('Sorry, I encountered an error. Please try again.', 'bot');
        }
    } catch (error) {
        if (messages.contains(typingIndicator)) {
            messages.removeChild(typingIndicator);
        }
        console.error('Chatbot error:', error);
        addStudentMessage('Connection error. Please check your internet connection.', 'bot');
    }
});

function addStudentMessage(text, sender) {
    const messages = document.getElementById('student-chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'} message-animation`;
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="max-w-[85%] lg:max-w-md px-5 py-3 chat-bubble-user text-white shadow-lg">
                <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="max-w-[85%] lg:max-w-md px-5 py-3 chat-bubble-bot">
                <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
            </div>
        `;
    }
    
    messages.appendChild(messageDiv);
    const container = document.getElementById('student-chat-container');
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
}

function askStudentQuestion(question) {
    const input = document.getElementById('student-chat-input');
    input.value = question;
    document.getElementById('student-chat-form').dispatchEvent(new Event('submit'));
}

async function clearStudentChat() {
    if (!confirm('Are you sure you want to clear your chat history permanently?')) return;
    
    try {
        const response = await fetch('{{ route("student.chatbot.clear") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const messages = document.getElementById('student-chat-messages');
            messages.innerHTML = `
                <div class="flex justify-start">
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-blue-600 text-white shadow-sm">
                        <p class="text-sm">Chat history cleared permanently. How can I help you today?</p>
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

// Real-time Dashboard Polling
function updateDashboard() {
    fetch('{{ route("student.dashboard.stats") }}')
        .then(response => response.json())
        .then(data => {
            // Update live time
            document.getElementById('live-time').innerText = data.live_time;
            document.getElementById('current-time-status').innerText = data.live_time.substring(0, 5);
            
            // Update stats
            document.getElementById('today-classes-count').innerText = data.today_classes_count;
            document.getElementById('available-rooms-count').innerText = data.available_rooms_count;
            
            // Update Next Lecture Display
            const nextDisplay = document.getElementById('next-lecture-display');
            if (data.next_lecture) {
                nextDisplay.innerHTML = `
                    <div class="text-sm font-black text-slate-900 truncate">${data.next_lecture.unit_code}</div>
                    <div class="text-[10px] text-slate-500 truncate mt-0.5">${data.next_lecture.unit_name}</div>
                    <div class="text-[9px] font-bold text-indigo-600 mt-1 flex items-center gap-1">
                        <span class="bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">👨‍🏫 ${data.next_lecture.lecturer}</span>
                    </div>
                `;
            } else {
                nextDisplay.innerHTML = '<div class="text-sm font-bold text-slate-300">None Scheduled</div>';
            }
            
            // Update Today's Sessions if not weekend
            if (!data.is_weekend && data.today_sessions) {
                const sessionContainer = document.getElementById('today-sessions-container');
                let html = '';
                
                if (data.today_sessions.length > 0) {
                    data.today_sessions.forEach(session => {
                        let borderClass = 'border-blue-500 bg-blue-50';
                        let badgeHtml = '<span class="px-3 py-1 bg-blue-600 text-white text-xs rounded-full">UPCOMING</span>';
                        
                        if (session.status === 'NOW') {
                            borderClass = 'border-green-500 bg-green-50';
                            badgeHtml = '<span class="px-3 py-1 bg-green-600 text-white text-xs rounded-full animate-pulse">NOW</span>';
                        } else if (session.status === 'COMPLETED') {
                            borderClass = 'border-gray-300 bg-gray-50';
                            badgeHtml = '<span class="px-3 py-1 bg-gray-400 text-white text-xs rounded-full">COMPLETED</span>';
                        }
                        
                        html += `
                            <div class="border-l-4 ${borderClass} pl-4 py-3 rounded-r-lg transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-900">${session.unit_code} — ${session.unit_name}</div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            ${session.time} • Room: ${session.room}
                                            ${session.lecturer ? '• Lecturer: ' + session.lecturer : ''}
                                        </div>
                                    </div>
                                    <div class="text-sm">${badgeHtml}</div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = `
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">📚</div>
                            <p>No classes scheduled for today</p>
                        </div>
                    `;
                }
                sessionContainer.innerHTML = html;
            }
        })
        .catch(error => console.error('Dashboard sync error:', error));
}

// Start polling every 60 seconds
setInterval(updateDashboard, 60000);

// Initial update on load
document.addEventListener('DOMContentLoaded', updateDashboard);

// Update localized live time every second for UI feel
setInterval(() => {
    const now = new Date();
    // Use Intl.DateTimeFormat with explicit timezone for safety, though localeTime usually defaults to system
    // For a web dashboard, usually "current time" means user's time, but the requirement is to match server's logic (Nairobi)
    const options = { timeZone: 'Africa/Nairobi', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const timeStr = new Intl.DateTimeFormat('en-GB', options).format(now);
    document.getElementById('live-time').innerText = timeStr;
    document.getElementById('current-time-status').innerText = timeStr.substring(0, 5);
}, 1000);
</script>
@endsection
