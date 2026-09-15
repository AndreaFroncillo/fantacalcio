@props([
'playerSeasons',
])

<x-ui.card>
    <div class="space-y-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 font-bold text-brand-700">
                    N
                </span>

                <h2 class="text-lg font-bold text-surface-900">
                    Nomina giocatore
                </h2>
            </div>

            <p class="mt-2 text-sm text-surface-600">
                Seleziona il prossimo giocatore da mettere all'asta.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <select
                data-auction-nomination-player
                class="block w-full rounded-lg border-0 bg-white px-3 py-2.5 text-sm text-surface-900 shadow-sm ring-1 ring-inset ring-surface-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 disabled:cursor-not-allowed disabled:bg-surface-100">
                <option value="">
                    Seleziona un giocatore
                </option>

                @foreach ($playerSeasons as $playerSeason)
                <option value="{{ $playerSeason->ulid }}">
                    {{ $playerSeason->footballPlayer->display_name ?? 'Giocatore' }}
                </option>
                @endforeach
            </select>

            <x-ui.button
                type="button"
                data-auction-start-nomination>
                Nomina
            </x-ui.button>
        </div>

        <p
            data-auction-nomination-error
            class="text-sm font-medium text-red-600"></p>
    </div>
</x-ui.card>