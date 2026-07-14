@props(['active' => false, 'href' => '#'])

@php
    $classes = $active
        ? 'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-indigo-700 bg-indigo-50'
        : 'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
