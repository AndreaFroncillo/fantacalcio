@props([
'type' => 'text',
'name',
'value' => null,
])

<input
    type="{{ $type }}"
    name="{{ $name }}"

    @if ($type !=='password' )
    value="{{ old($name, $value) }}"
    @endif

    {{ $attributes->class([
        'block w-full rounded-lg px-3 py-2 shadow-sm',
        'border-0 ring-1 ring-inset',
        'transition',

        'bg-white text-surface-900',
        'ring-surface-300',
        'placeholder:text-surface-400',

        'focus:ring-2 focus:ring-inset focus:ring-brand-600',

        'disabled:cursor-not-allowed',
        'disabled:bg-surface-100',
        'disabled:text-surface-500',

        'dark:bg-dark-850',
        'dark:text-surface-100',
        'dark:ring-white/15',
        'dark:placeholder:text-surface-500',
        'dark:focus:ring-accent-400',
        'dark:disabled:bg-dark-800',
        'dark:disabled:text-surface-500',

        'sm:text-sm',
    ]) }}>