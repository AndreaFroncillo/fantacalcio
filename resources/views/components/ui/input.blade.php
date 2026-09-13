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
        'block w-full rounded-md border-0 bg-white px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm',
    ]) }}>