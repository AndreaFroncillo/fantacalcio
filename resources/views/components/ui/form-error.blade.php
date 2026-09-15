@props([
'messages' => null,
])

@if ($messages)
@foreach ((array) $messages as $message)
<p
    {{ $attributes->class([
                'mt-2 text-sm font-medium',
                'text-red-600',
                'dark:text-red-400',
            ]) }}>
    {{ $message }}
</p>
@endforeach
@endif