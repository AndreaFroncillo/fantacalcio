<x-layouts.public>
    <x-slot:title>
        {{ __('auth.register.title') }}
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
                        {{ __('auth.register.eyebrow') }}
                    </p>

                    <h1
                        class="
                            mt-5
                            text-4xl font-black
                            tracking-tight
                            text-white
                        ">
                        {{ __('auth.register.hero_title') }}
                    </h1>

                    <p
                        class="
                            mt-5
                            max-w-sm
                            text-sm leading-7
                            text-brand-50/90

                            dark:text-surface-300
                        ">
                        {{ __('auth.register.hero_description') }}
                    </p>
                </div>

                <p
                    class="
                        relative mt-12
                        text-sm font-semibold
                        text-brand-100

                        dark:text-surface-400
                    ">
                    {{ __('auth.register.hero_footer') }}
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
                            {{ __('auth.register.eyebrow') }}
                        </p>

                        <h2
                            class="
                                mt-3
                                text-3xl font-black
                                tracking-tight
                                text-surface-900

                                dark:text-white
                            ">
                            {{ __('auth.register.title') }}
                        </h2>

                        <p
                            class="
                                mt-2
                                text-sm
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('auth.register.description') }}
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('register.store') }}"
                        class="mt-8 space-y-5"
                        data-registration-form
                        data-availability-url="{{ route('register.availability') }}">
                        @csrf

                        <div
                            class="
                                grid gap-5

                                sm:grid-cols-2
                            ">

                            <x-forms.field
                                name="name"
                                :label="__('auth.register.name')"
                                autocomplete="given-name"
                                required
                                autofocus />

                            <x-forms.field
                                name="surname"
                                :label="__('auth.register.surname')"
                                autocomplete="family-name"
                                required />

                        </div>

                        <div>
                            <x-forms.field
                                name="username"
                                :label="__('auth.register.username')"
                                autocomplete="username"
                                minlength="3"
                                maxlength="30"
                                required />

                            <p
                                hidden
                                data-feedback="username"
                                data-min-message="{{ __('auth.validation.username_min') }}"
                                data-format-message="{{ __('auth.validation.username_format') }}"
                                data-unavailable-message="{{ __('auth.validation.username_unique') }}"
                                data-available-message="{{ __('auth.validation.username_available') }}"
                                class="
                                    mt-2
                                    text-sm font-medium
                                ">
                            </p>
                        </div>

                        <div>
                            <x-forms.field
                                name="email"
                                :label="__('auth.register.email')"
                                type="email"
                                autocomplete="email"
                                required />

                            <p
                                hidden
                                data-feedback="email"
                                data-invalid-message="{{ __('auth.validation.email_invalid') }}"
                                data-unavailable-message="{{ __('auth.validation.email_unique') }}"
                                data-available-message="{{ __('auth.validation.email_available') }}"
                                class="
                                    mt-2
                                    text-sm font-medium
                                ">
                            </p>
                        </div>

                        <x-forms.field
                            name="password"
                            :label="__('auth.register.password')"
                            type="password"
                            autocomplete="new-password"
                            required />

                        <div>
                            <x-forms.field
                                name="password_confirmation"
                                :label="__('auth.register.password_confirmation')"
                                type="password"
                                autocomplete="new-password"
                                required />

                            <p
                                hidden
                                data-feedback="password_confirmation"
                                data-mismatch-message="{{ __('auth.validation.password_confirmed') }}"
                                class="
                                    mt-2
                                    text-sm font-medium
                                    text-red-600

                                    dark:text-red-400
                                ">
                            </p>
                        </div>

                        <x-ui.button
                            type="submit"
                            class="w-full">
                            {{ __('auth.register.submit') }}
                        </x-ui.button>

                    </form>

                    <p
                        class="
                            mt-8
                            text-center text-sm
                            text-surface-600

                            dark:text-surface-300
                        ">
                        {{ __('auth.register.already_registered') }}

                        <a
                            href="{{ route('login') }}"
                            class="
                                font-bold
                                text-brand-600
                                transition
                                hover:text-brand-700

                                dark:text-accent-400
                                dark:hover:text-accent-300
                            ">
                            {{ __('auth.register.login') }}
                        </a>
                    </p>

                </div>

            </section>

        </div>

    </main>

</x-layouts.public>