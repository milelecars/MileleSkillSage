@props(['active'])

@php
$classes = ($active ?? false)
            ? 'h-10 inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-white bg-blue-950 rounded focus:outline-none transition duration-150 ease-in-out'
            : 'h-10 inline-flex items-center px-1 pt-1  text-sm font-medium leading-5 text-white hover:text-white hover:bg-blue-950 rounded focus:outline-none focus:text-white transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
