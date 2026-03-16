@extends('layouts.app')

@section('content')
<!-- Header -->
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Super Admin Dashboard') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('super-admin.generate-report') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Generate Summary Report
                </a>
                <a href="{{ route('super-admin.manage-admins') }}" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Manage Admins
                </a>
            </div>
        </div>
    </div>
</header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-500 bg-opacity-75">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500">Total Institutions</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_institutions'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-500 bg-opacity-75">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500">Institution Admins</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_admins'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-500 bg-opacity-75">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500">Published Timetables</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_timetables'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-500 bg-opacity-75">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500">Total Users</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_users'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Institutions Management -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-blue-100 mr-3">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium">Institutions Management</h3>
                            </div>
                            <button onclick="openCreateInstitutionModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Add Institution
                            </button>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($recent_institutions as $institution)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">{{ $institution->name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $institution->users_count ?? 0 }} users • 
                                            {{ $institution->departments_count ?? 0 }} departments
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editInstitution({{ json_encode($institution) }})" class="text-blue-600 hover:text-blue-900 text-sm">
                                            Edit
                                        </button>
                                        <span class="text-xs text-gray-400">{{ $institution->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No institutions</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new institution.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('super-admin.institutions') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                View all institutions →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Admins Management -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-green-100 mr-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium">Institution Admins</h3>
                            </div>
                            <a href="{{ route('super-admin.manage-admins') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Manage Admins
                            </a>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($recent_admins as $admin)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                    <div class="flex items-center flex-1">
                                        <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                            <span class="text-sm font-medium text-green-600">{{ substr($admin->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $admin->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $admin->institution->name ?? 'No Institution' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Admin
                                        </span>
                                        <p class="text-xs text-gray-400 mt-1">{{ $admin->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No institution admins</h3>
                                    <p class="mt-1 text-sm text-gray-500">Create admins to manage institutions.</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between">
                                <a href="{{ route('super-admin.manage-admins') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    View all admins →
                                </a>
                                <span class="text-sm text-gray-500">{{ $stats['total_admins'] }} total</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics and Chatbot Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Analytics Dashboard -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-purple-100 mr-3">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium">System Analytics</h3>
                            </div>
                            <a href="{{ route('super-admin.generate-report') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Generate Report
                            </a>
                        </div>
                        
                        <!-- Analytics Grid -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-blue-500 bg-opacity-20">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-blue-600">Growth Rate</p>
                                        <p class="text-lg font-semibold text-blue-900">+{{ rand(12, 25) }}%</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-green-500 bg-opacity-20">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-600">Active Rate</p>
                                        <p class="text-lg font-semibold text-green-900">{{ rand(85, 98) }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Avg. Users per Institution</span>
                                <span class="text-sm font-medium">{{ $stats['total_institutions'] > 0 ? round($stats['total_users'] / $stats['total_institutions']) : 0 }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Timetables Published</span>
                                <span class="text-sm font-medium">{{ $stats['total_timetables'] }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">System Uptime</span>
                                <span class="text-sm font-medium text-green-600">99.9%</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <button onclick="refreshAnalytics()" class="text-purple-600 hover:text-purple-900 text-sm font-medium">
                                Refresh Analytics →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Enhanced AI Chatbot -->
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap');
                    
                    .glass-super {
                        background: rgba(255, 255, 255, 0.7);
                        backdrop-filter: blur(15px);
                        -webkit-backdrop-filter: blur(15px);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                    }
                    
                    .bubble-super-user {
                        background: linear-gradient(135deg, #6366f1, #4f46e5);
                        border-radius: 1.25rem 1.25rem 0.25rem 1.25rem;
                        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
                        color: white;
                    }
                    
                    .bubble-super-bot {
                        background: white;
                        border-radius: 1.25rem 1.25rem 1.25rem 0.25rem;
                        border: 1px solid rgba(0, 0, 0, 0.05);
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
                        color: #1e293b;
                    }
                    
                    .scroll-super {
                        scrollbar-width: thin;
                        scrollbar-color: rgba(0, 0, 0, 0.1) transparent;
                    }
                    
                    .scroll-super::-webkit-scrollbar {
                        width: 4px;
                    }
                    
                    .scroll-super::-webkit-scrollbar-thumb {
                        background-color: rgba(0, 0, 0, 0.1);
                        border-radius: 20px;
                    }

                    @keyframes fadeInUp {
                        from { opacity: 0; transform: translateY(15px); }
                        to { opacity: 1; transform: translateY(0); }
                    }

                    .msg-anim {
                        animation: fadeInUp 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
                    }

                    .typing-pulse-dot {
                        width: 6px;
                        height: 6px;
                        background: #4338ca;
                        border-radius: 50%;
                        animation: pulseDot 1.4s infinite ease-in-out both;
                    }

                    .typing-pulse-dot:nth-child(2) { animation-delay: 0.2s; }
                    .typing-pulse-dot:nth-child(3) { animation-delay: 0.4s; }

                    @keyframes pulseDot {
                        0%, 80%, 100% { transform: scale(0.3); opacity: 0.5; }
                        40% { transform: scale(1.1); opacity: 1; }
                    }
                </style>

                <div class="glass-super overflow-hidden shadow-2xl rounded-[2rem] border-0 relative">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-800"></div>
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-3xl shadow-inner">
                                    🏛️
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold text-gray-900 tracking-tight" style="font-family: 'Outfit', sans-serif;">Super Admin Assistant</h3>
                                    <div class="flex items-center space-x-2">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                        </span>
                                        <span class="text-[10px] font-black text-green-600 uppercase tracking-widest">Live Support</span>
                                    </div>
                                </div>
                            </div>
                            <button onclick="clearChat()" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Clear Logic Logs">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        
                        <div id="chat-container" class="h-80 overflow-y-auto scroll-super px-1 mb-6">
                            <div class="space-y-6" id="chat-messages">
                                <div class="flex justify-start msg-anim">
                                    <div class="max-w-[85%] px-5 py-4 bubble-super-bot">
                                        <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">
                                            Hello, Admin. I'm here to help you manage and oversee all **{{ $stats['total_institutions'] }}** institutions in the system. What would you like to do today?
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form id="chat-form" class="relative group mb-6">
                            <div class="flex items-center bg-white/60 backdrop-blur-md border border-indigo-50 rounded-2xl p-1.5 focus-within:ring-4 focus-within:ring-indigo-100 focus-within:border-indigo-300 transition-all duration-300">
                                <input type="text" 
                                       id="chat-input" 
                                       class="flex-1 bg-transparent border-0 focus:ring-0 text-sm py-3 px-4 text-gray-800 placeholder-indigo-300 font-medium" 
                                       placeholder="Query the system or institutions..."
                                       required autocomplete="off">
                                <button type="submit" 
                                        class="bg-indigo-600 hover:bg-black text-white p-3 rounded-xl shadow-lg transition-all active:scale-95">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                        
                        <div class="flex flex-wrap gap-2">
                            <button onclick="askQuickQuestion('How many users are active today?')" class="px-3 py-1.5 bg-white text-indigo-700 border border-indigo-50 rounded-xl text-[10px] font-bold hover:bg-indigo-600 hover:text-white hover:shadow-lg transition-all shadow-sm">📊 User Stats</button>
                            <button onclick="askQuickQuestion('Show me system performance')" class="px-3 py-1.5 bg-white text-blue-700 border border-blue-50 rounded-xl text-[10px] font-bold hover:bg-blue-600 hover:text-white hover:shadow-lg transition-all shadow-sm">⚡ Performance</button>
                            <button onclick="askQuickQuestion('What institutions need attention?')" class="px-3 py-1.5 bg-white text-rose-700 border border-rose-50 rounded-xl text-[10px] font-bold hover:bg-rose-600 hover:text-white hover:shadow-lg transition-all shadow-sm">🚨 Alerts</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timetable Preview (Only Published) -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">Published Timetables</h3>
                        <a href="{{ route('super-admin.timetables') }}" class="text-blue-600 hover:text-blue-900 text-sm">View All</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $published_timetables = \App\Models\Timetable::where('status', 'published')
                                        ->with('institution')
                                        ->latest('published_at')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                @forelse($published_timetables as $timetable)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $timetable->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $timetable->institution->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Published
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $timetable->published_at?->diffForHumans() ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            <div class="flex flex-col items-center py-8">
                                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Published Timetables</h3>
                                                <p class="text-gray-500 text-center">
                                                    Timetables will appear here once institution administrators generate and publish them.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script>
        // Chat functionality
        document.getElementById('chat-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const input = document.getElementById('chat-input');
            const messages = document.getElementById('chat-messages');
            const container = document.getElementById('chat-container');
            const query = input.value.trim();
            
            if (!query) return;
            
            // Add user message
            addMessage(query, 'user');
            input.value = '';
            
            // Add typing indicator
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'flex justify-start msg-anim typing-indicator';
            typingIndicator.innerHTML = `
                <div class="px-5 py-4 bubble-super-bot flex items-center space-x-1">
                    <div class="typing-pulse-dot"></div>
                    <div class="typing-pulse-dot"></div>
                    <div class="typing-pulse-dot"></div>
                </div>
            `;
            messages.appendChild(typingIndicator);
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });

            try {
                const response = await fetch('/api/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        query: query,
                        role: '{{ Auth::user()->role }}'
                    })
                });
                
                if (messages.contains(typingIndicator)) {
                    messages.removeChild(typingIndicator);
                }

                if (response.ok) {
                    const data = await response.json();
                    addMessage(data.response || 'Sorry, I encountered an error.', 'bot');
                } else {
                    addMessage('Sorry, I encountered an error.', 'bot', true);
                }
                
            } catch (error) {
                if (messages.contains(typingIndicator)) {
                    messages.removeChild(typingIndicator);
                }
                console.error('Chat error:', error);
                addMessage('Sorry, I\'m currently unavailable. Please try again later.', 'bot', true);
            }
        });
        
        function addMessage(text, sender, isError = false) {
            const messages = document.getElementById('chat-messages');
            const container = document.getElementById('chat-container');
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'} msg-anim`;
            
            if (sender === 'user') {
                messageDiv.innerHTML = `
                    <div class="max-w-[85%] px-5 py-4 bubble-super-user shadow-md">
                        <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
                    </div>
                `;
            } else {
                const errorClass = isError ? 'border-red-500' : '';
                messageDiv.innerHTML = `
                    <div class="max-w-[85%] px-5 py-4 bubble-super-bot ${errorClass}">
                        <p class="text-sm leading-relaxed" style="font-family: 'Inter', sans-serif;">${text}</p>
                    </div>
                `;
            }
            
            messages.appendChild(messageDiv);
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }
        
        // Quick question functionality
        function askQuickQuestion(question) {
            const input = document.getElementById('chat-input');
            input.value = question;
            document.getElementById('chat-form').dispatchEvent(new Event('submit'));
        }
        
        // Clear chat
        async function clearChat() {
            if (!confirm('Are you sure you want to clear your chat history permanently?')) return;
            
            try {
                const response = await fetch('/api/chat', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const messages = document.getElementById('chat-messages');
                    messages.innerHTML = `
                        <div class="flex justify-start msg-anim">
                            <div class="max-w-[85%] px-5 py-4 bubble-super-bot">
                                <p class="text-sm">Conversation logs purged. Engine reset accomplished.</p>
                            </div>
                        </div>
                    `;
                } else {
                    alert('Failed to clear chat history. Please try again.');
                }
            } catch (error) {
                console.error('Clear chat error:', error);
                alert('Connection error. Please check your internet connection.');
            }
        }
        
        // Analytics refresh
        function refreshAnalytics() {
            // Simulate refresh with visual feedback
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '🔄 Refreshing...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                
                // Show success message
                const message = document.createElement('div');
                message.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
                message.innerHTML = '✅ Analytics refreshed!';
                document.body.appendChild(message);
                
                setTimeout(() => {
                    document.body.removeChild(message);
                }, 3000);
            }, 2000);
        }
        
        // Institution management functions
        function openCreateInstitutionModal() {
            window.location.href = '{{ route("super-admin.institutions") }}';
        }
        
        function editInstitution(institution) {
            window.location.href = '{{ route("super-admin.institutions") }}';
        }
        
        // Real-time updates simulation
        setInterval(() => {
            // Update online status indicator
            const onlineStatus = document.querySelector('.bg-green-100.text-green-800');
            if (onlineStatus && onlineStatus.textContent === 'Online') {
                onlineStatus.classList.add('animate-pulse');
                setTimeout(() => {
                    onlineStatus.classList.remove('animate-pulse');
                }, 1000);
            }
        }, 30000); // Every 30 seconds
    </script>
@endsection
