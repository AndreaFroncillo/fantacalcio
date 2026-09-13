<x-layouts.app>
    <x-slot:title>
        Dashboard
    </x-slot:title>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                Dashboard
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Benvenuto, {{ auth()->user()->name }}.
            </p>
        </div>

        <x-ui.card>
            <p class="text-sm text-gray-600">
                Da qui gestiremo leghe, stagioni, squadre, aste e mercato.
            </p>
        </x-ui.card>
    </div>
</x-layouts.app>