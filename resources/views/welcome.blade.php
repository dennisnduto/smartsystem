<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Smart University Timetable System</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { font-family: 'Figtree', sans-serif; }
            .glass {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.125);
            }
            .hero-gradient {
                background: radial-gradient(circle at top right, #4f46e5, #1e1b4b);
            }
            .floating {
                animation: floating 3s ease-in-out infinite;
            }
            @keyframes floating {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-15px); }
                100% { transform: translateY(0px); }
            }
        </style>
    </head>
    <body class="antialiased bg-slate-50 text-slate-900">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 glass border-b border-indigo-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-black text-indigo-900 leading-none">SMART</div>
                            <div class="text-[10px] font-bold text-indigo-500 tracking-widest uppercase">Timetabling System</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <a href="#features" class="hidden md:block text-sm font-bold text-slate-600 hover:text-indigo-600 transition">Features</a>
                        <a href="#portals" class="hidden md:block text-sm font-bold text-slate-600 hover:text-indigo-600 transition">Portals</a>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition">Log In</a>
                            <a href="{{ route('register') }}" class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                Register
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 hero-gradient relative overflow-hidden">
            <!-- Decorative shapes -->
            <div class="absolute top-0 right-0 w-1/2 h-full bg-indigo-500/10 blur-3xl rounded-full -mr-64 -mt-64"></div>
            <div class="absolute bottom-0 left-0 w-1/3 h-full bg-purple-500/10 blur-3xl rounded-full -ml-32 -mb-32"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-200 text-xs font-bold uppercase tracking-wider mb-6">
                            <span class="w-2 h-2 bg-indigo-400 rounded-full mr-2 animate-pulse"></span>
                            AI-Powered Scheduling Engine
                        </div>
                        <h1 class="text-5xl md:text-7xl font-black text-white leading-[1.1] mb-6">
                            Smart Timetabling <br>
                            & Room Allocation.
                        </h1>
                        <p class="text-lg text-indigo-100/80 mb-10 max-w-lg leading-relaxed">
                            Revolutionizing university operations with intelligent conflict resolution, 
                            real-time capacity tracking, and personalized portals for everyone.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('register') }}" class="px-8 py-4 bg-white text-indigo-900 font-black rounded-2xl hover:scale-105 transition transform shadow-2xl">
                                Start
                            </a>
                            <a href="#features" class="px-8 py-4 bg-indigo-500/20 text-white font-bold rounded-2xl border border-white/20 hover:bg-indigo-500/30 transition">
                                Explore Features
                            </a>
                        </div>
                    </div>
                    <div class="relative hidden lg:block">
                        <div class="floating relative z-20">
                            <img src="{{ asset('images/hero-viz.png') }}" alt="Timetable Visualization" class="rounded-3xl shadow-2xl border border-white/10 glass p-2">
                            <!-- Overlay stats badge -->
                            <div class="absolute -bottom-6 -left-6 glass p-6 rounded-2xl shadow-xl border border-indigo-100/20">
                                <div class="text-3xl font-black text-indigo-900 leading-none">99%</div>
                                <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-1">Conflict Free</div>
                            </div>
                        </div>
                        <!-- Background glow -->
                        <div class="absolute -inset-10 bg-indigo-500/20 blur-3xl rounded-full z-10"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-12 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center group">
                        <div class="text-4xl font-black text-indigo-900 group-hover:scale-110 transition">{{ $stats['institutions'] ?? '0' }}</div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Partner Institutions</div>
                    </div>
                    <div class="text-center group">
                        <div class="text-4xl font-black text-indigo-900 group-hover:scale-110 transition">{{ $stats['users'] ?? '0' }}</div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Active Users</div>
                    </div>
                    <div class="text-center group">
                        <div class="text-4xl font-black text-indigo-900 group-hover:scale-110 transition">{{ $stats['timetables'] ?? '0' }}</div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Timetables Generated</div>
                    </div>
                    <div class="text-center group">
                        <div class="text-4xl font-black text-indigo-900 group-hover:scale-110 transition">24/7</div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">AI Monitoring</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-sm font-black text-indigo-600 uppercase tracking-[0.3em] mb-4">The Platform</h2>
                    <h3 class="text-4xl font-black text-slate-900">Engineered for Excellence</h3>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3">Instant Generation</h4>
                        <p class="text-slate-500 leading-relaxed">
                            Generate complex institutional timetables in seconds using our proprietary heuristics engine.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m4 0h1m-5 10h1m4 0h1m-5-4h1m4 0h1" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3">Room Optimization</h4>
                        <td class="text-slate-500 leading-relaxed">
                            Maximized room utility with real-time capacity monitoring and dynamic booking releases.
                        </td>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3">AI Assistant</h4>
                        <p class="text-slate-500 leading-relaxed">
                            Query schedules, lecturer details, and office hours directly through our integrated chatbot.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Portals Section -->
        <section id="portals" class="py-24 bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between mb-16 gap-4">
                    <div class="text-left">
                        <h2 class="text-sm font-black text-indigo-600 uppercase tracking-[0.3em] mb-4">User Portals</h2>
                        <h3 class="text-4xl font-black text-slate-900 capitalize">One System. Every Role.</h3>
                    </div>
                    <p class="text-slate-500 max-w-sm">Specialized experiences designed for the specific needs of administrators, faculty, and students.</p>
                </div>

                <div class="grid lg:grid-cols-3 gap-12">
                    <!-- Student -->
                    <div class="flex flex-col">
                        <div class="aspect-video bg-indigo-900 rounded-3xl mb-8 flex items-center justify-center relative group overflow-hidden">
                            <svg class="w-16 h-16 text-indigo-400 group-hover:scale-110 transition duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-900 mb-3">Students</h4>
                        <p class="text-slate-500 mb-6 flex-grow">Access your personalized timetable, request study rooms, and get instant updates on session changes.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-sm font-bold text-slate-700">
                                <span class="w-5 h-5 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mr-3">✓</span>
                                Next Class Countdown
                            </li>
                            <li class="flex items-center text-sm font-bold text-slate-700">
                                <span class="w-5 h-5 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mr-3">✓</span>
                                Room Occupancy Checks
                            </li>
                        </ul>
                    </div>

                    <!-- Lecturer -->
                    <div class="flex flex-col">
                        <div class="aspect-video bg-purple-900 rounded-3xl mb-8 flex items-center justify-center relative group overflow-hidden">
                            <svg class="w-16 h-16 text-purple-400 group-hover:scale-110 transition duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-900 mb-3">Lecturers</h4>
                        <p class="text-slate-500 mb-6 flex-grow">Manage your availability, view assigned units, and export high-quality PDF timetables for your sessions.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-sm font-bold text-slate-700">
                                <span class="w-5 h-5 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mr-3">✓</span>
                                Availability Management
                            </li>
                            <li class="flex items-center text-sm font-bold text-slate-700">
                                <span class="w-5 h-5 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mr-3">✓</span>
                                Premium PDF Exports
                            </li>
                        </ul>
                    </div>

                    <!-- Admin -->
                    <div class="flex flex-col">
                        <div class="aspect-video bg-slate-900 rounded-3xl mb-8 flex items-center justify-center relative group overflow-hidden">
                            <svg class="w-16 h-16 text-indigo-400 group-hover:scale-110 transition duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h4 class="text-2xl font-black text-slate-900 mb-3">Administrators</h4>
                        <p class="text-slate-500 mb-6 flex-grow">Full control over institutional records, AI generation settings, and comprehensive system reporting.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-sm font-bold text-slate-700">
                                <span class="w-5 h-5 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center mr-3">✓</span>
                                Matrix View Control
                            </li>
                            <li class="flex items-center text-sm font-bold text-slate-700">
                                <span class="w-5 h-5 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center mr-3">✓</span>
                                Analytic Reports
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 hero-gradient relative">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <h2 class="text-4xl md:text-5xl font-black text-white mb-8">Ready to bring intelligence to your institution?</h2>
                <p class="text-xl text-indigo-100/70 mb-12">Join hundreds of lecturers and thousands of students already using the Smart System.</p>
                <div class="flex flex-wrap justify-center gap-6">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-10 py-5 bg-white text-indigo-900 font-extrabold rounded-2xl shadow-2xl hover:scale-105 transition transform">
                            Enter Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-10 py-5 bg-white text-indigo-900 font-extrabold rounded-2xl shadow-2xl hover:scale-105 transition transform">
                            Register Your Account
                        </a>
                        <a href="{{ route('login') }}" class="px-10 py-5 bg-indigo-500/20 text-white font-bold rounded-2xl border border-white/20 hover:bg-indigo-500/30 transition">
                            Log In to System
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 py-16 text-slate-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-b border-slate-800 pb-12">
                <div class="grid md:grid-cols-4 gap-12">
                    <div class="col-span-2">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-2xl font-black text-white tracking-tight leading-none">Smart Timetabling</span>
                        </div>
                        <p class="max-w-sm leading-relaxed">
                            A next-generation timetabling management system built for clarity, speed, and intelligence. 
                            Simplify your academic ecosystem today.
                        </p>
                    </div>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p>&copy; {{ date('Y') }} Dennis Nduto. All rights reserved.</p>
                <div class="flex gap-6 text-slate-500 text-sm font-bold uppercase tracking-widest">
                    <a href="#" class="hover:text-white transition">Privacy</a>
                    <a href="#" class="hover:text-white transition">Terms</a>
                    <a href="#" class="hover:text-white transition">API</a>
                </div>
            </div>
        </footer>
    </body>
</html>
