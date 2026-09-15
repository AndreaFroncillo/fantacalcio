@props([
'for' => null,
])

<label
    @if ($for)
    for="{{ $for }}"
    @endif

    {{ $attributes->class([
        'block text-sm font-semibold',
        'text-surface-800',
        'dark:text-surface-200',
    ]) }}>
    {{ $slot }}
</label>