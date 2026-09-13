<x-layouts.guest>
    <x-slot:title>
        Conferma password
    </x-slot:title>

    <x-ui.card>
        <div class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Conferma password
                </h1>

                <p class="mt-2 text-sm text-gray-600">
                    Per continuare, conferma la password del tuo account.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('password.confirm.store') }}"
                class="space-y-5">
                @csrf

                <x-forms.field
                    name="password"
                    label="Password"
                    type="password"
                    autocomplete="current-password"
                    required
                    autofocus />

                <x-ui.button
                    type="submit"
                    class="w-full">
                    Conferma
                </x-ui.button>
            </form>
        </div>
    </x-ui.card>
</x-layouts.guest>