<x-layouts.guest>
    <x-slot:title>
        Password dimenticata
    </x-slot:title>

    <x-ui.card>
        <div class="space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Password dimenticata?
                </h1>

                <p class="mt-2 text-sm text-gray-600">
                    Inserisci la tua email e ti invieremo il link per reimpostare la password.
                </p>
            </div>

            @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
            @endif

            <form
                method="POST"
                action="{{ route('password.email') }}"
                class="space-y-5">
                @csrf

                <x-forms.field
                    name="email"
                    label="Email"
                    type="email"
                    autocomplete="email"
                    required
                    autofocus />

                <x-ui.button
                    type="submit"
                    class="w-full">
                    Invia link di recupero
                </x-ui.button>
            </form>

            <p class="text-center text-sm text-gray-600">
                <a
                    href="{{ route('login') }}"
                    class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Torna al login
                </a>
            </p>
        </div>
    </x-ui.card>
</x-layouts.guest>