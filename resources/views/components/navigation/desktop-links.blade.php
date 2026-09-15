<nav class="flex items-center gap-8">

    <a
        href="{{ url('/#come-funziona') }}"
        class="
            text-sm font-semibold
            text-surface-600
            transition
            hover:text-brand-600

            dark:text-surface-300
            dark:hover:text-brand-400
        ">
        {{ __('landing.nav.how_it_works') }}
    </a>

    <a
        href="{{ url('/#faq') }}"
        class="
            text-sm font-semibold
            text-surface-600
            transition
            hover:text-brand-600

            dark:text-surface-300
            dark:hover:text-neon-cyan
        ">
        {{ __('landing.nav.faq') }}
    </a>

    <a
        href="{{ url('/#contatti') }}"
        class="
            text-sm font-semibold
            text-surface-600
            transition
            hover:text-brand-600

            dark:text-surface-300
            dark:hover:text-neon-violet
        ">
        {{ __('landing.nav.contacts') }}
    </a>

</nav>