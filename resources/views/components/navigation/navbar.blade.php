<header
    class="
        sticky top-0 z-50
        border-b border-surface-200/80
        bg-white/85
        backdrop-blur-xl

        dark:border-white/10
        dark:bg-dark-950/80
    ">

    <div
        class="
            relative
            mx-auto
            flex h-[73px]
            max-w-7xl
            items-center
            justify-between
            px-6
            lg:px-8
        ">

        {{-- Brand --}}
        <x-navigation.brand />

        {{-- Desktop navigation --}}
        <nav
            class="
                absolute left-1/2
                hidden -translate-x-1/2
                items-center gap-2
                lg:flex
            ">

            <a
                href="{{ route('dashboard') }}"
                @class([ 'rounded-xl px-4 py-2.5 text-sm font-bold transition' , 'bg-brand-50 text-brand-700'=> request()->routeIs('dashboard'),
                'text-surface-600 hover:bg-surface-100 hover:text-brand-600' => ! request()->routeIs('dashboard'),

                'dark:bg-white/5 dark:text-accent-400' => request()->routeIs('dashboard'),
                'dark:text-surface-300 dark:hover:bg-white/5 dark:hover:text-accent-400' => ! request()->routeIs('dashboard'),
                ])>
                {{ __('landing.nav.dashboard') }}
            </a>

        </nav>

        {{-- Desktop actions --}}
        <div class="hidden items-center gap-2 lg:flex">

            <x-navigation.locale-selector />

            <x-navigation.theme-toggle />

            <div
                class="
                    ml-1 h-6 w-px
                    bg-surface-200

                    dark:bg-white/10
                ">
            </div>

            <span
                class="
                    ml-1
                    max-w-36 truncate
                    text-sm font-bold
                    text-surface-700

                    dark:text-surface-200
                ">
                {{ auth()->user()->name }}
            </span>

            <form
                method="POST"
                action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="
                        rounded-xl
                        px-4 py-2.5
                        text-sm font-bold
                        text-surface-600
                        transition
                        hover:bg-surface-100
                        hover:text-brand-700

                        dark:text-surface-300
                        dark:hover:bg-white/5
                        dark:hover:text-accent-400
                    ">
                    {{ __('auth.login.logout') }}
                </button>
            </form>

        </div>

        {{-- Mobile actions --}}
        <div class="flex items-center gap-1 lg:hidden">

            <x-navigation.locale-selector mobile />

            <x-navigation.theme-toggle />

            <button
                type="button"
                data-mobile-menu-toggle
                aria-expanded="false"
                aria-controls="authenticated-mobile-navigation"
                aria-label="{{ __('landing.nav.menu') }}"
                class="
                    flex h-10 w-10
                    items-center justify-center
                    rounded-xl
                    text-surface-700
                    transition
                    hover:bg-surface-100
                    hover:text-brand-600

                    dark:text-surface-200
                    dark:hover:bg-white/5
                    dark:hover:text-accent-400
                ">

                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    aria-hidden="true">

                    <path d="M4 7h16" />
                    <path d="M4 12h16" />
                    <path d="M4 17h16" />

                </svg>

            </button>

        </div>

    </div>

    <x-navigation.authenticated-mobile-menu />

</header>