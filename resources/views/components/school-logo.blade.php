@props(['size' => 'md'])

@php
$sizes = [
    'xs' => 'h-8 w-8 rounded-lg',
    'sm' => 'h-9 w-9 rounded-lg',
    'md' => 'h-11 w-11 rounded-xl',
];
$cls = $sizes[$size] ?? $sizes['md'];
@endphp

@if(file_exists(public_path('images/logo-smk.png')))
    <span {{ $attributes->merge(['class' => $cls.' inline-flex shrink-0 items-center justify-center overflow-hidden bg-white p-1 ring-1 ring-white/25 shadow-sm']) }}>
        <img src="{{ asset('images/logo-smk.png') }}" alt="Logo SMKN 1 Dukuhturi" class="h-full w-full object-contain" fetchpriority="high" decoding="async">
    </span>
@else
    <span {{ $attributes->merge(['class' => $cls.' inline-flex shrink-0 items-center justify-center bg-[#0e1b33] font-display font-semibold text-[#d9c27a] ring-1 ring-white/20']) }} aria-hidden="true">
        <span class="text-[1.35em] leading-none">E</span>
    </span>
@endif
