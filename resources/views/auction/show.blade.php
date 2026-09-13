<x-layouts.app>
    <x-slot:title>
        Asta Fantacalcio
    </x-slot:title>

    <div
        data-auction-ulid="{{ $auction->ulid }}"
        class="space-y-6">
        <x-auction.header
            :auction="$auction" />

        <x-auction.status />

        <div class="grid gap-6 lg:grid-cols-2">
            <x-auction.nomination-panel
                :player-seasons="$playerSeasons" />

            <x-auction.bid-panel />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-auction.participants />

            <x-auction.president-controls />
        </div>
    </div>
</x-layouts.app>