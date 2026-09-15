<div
    id="authenticated-mobile-navigation"
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

    <nav
        class="
            mx-auto
            flex max-w-7xl
            flex-col gap-2
        ">

        <div
            class="
                mb-2
                px-3
                text-sm
                text-surface-500

                dark:text-surface-400
            ">
            <span class="font-bold text-surface-800 dark:text-surface-100">
                {{ auth()->user()->name }}
            </span>

            @if (auth()->user()->username)
            <span>
                · {{ '@' . auth()->user()->username }}
            </span>
            @endif
        </div>

        <a
            href="{{ route('dashboard') }}"
            @class([ 'rounded-xl px-4 py-3 text-sm font-bold transition' , 'bg-brand-50 text-brand-700'=> request()->routeIs('dashboard'),
            'text-surface-700 hover:bg-surface-100' => ! request()->routeIs('dashboard'),

            'dark:bg-white/5 dark:text-accent-400' => request()->routeIs('dashboard'),
            'dark:text-surface-200 dark:hover:bg-white/5' => ! request()->routeIs('dashboard'),
            ])>
            {{ __('landing.nav.dashboard') }}
        </a>

        <div
            class="
                my-2
                border-t border-surface-200

                dark:border-white/10
            ">
        </div>

        <form
            method="POST"
            action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="
                    w-full
                    rounded-xl
                    px-4 py-3
                    text-left
                    text-sm font-bold
                    text-surface-700
                    transition
                    hover:bg-surface-100
                    hover:text-brand-700

                    dark:text-surface-200
                    dark:hover:bg-white/5
                    dark:hover:text-accent-400
                ">
                {{ __('auth.login.logout') }}
            </button>
        </form>

    </nav>

</div>