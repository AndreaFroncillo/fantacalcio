@props([
'auction',
])

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm font-medium text-indigo-600">
            Asta live
        </p>

        <h1 class="text-2xl font-bold tracking-tight text-gray-900">
            Asta Fantacalcio
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            {{ $auction->ulid }}
        </p>
    </div>
</div>