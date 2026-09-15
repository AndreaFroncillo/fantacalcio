<x-ui.card>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">

        <div class="rounded-xl bg-surface-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-surface-500">
                Stato
            </p>

            <p
                data-auction-status
                class="mt-2 text-sm font-bold text-brand-700"></p>
        </div>

        <div class="rounded-xl bg-surface-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-surface-500">
                Ruolo
            </p>

            <p
                data-auction-role
                class="mt-2 text-sm font-bold text-surface-900"></p>
        </div>

        <div class="rounded-xl bg-surface-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-surface-500">
                Giocatore
            </p>

            <p
                data-auction-player
                class="mt-2 text-sm font-bold text-surface-900"></p>
        </div>

        <div class="rounded-xl bg-surface-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-surface-500">
                Offerta corrente
            </p>

            <p
                data-auction-current-bid
                class="mt-2 text-lg font-black text-accent-600"></p>
        </div>

        <div class="rounded-xl bg-surface-900 p-4 text-white">
            <p class="text-xs font-bold uppercase tracking-wide text-surface-300">
                Timer
            </p>

            <p
                data-auction-countdown
                class="mt-2 text-2xl font-black tabular-nums text-white"></p>
        </div>

    </div>
</x-ui.card>