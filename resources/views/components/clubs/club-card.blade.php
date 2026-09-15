@props([
'club',
])

<article
    data-club-card
    data-competition="{{ $club['competition_key'] }}"
    data-club-name="{{ $club['name'] }}"
    data-club-competition="{{ $club['competition'] }}"
    data-club-players='@json($club['players'] ?? [])'
    tabindex="0"
    class="
        group
        relative
        w-40
        shrink-0
        snap-start
        cursor-pointer
        rounded-3xl
        border border-surface-200
        bg-white
        p-5
        text-center
        shadow-[0_2px_8px_rgba(100,116,139,0.10)]
        transition
        duration-300
        hover:-translate-y-1
        hover:border-brand-300
        hover:shadow-[0_10px_30px_rgba(100,116,139,0.18)]

        dark:border-white/10
        dark:bg-dark-850
        dark:hover:border-accent-400/40
        dark:hover:shadow-[0_0_30px_rgba(163,230,53,0.10)]
    ">

    <div
        class="
            mx-auto
            flex h-20 w-20
            items-center justify-center
            rounded-2xl
            bg-surface-100
            text-lg font-black
            text-brand-700
            ring-1 ring-inset ring-surface-200

            transition
            duration-300
            group-hover:scale-105

            dark:bg-white/5
            dark:text-accent-400
            dark:ring-white/10
        ">
        {{ $club['initials'] }}
    </div>

    <h3
        class="
            mt-4
            truncate
            text-sm font-black
            text-surface-900

            dark:text-white
        ">
        {{ $club['name'] }}
    </h3>

    <p
        class="
            mt-1
            truncate
            text-xs font-semibold
            text-surface-500

            dark:text-surface-400
        ">
        {{ $club['competition'] }}
    </p>

</article>