<div
    data-club-preview
    aria-hidden="true"
    class="
        pointer-events-auto
        absolute
        z-[100]
        w-72
        rounded-2xl
        border border-surface-200
        bg-white
        p-5
        text-left
        opacity-0
        shadow-[0_18px_45px_rgba(15,23,42,0.16)]
        transition-opacity
        duration-150

        max-lg:hidden

        dark:border-white/10
        dark:bg-dark-800
        dark:shadow-[0_18px_50px_rgba(0,0,0,0.35)]
    ">

    <p
        data-club-preview-name
        class="
            text-base font-black
            text-surface-900

            dark:text-white
        ">
    </p>

    <p
        data-club-preview-competition
        class="
            mt-1
            text-xs font-semibold
            text-surface-500

            dark:text-surface-400
        ">
    </p>

    <div
        data-club-preview-players
        class="
        mt-5
        space-y-4
    ">
    </div>

    <button
        type="button"
        data-club-preview-link
        class="
            mt-5
            inline-flex
            items-center
            gap-1
            text-xs font-bold
            text-brand-600
            transition
            hover:text-brand-700

            dark:text-accent-400
            dark:hover:text-accent-300
        ">
        {{ __('landing.clubs.preview.cta') }}
    </button>

</div>