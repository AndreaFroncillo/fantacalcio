<nav class="border-b border-brand-700 bg-brand-700 text-white shadow-sm">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

        <div class="flex items-center gap-8">
            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-2 text-lg font-bold tracking-tight text-white">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent-400 font-black text-surface-900">
                    F
                </span>

                <span>
                    Fantacalcio
                </span>
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
            <span class="hidden text-sm font-medium text-brand-100 sm:block">
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