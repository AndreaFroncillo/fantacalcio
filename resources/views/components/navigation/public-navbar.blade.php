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
            flex
            max-w-7xl
            items-center
            justify-between
            px-6 py-4
            lg:px-8
        ">

        {{-- Brand --}}
        <x-navigation.brand />

        {{-- Desktop navigation: realmente centrata --}}
        <div
            class="
                absolute
                left-1/2
                hidden
                -translate-x-1/2
                lg:block
            ">
            <x-navigation.desktop-links />
        </div>

        {{-- Desktop actions --}}
        <div class="hidden items-center gap-3 lg:flex">
            <x-navigation.locale-selector />
            <x-navigation.theme-toggle />
            <x-navigation.auth-actions />
        </div>

        {{-- Mobile actions --}}
        <div class="flex items-center gap-1 lg:hidden">
            <x-navigation.locale-selector mobile />
            <x-navigation.theme-toggle />

            <button
                type="button"
                data-mobile-menu-toggle
                aria-expanded="false"
                aria-controls="mobile-navigation"
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

    <x-navigation.mobile-menu />

</header>