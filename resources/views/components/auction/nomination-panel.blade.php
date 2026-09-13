@props([
'playerSeasons',
])

<x-ui.card>
    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                Nomina giocatore
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Seleziona il prossimo giocatore da mettere all'asta.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <select
                data-auction-nomination-player
                class="block w-full rounded-md border-0 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600">
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
            class="text-sm text-red-600"></p>
    </div>
</x-ui.card>