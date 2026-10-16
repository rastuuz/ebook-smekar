@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-stone-300 shadow-sm focus:border-brand-600 focus:ring-brand-600']) }}>
