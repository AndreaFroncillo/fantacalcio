@props([
'href',
'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'rounded-lg px-3 py-2 text-sm font-semibold transition',
        'bg-white/15 text-white shadow-sm' => $active,
        'text-brand-100 hover:bg-white/10 hover:text-white' => ! $active,
    ]) }}>
    {{ $slot }}
</a>
