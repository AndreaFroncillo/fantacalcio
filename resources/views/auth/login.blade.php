<x-layouts.public>
    <x-slot:title>
        {{ __('auth.login.title') }}
    </x-slot:title>

    <x-navigation.public-navbar />

    <main
        class="
            relative
            flex min-h-[calc(100vh-73px)]
            items-center
            overflow-hidden
            bg-surface-50
            px-6 py-16

            dark:bg-dark-950
        ">

        {{-- Background decoration --}}
        <div
            aria-hidden="true"
            class="
                pointer-events-none
                absolute -left-32 top-20
                h-80 w-80
                rounded-full
                bg-brand-200/30
                blur-3xl

                dark:bg-brand-500/10
            ">
        </div>

        <div
            aria-hidden="true"
            class="
                pointer-events-none
                absolute -right-32 bottom-10
                h-96 w-96
                rounded-full
                bg-accent-200/30
                blur-3xl

                dark:bg-accent-400/5
            ">
        </div>

        <div
            class="
                relative
                mx-auto
                grid w-full max-w-5xl
                overflow-hidden
                rounded-[2rem]
                border border-surface-200
                bg-white
                shadow-[0_24px_70px_rgba(100,116,139,0.14)]

                lg:grid-cols-[0.9fr_1.1fr]

                dark:border-white/10
                dark:bg-dark-900
                dark:shadow-[0_24px_70px_rgba(0,0,0,0.30)]
            ">

            {{-- Intro --}}
            <section
                class="
                    relative
                    hidden
                    overflow-hidden
                    bg-brand-600
                    p-10
                    lg:flex
                    lg:flex-col
                    lg:justify-between

                    dark:bg-dark-850
                ">

                <div
                    aria-hidden="true"
                    class="
                        absolute -right-24 -top-24
                        h-64 w-64
                        rounded-full
                        border border-white/15
                    ">
                </div>

                <div class="relative">
                    <p
                        class="
                            text-xs font-black
                            uppercase tracking-[0.25em]
                            text-brand-100

                            dark:text-accent-400
                        ">
                        {{ __('auth.login.eyebrow') }}
                    </p>

                    <h1
                        class="
                            mt-5
                            text-4xl font-black
                            tracking-tight
                            text-white
                        ">
                        {{ __('auth.login.hero_title') }}
                    </h1>

                    <p
                        class="
                            mt-5
                            max-w-sm
                            text-sm leading-7
                            text-brand-50/90

                            dark:text-surface-300
                        ">
                        {{ __('auth.login.hero_description') }}
                    </p>
                </div>

                <p
                    class="
                        relative mt-12
                        text-sm font-semibold
                        text-brand-100

                        dark:text-surface-400
                    ">
                    {{ __('auth.login.hero_footer') }}
                </p>
            </section>

            {{-- Form --}}
            <section class="p-7 sm:p-10 lg:p-12">

                <div class="mx-auto max-w-md">

                    <div>
                        <p
                            class="
                                text-sm font-black
                                uppercase tracking-[0.2em]
                                text-brand-600

                                dark:text-accent-400
                            ">
                            {{ __('auth.login.eyebrow') }}
                        </p>

                        <h2
                            class="
                                mt-3
                                text-3xl font-black
                                tracking-tight
                                text-surface-900

                                dark:text-white
                            ">
                            {{ __('auth.login.title') }}
                        </h2>

                        <p
                            class="
                                mt-2
                                text-sm
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('auth.login.description') }}
                        </p>
                    </div>

                    @if (session('status'))
                    <div
                        class="
                                mt-6
                                rounded-xl
                                border border-brand-200
                                bg-brand-50
                                px-4 py-3
                                text-sm font-medium
                                text-brand-700

                                dark:border-brand-500/20
                                dark:bg-brand-500/10
                                dark:text-brand-300
                            ">
                        {{ session('status') }}
                    </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('login.store') }}"
                        class="mt-8 space-y-5">
                        @csrf

                        <x-forms.field
                            name="email"
                            :label="__('auth.login.identifier')"
                            type="text"
                            autocomplete="username"
                            required
                            autofocus />

                        <x-forms.field
                            name="password"
                            :label="__('auth.login.password')"
                            type="password"
                            autocomplete="current-password"
                            required />

                        <div
                            class="
                                flex flex-wrap
                                items-center
                                justify-between
                                gap-3
                            ">

                            <label
                                class="
                                    flex cursor-pointer
                                    items-center gap-2
                                    text-sm font-medium
                                    text-surface-600

                                    dark:text-surface-300
                                ">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    class="
                                        size-4
                                        rounded
                                        border-surface-300
                                        text-brand-600
                                        focus:ring-brand-500

                                        dark:border-white/20
                                        dark:bg-dark-800
                                    ">

                                {{ __('auth.login.remember') }}
                            </label>

                            <a
                                href="{{ route('password.request') }}"
                                class="
                                    text-sm font-bold
                                    text-brand-600
                                    transition
                                    hover:text-brand-700

                                    dark:text-accent-400
                                    dark:hover:text-accent-300
                                ">
                                {{ __('auth.login.forgot_password') }}
                            </a>

                        </div>

                        <x-ui.button
                            type="submit"
                            class="w-full">
                            {{ __('auth.login.submit') }}
                        </x-ui.button>

                    </form>

                    <p
                        class="
                            mt-8
                            text-center text-sm
                            text-surface-600

                            dark:text-surface-300
                        ">
                        {{ __('auth.login.no_account') }}

                        <a
                            href="{{ route('register') }}"
                            class="
                                font-bold
                                text-brand-600
                                transition
                                hover:text-brand-700

                                dark:text-accent-400
                                dark:hover:text-accent-300
                            ">
                            {{ __('auth.login.register') }}
                        </a>
                    </p>

                </div>

            </section>

        </div>

    </main>

</x-layouts.public>