<x-ui.card>
    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                Controlli presidente
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Conferma o rifiuta l'aggiudicazione corrente.
            </p>
        </div>

        <div
            data-auction-president-controls
            class="flex flex-wrap gap-2"></div>

        <p
            data-auction-president-error
            class="text-sm text-red-600"></p>
    </div>
</x-ui.card>