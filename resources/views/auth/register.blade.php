<x-layouts.guest>
    <x-slot:title>
        Registrati
    </x-slot:title>

    <x-ui.card>
        <div class="space-y-6">

            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Crea il tuo account
                </h1>

                <p class="mt-2 text-sm text-gray-600">
                    Registrati per partecipare alle tue leghe.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('register.store') }}"
                class="space-y-5">
                @csrf

                <x-forms.field
                    name="name"
                    label="Nome"
                    autocomplete="name"
                    required
                    autofocus />

                <x-forms.field
                    name="email"
                    label="Email"
                    type="email"
                    autocomplete="email"
                    required />

                <x-forms.field
                    name="password"
                    label="Password"
                    type="password"
                    autocomplete="new-password"
                    required />

                <x-forms.field
                    name="password_confirmation"
                    label="Conferma password"
                    type="password"
                    autocomplete="new-password"
                    required />

                <x-ui.button
                    type="submit"
                    class="w-full">
                    Registrati
                </x-ui.button>
            </form>

            <p class="text-center text-sm text-gray-600">
                Hai già un account?

                <a
                    href="{{ route('login') }}"
                    class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Accedi
                </a>
            </p>

        </div>
    </x-ui.card>
</x-layouts.guest>