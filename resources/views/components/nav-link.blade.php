@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-2 lg:px-4 py-2 border-b-2 border-indigo-500 text-sm font-medium leading-5 text-indigo-600 bg-indigo-50 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out rounded-t-lg'
            : 'inline-flex items-center px-2 lg:px-4 py-2 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-600 hover:text-gray-800 hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:text-gray-800 focus:border-gray-300 focus:bg-gray-50 transition duration-150 ease-in-out rounded-t-lg';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
