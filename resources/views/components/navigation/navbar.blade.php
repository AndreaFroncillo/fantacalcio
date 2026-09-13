<nav class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

        <div class="flex items-center gap-8">
            <a
                href="{{ route('dashboard') }}"
                class="text-lg font-bold text-gray-900">
                Fantacalcio
            </a>

            <div class="hidden items-center gap-2 md:flex">
                <x-navigation.nav-link
                    :href="route('dashboard')"
                    :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-navigation.nav-link>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <span class="hidden text-sm text-gray-600 sm:block">
                {{ auth()->user()->name }}
            </span>

            <form
                method="POST"
                action="{{ route('logout') }}">
                @csrf

                <x-ui.button
                    type="submit"
                    variant="secondary">
                    Logout
                </x-ui.button>
            </form>
        </div>

    </div>
</nav>