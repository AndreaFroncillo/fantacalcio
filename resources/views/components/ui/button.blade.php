@props([
'type' => 'button',
'variant' => 'primary',
])

@php
$variants = [
'primary' => 'bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600',
'secondary' => 'bg-white text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600',
];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        $variants[$variant] ?? $variants['primary'],
    ]) }}>
    {{ $slot }}
</button>