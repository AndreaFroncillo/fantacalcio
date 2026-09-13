@props([
'href',
'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'rounded-md px-3 py-2 text-sm font-medium transition',
        'bg-gray-900 text-white' => $active,
        'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! $active,
    ]) }}>
    {{ $slot }}
</a>