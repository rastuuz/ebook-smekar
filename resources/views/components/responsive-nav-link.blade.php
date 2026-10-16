@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg bg-white/[0.08] px-3 py-2 text-start text-sm font-medium text-white shadow-[inset_3px_0_0_#c9a227] focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full rounded-lg px-3 py-2 text-start text-sm font-medium text-stone-300 hover:bg-white/10 hover:text-white focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
