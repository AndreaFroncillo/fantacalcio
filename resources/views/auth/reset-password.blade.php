<x-layouts.guest>
    <x-slot:title>
        Reimposta password
    </x-slot:title>

    <x-ui.card>
        <div class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Reimposta password
                </h1>

                <p class="mt-2 text-sm text-gray-600">
                    Scegli una nuova password per il tuo account.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('password.update') }}"
                class="space-y-5">
                @csrf

                <input
                    type="hidden"
                    name="token"
                    value="{{ $request->route('token') }}">

                <x-forms.field
                    name="email"
                    label="Email"
                    type="email"
                    :value="$request->email"
                    autocomplete="email"
                    required />

                <x-forms.field
                    name="password"
                    label="Nuova password"
                    type="password"
                    autocomplete="new-password"
                    required />

                <x-forms.field
                    name="password_confirmation"
                    label="Conferma nuova password"
                    type="password"
                    autocomplete="new-password"
                    required />

                <x-ui.button
                    type="submit"
                    class="w-full">
                    Reimposta password
                </x-ui.button>
            </form>
        </div>
    </x-ui.card>
</x-layouts.guest>