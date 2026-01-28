<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Smart University Timetable System</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-indigo-50 to-purple-50">
        <div class="min-h-screen flex flex-col">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                Smart University Timetable
                            </h1>
                        </div>
                        <nav class="flex gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 hover:text-indigo-600 transition">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    </div>
                </div>
            </header>

            <main class="flex-1">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-gray-900 mb-4">
                            Intelligent Timetable Management System
                        </h2>
                        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                            Streamline your university's scheduling with AI-powered conflict detection, 
                            real-time room availability, and automated notifications.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6 mb-12">
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Smart Scheduling</h3>
                            <p class="text-gray-600">
                                AI-powered timetable generation that prioritizes lecturer availability and prevents conflicts.
                            </p>
                        </div>

                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Room Management</h3>
                            <p class="text-gray-600">
                                Real-time room availability tracking with automatic booking and release capabilities.
                            </p>
                        </div>

                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">AI Assistant</h3>
                            <p class="text-gray-600">
                                Intelligent chatbot that answers questions about schedules, rooms, and lecturers.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                        <h3 class="text-2xl font-semibold text-gray-900 mb-4">Get Started</h3>
                        <p class="text-gray-600 mb-6">
                            Join your institution and start managing your timetable efficiently.
                        </p>
                        @guest
                            <div class="flex gap-4 justify-center">
                                <a href="{{ route('register') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                                    Register as Student
                                </a>
                                <a href="{{ route('login') }}" class="px-6 py-3 border-2 border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 transition font-semibold">
                                    Log In
                                </a>
                            </div>
                        @endguest
                    </div>
                </div>
            </main>

            <footer class="bg-white border-t mt-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <p class="text-center text-gray-600">
                        &copy; {{ date('Y') }} Smart University Timetable System. All rights reserved.
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>
