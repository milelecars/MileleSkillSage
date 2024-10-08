@props(['active'])

@php
$classes = ($active ?? false)
            ? "inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-white bg-blue-950 transition duration-150 ease-in-out"
            : 'block w-full ps-3 pe-4 py-2 text-start text-base font-medium text-white hover:bg-blue-950 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
