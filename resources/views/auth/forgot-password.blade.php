<x-layouts.public>
    <x-slot:title>
        {{ __('auth.forgot.title') }}
    </x-slot:title>

    <x-navigation.public-navbar />

    <main
        class="
            relative flex min-h-[calc(100vh-5rem)]
            items-center overflow-hidden
            bg-surface-50 px-6 py-16

            dark:bg-dark-950
        ">

        {{-- Background glow --}}
        <div
            class="
                pointer-events-none absolute
                -left-32 top-1/3
                h-96 w-96 rounded-full
                bg-brand-200/30 blur-3xl

                dark:bg-brand-500/5
            ">
        </div>

        <div
            class="
                pointer-events-none absolute
                -right-32 bottom-0
                h-96 w-96 rounded-full
                bg-accent-200/30 blur-3xl

                dark:bg-accent-400/5
            ">
        </div>

        <div
            class="
                relative mx-auto grid w-full max-w-5xl
                overflow-hidden rounded-3xl
                border border-surface-200
                bg-white shadow-xl
                shadow-surface-300/20

                dark:border-white/10
                dark:bg-dark-900
                dark:shadow-black/20

                lg:grid-cols-[0.9fr_1.1fr]
            ">

            {{-- Left panel --}}
            <div
                class="
                    relative hidden overflow-hidden
                    bg-brand-600 p-10 text-white

                    dark:bg-dark-850
                    lg:flex lg:flex-col lg:justify-between
                ">

                <div
                    class="
                        pointer-events-none absolute
                        -right-20 -top-24
                        h-64 w-64 rounded-full
                        border border-white/20
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
                        {{ __('auth.forgot.eyebrow') }}
                    </p>

                    <h1
                        class="
                            mt-5 text-4xl font-black
                            tracking-tight
                        ">
                        {{ __('auth.forgot.panel_title') }}
                    </h1>

                    <p
                        class="
                            mt-5 max-w-md
                            text-base leading-7
                            text-brand-50/90

                            dark:text-surface-300
                        ">
                        {{ __('auth.forgot.panel_description') }}
                    </p>
                </div>

                <p
                    class="
                        relative text-sm font-semibold
                        text-brand-100

                        dark:text-brand-100
                    ">
                    {{ __('auth.forgot.panel_footer') }}
                </p>
            </div>

            {{-- Form --}}
            <div class="p-8 sm:p-10 lg:p-12">

                <div class="mx-auto max-w-md">

                    <p
                        class="
                            text-xs font-black
                            uppercase tracking-[0.25em]
                            text-brand-600

                            dark:text-accent-400
                        ">
                        {{ __('auth.forgot.eyebrow') }}
                    </p>

                    <h2
                        class="
                            mt-4 text-3xl font-black
                            tracking-tight text-surface-900

                            dark:text-white
                        ">
                        {{ __('auth.forgot.title') }}
                    </h2>

                    <p
                        class="
                            mt-2 text-sm leading-6
                            text-surface-600

                            dark:text-surface-300
                        ">
                        {{ __('auth.forgot.description') }}
                    </p>

                    @if (session('status'))
                    <div
                        class="
                                mt-6 rounded-xl
                                border border-brand-200
                                bg-brand-50 p-4
                                text-sm font-medium
                                text-brand-700

                                dark:border-brand-500/20
                                dark:bg-brand-500/10
                                dark:text-brand-200
                            ">
                        {{ session('status') }}
                    </div>
                    @endif

                    <form
                        method="POST"
                        action="{{ route('password.email') }}"
                        class="mt-8 space-y-5">
                        @csrf

                        <x-forms.field
                            name="email"
                            :label="__('auth.fields.email')"
                            type="email"
                            autocomplete="email"
                            required
                            autofocus />

                        <x-ui.button
                            type="submit"
                            class="w-full">
                            {{ __('auth.forgot.submit') }}
                        </x-ui.button>
                    </form>

                    <div
                        class="
                            mt-8 border-t
                            border-surface-200 pt-6
                            text-center

                            dark:border-white/10
                        ">

                        <a
                            href="{{ route('login') }}"
                            class="
                                inline-flex items-center gap-2
                                text-sm font-bold
                                text-brand-600
                                transition
                                hover:text-brand-700

                                dark:text-accent-400
                                dark:hover:text-accent-300
                            ">

                            <span aria-hidden="true">←</span>

                            {{ __('auth.forgot.back_to_login') }}
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </main>
</x-layouts.public>