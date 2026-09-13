<x-ui.card>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">

        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Stato
            </p>

            <p
                data-auction-status
                class="mt-1 text-sm font-semibold text-gray-900"></p>
        </div>

        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Ruolo
            </p>

            <p
                data-auction-role
                class="mt-1 text-sm font-semibold text-gray-900"></p>
        </div>

        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Giocatore
            </p>

            <p
                data-auction-player
                class="mt-1 text-sm font-semibold text-gray-900"></p>
        </div>

        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Offerta corrente
            </p>

            <p
                data-auction-current-bid
                class="mt-1 text-sm font-semibold text-gray-900"></p>
        </div>

        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Timer
            </p>

            <p
                data-auction-countdown
                class="mt-1 text-lg font-bold text-gray-900"></p>
        </div>

    </div>
</x-ui.card>