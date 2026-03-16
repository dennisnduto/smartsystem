<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">                    <div class="flex items-center gap-4">
                        @if(auth()->user() && auth()->user()->role === 'institution_admin')
                        <a href="/timetables/generate" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Generate Timetable</a>
                        @endif

                        @if(auth()->user() && auth()->user()->role === 'super_admin')
                        <a href="/admin" class="inline-flex items-center px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-800">Super Admin</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
