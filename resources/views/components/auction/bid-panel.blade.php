<x-ui.card class="border-l-4 border-l-accent-400">
    <div class="space-y-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent-100 font-bold text-accent-700">
                    €
                </span>

                <h2 class="text-lg font-bold text-surface-900">
                    Offerte
                </h2>
            </div>

            <p class="mt-2 text-sm text-surface-600">
                Effettua un rilancio sul giocatore corrente.
            </p>
        </div>

        <div
            data-auction-bid-controls
            class="flex flex-wrap gap-2"></div>

        <p
            data-auction-bid-error
            class="text-sm font-medium text-red-600"></p>
    </div>
</x-ui.card>