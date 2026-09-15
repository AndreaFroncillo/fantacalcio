@props([
'mobile' => false,
])

<div class="relative group">

    <button
        type="button"
        class="
            flex items-center gap-2
            rounded-xl
            text-sm font-bold
            text-surface-700
            transition
            hover:bg-surface-100

            {{ $mobile ? 'h-10 px-2' : 'px-3 py-2.5' }}

            dark:text-surface-200
            dark:hover:bg-white/5
        ">

        <img
            src="{{ asset(
                app()->getLocale() === 'it'
                    ? 'vendor/blade-flags/language-it.svg'
                    : 'vendor/blade-flags/language-en.svg'
            ) }}"
            alt="{{ app()->getLocale() }}"
            class="h-5 w-5 rounded-full">

        <span class="uppercase">
            {{ app()->getLocale() }}
        </span>

        @unless ($mobile)
        <svg
            class="
                    h-4 w-4
                    transition
                    group-hover:rotate-180
                "
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true">

            <path
                fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                clip-rule="evenodd" />
        </svg>
        @endunless

    </button>

    <div
        class="
            invisible
            absolute
            right-0
            top-full
            z-50
            w-44
            pt-2
            opacity-0
            transition

            group-hover:visible
            group-hover:opacity-100
            group-focus-within:visible
            group-focus-within:opacity-100
        ">

        <div
            class="
                rounded-2xl
                border border-surface-200
                bg-white
                p-2
                shadow-xl

                dark:border-white/10
                dark:bg-dark-850
            ">

            <x-ui.locale-option
                locale="it"
                label="Italiano"
                flag="it" />

            <x-ui.locale-option
                locale="en"
                label="English"
                flag="en" />

        </div>
    </div>

</div>