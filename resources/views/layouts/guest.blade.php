<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Smart Timetabling and Room Allocation System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="text-center mt-4 px-4">
                <a href="/" class="inline-flex flex-col items-center">
                    <x-application-logo class="w-16 h-16 fill-current text-indigo-600" />
                    <div class="mt-4 flex flex-col items-center">
                        <span class="text-2xl font-black text-gray-900 tracking-tight uppercase leading-none">Smart Timetabling</span>
                        <span class="text-sm font-bold text-indigo-600 uppercase tracking-[0.2em] mt-2">Room Allocation System</span>
                    </div>
                </a>
                <div class="mt-2 text-sm text-gray-500 font-medium">
                    Streamlining schedules and optimizing resources
                </div>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
