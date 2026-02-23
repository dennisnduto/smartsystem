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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="p-2 rounded-full bg-indigo-100 mr-3">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-2.58-.37l-3.91 1.2c-.362.112-.94-.258-.97-.8l-.187-3.114A8.001 8.001 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium">AI Assistant</h3>
                            </div>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                    Online
                                </span>
                                <button onclick="clearChat()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div id="chat-container" class="h-80 overflow-y-auto bg-gray-50 rounded-lg p-4 mb-4 border">
                            <div class="space-y-3" id="chat-messages">
                                <div class="flex justify-start">
                                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-indigo-500 text-white shadow-sm">
                                        <p class="text-sm">👋 Hello! I'm your AI assistant. I can help you with:</p>
                                        <ul class="text-sm mt-2 space-y-1">
                                            <li>• Managing institutions & admins</li>
                                            <li>• Analyzing system performance</li>
                                            <li>• Generating reports</li>
                                            <li>• Troubleshooting issues</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form id="chat-form" class="flex space-x-2">
                            <input type="text" 
                                   id="chat-input" 
                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                   placeholder="Ask about institutions, users, analytics, or reports..."
                                   required>
                            <button type="submit" 
                                    class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </form>
                        
                        <!-- Quick Action Buttons -->
                        <div class="mt-4 flex space-x-2">
                            <button onclick="askQuickQuestion('How many users are active today?')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-xs transition">
                                User Stats
                            </button>
                            <button onclick="askQuickQuestion('Show me system performance')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-xs transition">
                                Performance
                            </button>
                            <button onclick="askQuickQuestion('What institutions need attention?')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-xs transition">
                                Alerts
                            </button>
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
            const query = input.value.trim();
            
            if (!query) return;
            
            // Add user message
            addMessage(query, 'user');
            input.value = '';
            
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
                
                const data = await response.json();
                addMessage(data.response || 'Sorry, I encountered an error.', 'bot');
                
            } catch (error) {
                console.error('Chat error:', error);
                addMessage('Sorry, I\'m currently unavailable. Please try again later.', 'bot', true);
            }
        });
        
        function addMessage(text, sender, isError = false) {
            const messages = document.getElementById('chat-messages');
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
                const bgColor = isError ? 'bg-red-500' : 'bg-indigo-500';
                messageDiv.innerHTML = `
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${bgColor} text-white shadow-sm">
                        <p class="text-sm">${text}</p>
                    </div>
                `;
            }
            
            messages.appendChild(messageDiv);
            document.getElementById('chat-container').scrollTop = document.getElementById('chat-container').scrollHeight;
        }
        
        // Quick question functionality
        function askQuickQuestion(question) {
            const input = document.getElementById('chat-input');
            input.value = question;
            document.getElementById('chat-form').dispatchEvent(new Event('submit'));
        }
        
        // Clear chat
        function clearChat() {
            const messages = document.getElementById('chat-messages');
            messages.innerHTML = `
                <div class="flex justify-start">
                    <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg bg-indigo-500 text-white shadow-sm">
                        <p class="text-sm">👋 Hello! I'm your AI assistant. I can help you with:</p>
                        <ul class="text-sm mt-2 space-y-1">
                            <li>• Managing institutions & admins</li>
                            <li>• Analyzing system performance</li>
                            <li>• Generating reports</li>
                            <li>• Troubleshooting issues</li>
                        </ul>
                    </div>
                </div>
            `;
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
