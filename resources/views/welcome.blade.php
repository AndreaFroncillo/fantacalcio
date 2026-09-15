<x-layouts.public>
    <x-slot:title>
        Fantacalcio
    </x-slot:title>

    <div class="relative overflow-x-clip">

        {{-- Ambient background --}}
        <div
            class="
                pointer-events-none
                absolute inset-x-0 top-0
                -z-10
                h-[700px]
                bg-gradient-to-b
                from-brand-100/70
                via-surface-50
                to-transparent

                dark:from-brand-500/10
                dark:via-dark-950
                dark:to-dark-950
            ">
        </div>

        {{-- NAVBAR --}}
        <x-navigation.public-navbar />


        <main>

            {{-- HERO --}}
            <section
                class="
                    mx-auto
                    max-w-7xl
                    px-6
                    pb-12
                    pt-20
                    lg:px-8
                    lg:pb-20
                    lg:pt-28
                ">

                <div
                    class="
                        relative
                        mx-auto
                        max-w-5xl
                        overflow-hidden
                        rounded-[2rem]
                        border
                        border-surface-200
                        bg-white/90
                        px-6 py-14
                        text-center
                        shadow-xl
                        shadow-surface-900/5

                        sm:px-12
                        lg:px-20
                        lg:py-20

                        dark:border-brand-400/20
                        dark:bg-dark-850/85
                        dark:shadow-[0_0_80px_rgba(16,185,129,0.08)]
                    ">

                    {{-- Neon accents --}}
                    <div
                        class="
                            absolute
                            -left-24 -top-24
                            h-64 w-64
                            rounded-full
                            bg-brand-400/20
                            blur-3xl

                            dark:bg-brand-400/15
                        ">
                    </div>

                    <div
                        class="
                            absolute
                            -bottom-24 -right-24
                            h-64 w-64
                            rounded-full
                            bg-accent-300/30
                            blur-3xl

                            dark:bg-neon-cyan/10
                        ">
                    </div>

                    <div class="relative">

                        <span
                            class="
                                inline-flex
                                rounded-full
                                border
                                border-brand-200
                                bg-brand-50
                                px-4 py-2
                                text-xs
                                font-black
                                uppercase
                                tracking-[0.2em]
                                text-brand-700

                                dark:border-brand-400/30
                                dark:bg-brand-400/10
                                dark:text-brand-300
                            ">
                            {{ __('landing.hero.eyebrow') }}
                        </span>

                        <h1
                            class="
                                mx-auto
                                mt-8
                                max-w-4xl
                                text-4xl
                                font-black
                                tracking-tight
                                text-surface-900

                                sm:text-5xl
                                lg:text-7xl

                                dark:text-white
                            ">
                            {{ __('landing.hero.title') }}
                            <span
                                class="
                                    text-brand-600

                                    dark:text-brand-400
                                    dark:drop-shadow-[0_0_18px_rgba(52,211,153,0.35)]
                                ">
                                {{ __('landing.hero.title_highlight') }}
                            </span>
                        </h1>

                        <p
                            class="
                                mx-auto
                                mt-7
                                max-w-2xl
                                text-lg
                                leading-8
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('landing.hero.description') }}
                        </p>


                        {{-- Features mini list --}}
                        <div
                            class="
                                mx-auto
                                mt-10
                                grid
                                max-w-3xl
                                gap-3
                                text-left
                                sm:grid-cols-2
                                lg:grid-cols-4
                            ">

                            @foreach ([
                            __('landing.hero.features.auction'),
                            __('landing.hero.features.squads'),
                            __('landing.hero.features.market'),
                            __('landing.hero.features.competitions'),
                            ] as $feature)

                            <div
                                class="
                                        flex
                                        items-center
                                        gap-3
                                        rounded-xl
                                        bg-surface-50
                                        px-4 py-3
                                        text-sm
                                        font-bold

                                        dark:bg-white/5
                                        dark:ring-1
                                        dark:ring-white/10
                                    ">

                                <span
                                    class="
                                            h-2.5 w-2.5
                                            rounded-full
                                            bg-brand-500

                                            dark:bg-accent-400
                                            dark:shadow-[0_0_10px_rgba(163,230,53,0.8)]
                                        ">
                                </span>

                                {{ $feature }}

                            </div>

                            @endforeach

                        </div>

                    </div>
                </div>


                {{-- LOGIN / REGISTER CARDS --}}
                <div
                    class="
                        mx-auto
                        mt-8
                        grid
                        max-w-5xl
                        gap-6
                        md:grid-cols-2
                    ">

                    <a
                        href="{{ route('register') }}"
                        class="
                            group
                            rounded-3xl
                            border
                            border-surface-200
                            bg-white
                            p-8
                            shadow-sm
                            transition
                            duration-300
                            hover:-translate-y-1
                            hover:border-brand-300
                            hover:shadow-xl

                            dark:border-white/10
                            dark:bg-dark-850
                            dark:hover:border-brand-400/40
                            dark:hover:shadow-[0_0_35px_rgba(52,211,153,0.10)]
                        ">

                        <div
                            class="
                                flex
                                h-12 w-12
                                items-center justify-center
                                rounded-2xl
                                bg-brand-100
                                text-xl
                                font-black
                                text-brand-700

                                dark:bg-brand-400/10
                                dark:text-brand-300
                            ">
                            +
                        </div>

                        <h2
                            class="
                                mt-6
                                text-2xl
                                font-black
                            ">
                            {{ __('landing.auth_cards.register.title') }}
                        </h2>

                        <p
                            class="
                                mt-3
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('landing.auth_cards.register.description') }}
                        </p>

                        <span
                            class="
                                mt-6
                                inline-flex
                                font-bold
                                text-brand-600

                                dark:text-brand-400
                            ">
                            {{ __('landing.auth_cards.register.cta') }} →
                        </span>
                    </a>


                    <a
                        href="{{ route('login') }}"
                        class="
                            group
                            rounded-3xl
                            border
                            border-surface-200
                            bg-white
                            p-8
                            shadow-sm
                            transition
                            duration-300
                            hover:-translate-y-1
                            hover:border-accent-300
                            hover:shadow-xl

                            dark:border-white/10
                            dark:bg-dark-850
                            dark:hover:border-neon-cyan/40
                            dark:hover:shadow-[0_0_35px_rgba(34,211,238,0.08)]
                        ">

                        <div
                            class="
                                flex
                                h-12 w-12
                                items-center justify-center
                                rounded-2xl
                                bg-accent-100
                                text-xl
                                font-black
                                text-accent-700

                                dark:bg-neon-cyan/10
                                dark:text-neon-cyan
                            ">
                            →
                        </div>

                        <h2
                            class="
                                mt-6
                                text-2xl
                                font-black
                            ">
                            {{ __('landing.auth_cards.login.title') }}
                        </h2>

                        <p
                            class="
                                mt-3
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('landing.auth_cards.login.description') }}
                        </p>

                        <span
                            class="
                                mt-6
                                inline-flex
                                font-bold
                                text-surface-900

                                dark:text-neon-cyan
                            ">
                            {{ __('landing.auth_cards.login.cta') }} →
                        </span>
                    </a>

                </div>
            </section>


            {{-- APP DOWNLOAD --}}
            <section
                class="
                    mx-auto
                    max-w-7xl
                    px-6
                    py-16
                    lg:px-8
                ">

                <div
                    class="
                        rounded-[2rem]
                        bg-surface-900
                        px-8 py-12
                        text-white

                        md:flex
                        md:items-center
                        md:justify-between

                        dark:border
                        dark:border-white/10
                        dark:bg-dark-850
                        dark:shadow-[0_0_50px_rgba(167,139,250,0.08)]
                    ">

                    <div>
                        <span
                            class="
                                text-sm
                                font-black
                                uppercase
                                tracking-widest
                                text-brand-400
                            ">
                            {{ __('landing.mobile.eyebrow') }}
                        </span>

                        <h2
                            class="
                                mt-3
                                text-3xl
                                font-black
                            ">
                            {{ __('landing.mobile.title') }}
                        </h2>

                        <p
                            class="
                                mt-3
                                max-w-xl
                                text-surface-300
                            ">
                            {{ __('landing.mobile.description') }}
                        </p>
                    </div>

                    <div
                        class="
                            mt-8
                            flex
                            flex-wrap
                            gap-3

                            md:mt-0
                        ">

                        <div
                            class="
                                rounded-xl
                                border
                                border-white/15
                                bg-white/5
                                px-5 py-3
                                font-bold
                                text-white/70
                            ">
                            {{ __('landing.mobile.android') }}
                        </div>

                        <div
                            class="
                                rounded-xl
                                border
                                border-white/15
                                bg-white/5
                                px-5 py-3
                                font-bold
                                text-white/70
                            ">
                            {{ __('landing.mobile.ios') }}
                        </div>

                    </div>
                </div>
            </section>


            {{-- FEATURES --}}
            <section
                id="come-funziona"
                class="
                    mx-auto
                    max-w-7xl
                    space-y-24
                    px-6
                    py-20
                    lg:px-8
                ">

                {{-- Feature 1 --}}
                <div
                    class="
                        grid
                        items-center
                        gap-12
                        lg:grid-cols-2
                    ">

                    <div
                        class="
                            min-h-80
                            rounded-[2rem]
                            border
                            border-brand-200
                            bg-gradient-to-br
                            from-brand-100
                            to-brand-50

                            dark:border-brand-400/20
                            dark:from-brand-500/10
                            dark:to-dark-850
                        ">
                    </div>

                    <div>
                        <span
                            class="
                                text-sm
                                font-black
                                uppercase
                                tracking-widest
                                text-brand-600

                                dark:text-brand-400
                            ">
                            {{ __('landing.features.auction.eyebrow') }}
                        </span>

                        <h2
                            class="
                                mt-4
                                text-4xl
                                font-black
                                tracking-tight
                            ">
                            {{ __('landing.features.auction.title') }}
                        </h2>

                        <p
                            class="
                                mt-5
                                text-lg
                                leading-8
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('landing.features.auction.description') }}
                        </p>
                    </div>

                </div>


                {{-- Feature 2 --}}
                <div
                    class="
                        grid
                        items-center
                        gap-12
                        lg:grid-cols-2
                    ">

                    <div class="lg:order-2">

                        <div
                            class="
                                min-h-80
                                rounded-[2rem]
                                border
                                border-accent-200
                                bg-gradient-to-br
                                from-accent-100
                                to-white

                                dark:border-neon-cyan/20
                                dark:from-neon-cyan/10
                                dark:to-dark-850
                            ">
                        </div>

                    </div>

                    <div class="lg:order-1">

                        <span
                            class="
                                text-sm
                                font-black
                                uppercase
                                tracking-widest
                                text-accent-700

                                dark:text-neon-cyan
                            ">
                            {{ __('landing.features.squads.eyebrow') }}
                        </span>

                        <h2
                            class="
                                mt-4
                                text-4xl
                                font-black
                                tracking-tight
                            ">
                            {{ __('landing.features.squads.title') }}
                        </h2>

                        <p
                            class="
                                mt-5
                                text-lg
                                leading-8
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('landing.features.squads.description') }}
                        </p>

                    </div>
                </div>


                {{-- Feature 3 --}}
                <div
                    class="
                        grid
                        items-center
                        gap-12
                        lg:grid-cols-2
                    ">

                    <div
                        class="
                            min-h-80
                            rounded-[2rem]
                            border
                            border-violet-200
                            bg-gradient-to-br
                            from-violet-100
                            to-white

                            dark:border-neon-violet/20
                            dark:from-neon-violet/10
                            dark:to-dark-850
                        ">
                    </div>

                    <div>
                        <span
                            class="
                                text-sm
                                font-black
                                uppercase
                                tracking-widest
                                text-violet-600

                                dark:text-neon-violet
                            ">
                            {{ __('landing.features.season.eyebrow') }}
                        </span>

                        <h2
                            class="
                                mt-4
                                text-4xl
                                font-black
                                tracking-tight
                            ">
                            {{ __('landing.features.season.title') }}
                        </h2>

                        <p
                            class="
                                mt-5
                                text-lg
                                leading-8
                                text-surface-600

                                dark:text-surface-300
                            ">
                            {{ __('landing.features.season.description') }}
                        </p>
                    </div>

                </div>

            </section>

            {{-- CLUB SHOWCASE --}}
            <x-clubs.showcase />


            {{-- FINAL CTA --}}
            <section
                class="
                    mx-auto
                    max-w-7xl
                    px-6
                    py-20
                    lg:px-8
                ">

                <div
                    class="
                        overflow-hidden
                        rounded-[2rem]
                        bg-brand-600
                        px-8 py-16
                        text-center
                        text-white

                        dark:bg-dark-850
                        dark:ring-1
                        dark:ring-brand-400/20
                        dark:shadow-[0_0_70px_rgba(52,211,153,0.10)]
                    ">

                    <h2
                        class="
                            text-4xl
                            font-black
                            tracking-tight
                        ">
                        {{ __('landing.final_cta.title') }}
                    </h2>

                    <p
                        class="
                            mx-auto
                            mt-4
                            max-w-xl
                            text-brand-100

                            dark:text-surface-300
                        ">
                        {{ __('landing.final_cta.description') }}
                    </p>

                    <a
                        href="{{ route('register') }}"
                        class="
                            mt-8
                            inline-flex
                            rounded-xl
                            bg-white
                            px-6 py-3
                            font-black
                            text-brand-700
                            transition
                            hover:-translate-y-0.5

                            dark:bg-accent-400
                            dark:text-dark-950
                            dark:shadow-[0_0_25px_rgba(163,230,53,0.25)]
                        ">
                        {{ __('landing.final_cta.button') }}
                    </a>

                </div>
            </section>

        </main>


        {{-- FOOTER --}}
        <footer
            id="contatti"
            class="
                border-t
                border-surface-200

                dark:border-white/10
            ">

            <div
                class="
                    mx-auto
                    flex
                    max-w-7xl
                    flex-col
                    gap-6
                    px-6 py-10

                    md:flex-row
                    md:items-center
                    md:justify-between

                    lg:px-8
                ">

                <span class="font-black">
                    Fantacalcio
                </span>

                <div
                    class="
                        flex
                        flex-wrap
                        gap-6
                        text-sm
                        text-surface-600

                        dark:text-surface-400
                    ">

                    <a href="#faq">
                        {{ __('landing.footer.faq') }}
                    </a>

                    <a href="#contatti">
                        {{ __('landing.footer.contacts') }}
                    </a>

                    <span>
                        {{ __('landing.footer.privacy') }}
                    </span>

                    <span>
                        {{ __('landing.footer.terms') }}
                    </span>

                </div>

            </div>
        </footer>

    </div>
</x-layouts.public>