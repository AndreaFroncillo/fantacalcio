@props([
'auction',
])

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-brand-800">
                Asta live
            </span>
        </div>

        <h1 class="mt-3 text-3xl font-bold tracking-tight text-surface-900">
            Asta Fantacalcio
        </h1>

        <p class="mt-1 font-mono text-xs text-surface-500">
            {{ $auction->ulid }}
        </p>
    </div>
</div>