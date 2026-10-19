@props(['href' => '#', 'label' => 'Kembali'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn-ghost inline-flex items-center gap-1.5 !py-2 text-sm']) }}>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    {{ $label }}
</a>
