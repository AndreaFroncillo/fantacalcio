<x-ui.card class="border-t-4 border-t-brand-600">
    <div class="space-y-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 font-bold text-brand-700">
                    P
                </span>

                <h2 class="text-lg font-bold text-surface-900">
                    Controlli presidente
                </h2>
            </div>

            <p class="mt-2 text-sm text-surface-600">
                Conferma o rifiuta l'aggiudicazione corrente.
            </p>
        </div>

        <div
            data-auction-president-controls
            class="flex flex-wrap gap-2"></div>

        <p
            data-auction-president-error
            class="text-sm font-medium text-red-600"></p>
    </div>
</x-ui.card>