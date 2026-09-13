<x-layouts.guest>
    <x-slot:title>
        Accedi
    </x-slot:title>

    <x-ui.card>
        <div class="space-y-6">

            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Accedi
                </h1>

                <p class="mt-2 text-sm text-gray-600">
                    Accedi al tuo account Fantacalcio.
                </p>
            </div>

            @if (session('status'))
            <div
                class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
            @endif

            <form
                method="POST"
                action="{{ route('login.store') }}"
                class="space-y-5">
                @csrf

                <x-forms.field
                    name="email"
                    label="Email"
                    type="email"
                    autocomplete="email"
                    required
                    autofocus />

                <x-forms.field
                    name="password"
                    label="Password"
                    type="password"
                    autocomplete="current-password"
                    required />

                <div class="flex items-center justify-between">

                    <label
                        class="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            type="checkbox"
                            name="remember"
                            class="size-4 rounded border-gray-300">

                        Ricordami
                    </label>

                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Password dimenticata?
                    </a>

                </div>

                <x-ui.button
                    type="submit"
                    class="w-full">
                    Accedi
                </x-ui.button>
            </form>

            <p class="text-center text-sm text-gray-600">
                Non hai un account?

                <a
                    href="{{ route('register') }}"
                    class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Registrati
                </a>
            </p>

        </div>
    </x-ui.card>
</x-layouts.guest>