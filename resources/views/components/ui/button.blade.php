@props([
'type' => 'button',
'variant' => 'primary',
])

@php
$variants = [
'primary' => 'bg-brand-600 text-white hover:bg-brand-500 focus-visible:outline-brand-600',
'secondary' => 'bg-white text-surface-900 ring-1 ring-inset ring-surface-300 hover:bg-surface-50',
'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600',
'warning' => 'bg-amber-500 text-surface-900 hover:bg-amber-400 focus-visible:outline-amber-500',
];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition',
        'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50',
        $variants[$variant] ?? $variants['primary'],
    ]) }}>
    {{ $slot }}
</button>