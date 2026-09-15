<button
    type="button"
    data-theme-toggle
    aria-label="{{ __('landing.theme.toggle') }}"
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
        data-theme-light-icon
        class="hidden h-5 w-5"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true">
        <circle cx="12" cy="12" r="4" />
        <path
            d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
    </svg>

    <svg
        data-theme-dark-icon
        class="hidden h-5 w-5"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        aria-hidden="true">
        <path
            d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
    </svg>

</button>