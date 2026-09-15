@props([
'locale',
'label',
'flag',
])

<form
    method="POST"
    action="{{ route('locale.update', $locale) }}">
    @csrf

    <button
        type="submit"
        class="
            flex w-full items-center gap-3
            rounded-xl px-3 py-2
            text-left text-sm font-semibold
            text-surface-700
            transition
            hover:bg-surface-100

            dark:text-surface-200
            dark:hover:bg-white/5
        ">

        <img
            src="{{ asset('vendor/blade-flags/language-' . $flag . '.svg') }}"
            alt="{{ $label }}"
            class="h-5 w-5 rounded-full">

        <span>
            {{ $label }}
        </span>

        @if (app()->getLocale() === $locale)
        <span
            class="
                    ml-auto
                    h-2 w-2
                    rounded-full
                    bg-brand-500

                    dark:bg-accent-400
                    dark:shadow-[0_0_8px_rgba(163,230,53,0.8)]
                ">
        </span>
        @endif
    </button>
</form>