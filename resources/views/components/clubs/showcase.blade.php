@php
$clubs = [
[
'name' => 'Inter',
'competition' => 'Serie A',
'competition_key' => 'serie-a',
'country' => 'IT',
'initials' => 'INT',
'players' => [
        'P' => [
            'Sommer',
            'Martínez',
            'Di Gennaro',
        ],
        'D' => [
            'Acerbi',
            'Bastoni',
            'Bisseck',
            'Carlos Augusto',
            'Darmian',
            'de Vrij',
            'Dimarco',
            'Pavard',
        ],
        'C' => [
            'Barella',
            'Çalhanoğlu',
            'Frattesi',
            'Mkhitaryan',
            'Zieliński',
        ],
        'A' => [
            'Lautaro Martínez',
            'Thuram',
            'Taremi',
            'Arnautović',
        ],
    ],
],
[
'name' => 'Milan',
'competition' => 'Serie A',
'competition_key' => 'serie-a',
'country' => 'IT',
'initials' => 'MIL',
],
[
'name' => 'Juventus',
'competition' => 'Serie A',
'competition_key' => 'serie-a',
'country' => 'IT',
'initials' => 'JUV',
],
[
'name' => 'Manchester City',
'competition' => 'Premier League',
'competition_key' => 'premier-league',
'country' => 'EN',
'initials' => 'MCI',
],
[
'name' => 'Liverpool',
'competition' => 'Premier League',
'competition_key' => 'premier-league',
'country' => 'EN',
'initials' => 'LIV',
],
[
'name' => 'Arsenal',
'competition' => 'Premier League',
'competition_key' => 'premier-league',
'country' => 'EN',
'initials' => 'ARS',
],
[
'name' => 'Real Madrid',
'competition' => 'LaLiga',
'competition_key' => 'laliga',
'country' => 'ES',
'initials' => 'RMA',
],
[
'name' => 'Barcelona',
'competition' => 'LaLiga',
'competition_key' => 'laliga',
'country' => 'ES',
'initials' => 'BAR',
],
[
'name' => 'Bayern Monaco',
'competition' => 'Bundesliga',
'competition_key' => 'bundesliga',
'country' => 'DE',
'initials' => 'BAY',
],
[
'name' => 'Borussia Dortmund',
'competition' => 'Bundesliga',
'competition_key' => 'bundesliga',
'country' => 'DE',
'initials' => 'BVB',
],
[
'name' => 'Paris Saint-Germain',
'competition' => 'Ligue 1',
'competition_key' => 'ligue-1',
'country' => 'FR',
'initials' => 'PSG',
],
[
'name' => 'Marseille',
'competition' => 'Ligue 1',
'competition_key' => 'ligue-1',
'country' => 'FR',
'initials' => 'OM',
],
];

$competitions = [
[
'value' => 'all',
'label' => __('landing.clubs.filters.all'),
],
[
'value' => 'serie-a',
'label' => 'Serie A',
],
[
'value' => 'premier-league',
'label' => 'Premier League',
],
[
'value' => 'laliga',
'label' => 'LaLiga',
],
[
'value' => 'bundesliga',
'label' => 'Bundesliga',
],
[
'value' => 'ligue-1',
'label' => 'Ligue 1',
],
];
@endphp

<section
    id="clubs"
    data-clubs-showcase
    class="
        relative
        border-y border-surface-200
        bg-white py-20

        dark:border-white/10
        dark:bg-dark-900
    ">

    <div class="mx-auto max-w-7xl px-6 lg:px-8">

        <div class="max-w-3xl">
            <p
                class="
                    text-sm font-black
                    uppercase tracking-[0.25em]
                    text-brand-600

                    dark:text-accent-400
                ">
                {{ __('landing.clubs.eyebrow') }}
            </p>

            <h2
                class="
                    mt-4
                    text-3xl font-black tracking-tight
                    text-surface-900
                    sm:text-4xl

                    dark:text-white
                ">
                {{ __('landing.clubs.title') }}
            </h2>

            <p
                class="
                    mt-4
                    text-base leading-7
                    text-surface-600

                    dark:text-surface-300
                ">
                {{ __('landing.clubs.description') }}
            </p>
        </div>

        {{-- Competition pills --}}
        <div
            class="
                mt-8
                flex gap-2
                overflow-x-auto
                pb-2
                [scrollbar-width:none]
                [&::-webkit-scrollbar]:hidden
            ">

            @foreach ($competitions as $competition)
            <button
                type="button"
                data-club-filter
                data-competition="{{ $competition['value'] }}"
                aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                class="
                    shrink-0
                    rounded-full
                    border
                    px-4 py-2
                    text-sm font-bold
                    transition

                    {{ $loop->first
                        ? 'border-brand-500 bg-brand-50 text-brand-700 dark:border-accent-400/50 dark:bg-accent-400/10 dark:text-accent-400'
                        : 'border-surface-200 bg-surface-50 text-surface-700 hover:border-brand-400 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-surface-200 dark:hover:border-accent-400/50 dark:hover:text-accent-400'
                    }}">
                {{ $competition['label'] }}
            </button>
            @endforeach

        </div>

        {{-- Clubs horizontal scroll --}}
        <div
            data-clubs-track
            class="
                mt-8 pt-2
                flex snap-x snap-mandatory
                gap-4
                overflow-x-auto
                pb-6
                [scrollbar-width:none]
                [&::-webkit-scrollbar]:hidden
            ">

            @foreach ($clubs as $club)
            <x-clubs.club-card :club="$club" />
            @endforeach

        </div>

        <p
            class="
                mt-2
                text-sm
                text-surface-500

                dark:text-surface-400
            ">
            {{ __('landing.clubs.hint') }}
        </p>

    </div>

    <x-clubs.club-preview />

</section>