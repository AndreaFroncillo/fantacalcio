<div
    id="mobile-navigation"
    data-mobile-menu
    class="
                    hidden
                    border-t border-surface-200
                    bg-white/95
                    px-6 py-5
                    backdrop-blur-xl

                    lg:hidden

                    dark:border-white/10
                    dark:bg-dark-950/95
                ">

    <nav class="mx-auto flex max-w-7xl flex-col gap-2">

        <a
            href="{{ url('/#come-funziona') }}"
            class="
                            rounded-xl px-3 py-3
                            text-sm font-semibold
                            text-surface-700
                            transition
                            hover:bg-surface-100

                            dark:text-surface-200
                            dark:hover:bg-white/5
                        ">
            {{ __('landing.nav.how_it_works') }}
        </a>

        <a
            href="{{ url('/#faq') }}"
            class="
                            rounded-xl px-3 py-3
                            text-sm font-semibold
                            text-surface-700
                            transition
                            hover:bg-surface-100

                            dark:text-surface-200
                            dark:hover:bg-white/5
                        ">
            {{ __('landing.nav.faq') }}
        </a>

        <a
            href="{{ url('/#contatti') }}"
            class="
                            rounded-xl px-3 py-3
                            text-sm font-semibold
                            text-surface-700
                            transition
                            hover:bg-surface-100

                            dark:text-surface-200
                            dark:hover:bg-white/5
                        ">
            {{ __('landing.nav.contacts') }}
        </a>

        <div
            class="
                            my-2
                            border-t border-surface-200

                            dark:border-white/10
                        ">
        </div>

        @auth
        <a
            href="{{ url('/dashboard') }}"
            class="
                                rounded-xl
                                bg-brand-600
                                px-4 py-3
                                text-center
                                text-sm font-bold
                                text-white

                                dark:bg-accent-400
                                dark:text-dark-950
                            ">
            {{ __('landing.nav.dashboard') }}
        </a>
        @else
        <a
            href="{{ route('login') }}"
            class="
                                rounded-xl px-4 py-3
                                text-center
                                text-sm font-bold
                                text-surface-700
                                transition
                                hover:bg-surface-100

                                dark:text-surface-200
                                dark:hover:bg-white/5
                            ">
            {{ __('landing.nav.login') }}
        </a>

        <a
            href="{{ route('register') }}"
            class="
                                rounded-xl
                                bg-brand-600
                                px-4 py-3
                                text-center
                                text-sm font-bold
                                text-white

                                dark:bg-accent-400
                                dark:text-dark-950
                            ">
            {{ __('landing.nav.register') }}
        </a>
        @endauth

    </nav>
</div>