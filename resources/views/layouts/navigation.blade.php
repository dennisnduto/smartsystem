<nav class="bg-white shadow-lg border-b border-gray-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <x-application-logo class="block h-8 w-auto fill-current text-indigo-600" />
                        <div class="flex flex-col justify-center leading-tight hidden sm:flex">
                            <span class="text-base font-black text-gray-900 tracking-tight">Smart Timetabling</span>
                            <span class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest mt-0.5">Room Allocation System</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-2 lg:ms-8 sm:flex">
                    @if(Auth::check() && Auth::user()->isSuperAdmin())
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super-admin.manage-admins')" :active="request()->routeIs('super-admin.manage-admins')">
                            {{ __('Manage Admins') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super-admin.timetables')" :active="request()->routeIs('super-admin.timetables')">
                            {{ __('Timetables') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super-admin.generate-report')" :active="request()->routeIs('super-admin.generate-report')">
                            {{ __('Reports') }}
                        </x-nav-link>
                    @elseif(Auth::check() && Auth::user()->isInstitutionAdmin())
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <span class="mr-1 hidden 2xl:inline">📊</span> {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('institution-admin.departments.index')" :active="request()->routeIs('institution-admin.departments.*')">
                            <span class="mr-1 hidden 2xl:inline">🏢</span> {{ __('Departments') }}
                        </x-nav-link>
                        <x-nav-link :href="route('institution-admin.lecturers.index')" :active="request()->routeIs('institution-admin.lecturers.*')">
                            <span class="mr-1 hidden 2xl:inline">👨‍🏫</span> {{ __('Lecturers') }}
                        </x-nav-link>
                        <x-nav-link :href="route('institution-admin.courses.index')" :active="request()->routeIs('institution-admin.courses.*')">
                            <span class="mr-1 hidden 2xl:inline">📚</span> {{ __('Courses') }}
                        </x-nav-link>
                        <x-nav-link :href="route('institution-admin.rooms.index')" :active="request()->routeIs('institution-admin.rooms.*')">
                            <span class="mr-1 hidden 2xl:inline">🏠</span> {{ __('Rooms') }}
                        </x-nav-link>
                        <x-nav-link :href="route('institution-admin.timetables.index')" :active="request()->routeIs('institution-admin.timetables.*')">
                            <span class="mr-1 hidden 2xl:inline">📅</span> 
                            <span class="hidden lg:inline">{{ __('Manage Timetables') }}</span>
                            <span class="lg:hidden">{{ __('Timetables') }}</span>
                        </x-nav-link>
                        <x-nav-link :href="route('institution-admin.analytics')" :active="request()->routeIs('institution-admin.analytics')">
                            <span class="mr-1 hidden 2xl:inline">📈</span> {{ __('Analytics') }}
                        </x-nav-link>
                    @elseif(Auth::check() && Auth::user()->isStudent())
                        <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.timetable')" :active="request()->routeIs('student.timetable*')">
                            {{ __('Timetable') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.rooms')" :active="request()->routeIs('student.rooms')">
                            {{ __('Room Availability') }}
                        </x-nav-link>
                    @elseif(Auth::check() && Auth::user()->isLecturer())
                        <x-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('lecturer.timetable')" :active="request()->routeIs('lecturer.timetable*')">
                            {{ __('Timetable') }}
                        </x-nav-link>
                        <x-nav-link :href="route('lecturer.rooms')" :active="request()->routeIs('lecturer.rooms')">
                            {{ __('Room Availability') }}
                        </x-nav-link>
                        <x-nav-link :href="route('lecturer.room-bookings.index')" :active="request()->routeIs('lecturer.room-bookings.*')">
                            {{ __('My Bookings') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

                <!-- Notifications Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ms-3">
                    <x-dropdown align="right" width="80">
                        <x-slot name="trigger">
                            <button class="relative inline-flex items-center p-2 text-gray-500 hover:text-indigo-600 transition focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <span class="absolute top-1.5 right-1.5 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white font-bold items-center justify-center">
                                            {{ Auth::user()->unreadNotifications->count() }}
                                        </span>
                                    </span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                                <span class="text-xs font-black text-gray-900 uppercase tracking-widest">Notifications</span>
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.mark-all-as-read') }}">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-tighter">Mark all read</button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-64 overflow-y-auto">
                                @forelse(Auth::user()->unreadNotifications as $notification)
                                    <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-3 hover:bg-indigo-50/50 transition border-b border-gray-50 last:border-0 group">
                                            <div class="flex items-start gap-3">
                                                <div class="w-2 h-2 mt-1.5 flex-shrink-0 bg-indigo-500 rounded-full animate-pulse"></div>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition">{{ $notification->data['title'] ?? 'System Update' }}</div>
                                                    <div class="text-xs text-gray-500 line-clamp-2 mt-0.5">{{ $notification->data['message'] ?? 'New notification received.' }}</div>
                                                    <div class="text-[10px] text-gray-400 mt-1 font-medium">{{ $notification->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    </form>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <div class="text-gray-300 mb-2">
                                            <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500">All caught up!</p>
                                    </div>
                                @endforelse
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition ease-in-out duration-150">
                            <div class="flex items-center space-x-3">
                                <div class="text-left">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                                    </div>
                                    @if(Auth::check() && Auth::user()->isSuperAdmin())
                                        <div class="text-xs text-red-600 font-medium">Super Admin</div>
                                    @elseif(Auth::check() && Auth::user()->isInstitutionAdmin())
                                        <div class="text-xs text-blue-600 font-medium">{{ Auth::user()->institution ? Auth::user()->institution->name : 'Admin' }}</div>
                                    @elseif(Auth::check() && Auth::user()->isLecturer())
                                        <div class="text-xs text-purple-600 font-medium">Lecturer</div>
                                    @elseif(Auth::check() && Auth::user()->isStudent())
                                        <div class="text-xs text-green-600 font-medium">Year {{ Auth::user()->year_of_study ? substr(Auth::user()->year_of_study, 1) : '?' }}</div>
                                    @endif
                                </div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>{{ __('Profile') }}</span>
                            </div>
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>{{ __('Log Out') }}</span>
                                </div>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden" x-data="{ open: false }">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden absolute top-16 left-0 right-0 bg-white border-b border-gray-200 z-50">
                    <div class="pt-2 pb-3 space-y-1">
                        @if(Auth::check() && Auth::user()->isSuperAdmin())
                            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('super-admin.manage-admins')" :active="request()->routeIs('super-admin.manage-admins')">
                                {{ __('Manage Admins') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('super-admin.timetables')" :active="request()->routeIs('super-admin.timetables')">
                                {{ __('Timetables') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('super-admin.generate-report')" :active="request()->routeIs('super-admin.generate-report')">
                                {{ __('Reports') }}
                            </x-responsive-nav-link>
                        @elseif(Auth::check() && Auth::user()->isInstitutionAdmin())
                            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                <span class="mr-1">📊</span> {{ __('Dashboard') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('institution-admin.departments.index')" :active="request()->routeIs('institution-admin.departments.*')">
                                <span class="mr-1">🏢</span> {{ __('Departments') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('institution-admin.lecturers.index')" :active="request()->routeIs('institution-admin.lecturers.*')">
                                <span class="mr-1">👨‍🏫</span> {{ __('Lecturers') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('institution-admin.courses.index')" :active="request()->routeIs('institution-admin.courses.*')">
                                <span class="mr-1">📚</span> {{ __('Courses') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('institution-admin.rooms.index')" :active="request()->routeIs('institution-admin.rooms.*')">
                                <span class="mr-1">🏠</span> {{ __('Rooms') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('institution-admin.timetables.index')" :active="request()->routeIs('institution-admin.timetables.*')">
                                <span class="mr-1">📅</span> {{ __('Manage Timetables') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('institution-admin.analytics')" :active="request()->routeIs('institution-admin.analytics')">
                                <span class="mr-1">📈</span> {{ __('Analytics') }}
                            </x-responsive-nav-link>
                        @elseif(Auth::check() && Auth::user()->isStudent())
                            <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                                {{ __('Dashboard') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('student.timetable')" :active="request()->routeIs('student.timetable*')">
                                {{ __('Timetable') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('student.rooms')" :active="request()->routeIs('student.rooms')">
                                {{ __('Room Availability') }}
                            </x-responsive-nav-link>
                        @elseif(Auth::check() && Auth::user()->isLecturer())
                            <x-responsive-nav-link :href="route('lecturer.dashboard')" :active="request()->routeIs('lecturer.dashboard')">
                                {{ __('Dashboard') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('lecturer.timetable')" :active="request()->routeIs('lecturer.timetable*')">
                                {{ __('Timetable') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('lecturer.rooms')" :active="request()->routeIs('lecturer.rooms')">
                                {{ __('Room Availability') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('lecturer.room-bookings.index')" :active="request()->routeIs('lecturer.room-bookings.*')">
                                {{ __('My Bookings') }}
                            </x-responsive-nav-link>
                        @endif
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                                @if(Auth::check() && Auth::user()->isSuperAdmin())
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Super Admin
                                    </span>
                                @elseif(Auth::check() && Auth::user()->isInstitutionAdmin())
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ Auth::user()->institution ? Auth::user()->institution->name : 'Admin' }}
                                    </span>
                                @elseif(Auth::check() && Auth::user()->isLecturer())
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Lecturer
                                    </span>
                                @elseif(Auth::check() && Auth::user()->isStudent())
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Year {{ Auth::user()->year_of_study ? substr(Auth::user()->year_of_study, 1) : '?' }}
                                    </span>
                                @endif
                            </div>
                            <div class="font-medium text-sm text-gray-500">{{ Auth::check() ? Auth::user()->email : 'guest@example.com' }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <x-responsive-nav-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-responsive-nav-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" id="logout-form-mobile">
                                @csrf
                                <x-responsive-nav-link :href="route('logout')"
                                        onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                                    {{ __('Log Out') }}
                                </x-responsive-nav-link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
