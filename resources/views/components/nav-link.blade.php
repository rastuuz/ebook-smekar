@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-1.5 rounded-lg bg-white/10 text-sm font-medium leading-5 text-[#d9c27a] shadow-[inset_0_-2px_0_#c9a227] focus:outline-none transition-all duration-200 ease-in-out'
            : 'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium leading-5 text-stone-300 hover:text-white hover:bg-white/5 focus:outline-none transition-all duration-200 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
