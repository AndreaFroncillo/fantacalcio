@auth
<a
    href="{{ url('/dashboard') }}"
    class="
            rounded-xl
            bg-brand-600
            px-5 py-2.5
            text-sm font-bold
            text-white
            transition
            hover:bg-brand-500
        ">
    {{ __('landing.nav.dashboard') }}
</a>
@else
<a
    href="{{ route('login') }}"
    class="
            rounded-xl
            px-4 py-2.5
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
            px-5 py-2.5
            text-sm font-bold
            text-white
            shadow-lg
            shadow-brand-600/20
            transition
            hover:-translate-y-0.5
            hover:bg-brand-500

            dark:bg-accent-400
            dark:text-dark-950
            dark:shadow-[0_0_25px_rgba(163,230,53,0.20)]
        ">
    {{ __('landing.nav.register') }}
</a>
@endauth