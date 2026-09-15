export const initializeCompetitionFilter = (
    showcase
) => {
    const filterButtons = showcase.querySelectorAll(
        '[data-club-filter]'
    );

    const clubCards = showcase.querySelectorAll(
        '[data-club-card]'
    );

    if (!filterButtons.length || !clubCards.length) {
        return;
    }

    const activeClasses = [
        'border-brand-500',
        'bg-brand-50',
        'text-brand-700',
        'dark:border-accent-400/50',
        'dark:bg-accent-400/10',
        'dark:text-accent-400',
    ];

    const inactiveClasses = [
        'border-surface-200',
        'bg-surface-50',
        'text-surface-700',
        'hover:border-brand-400',
        'hover:text-brand-700',
        'dark:border-white/10',
        'dark:bg-white/5',
        'dark:text-surface-200',
        'dark:hover:border-accent-400/50',
        'dark:hover:text-accent-400',
    ];

    const updateButtonState = (
        activeButton
    ) => {
        filterButtons.forEach((button) => {
            const isActive =
                button === activeButton;

            button.setAttribute(
                'aria-pressed',
                String(isActive)
            );

            if (isActive) {
                button.classList.remove(
                    ...inactiveClasses
                );

                button.classList.add(
                    ...activeClasses
                );

                return;
            }

            button.classList.remove(
                ...activeClasses
            );

            button.classList.add(
                ...inactiveClasses
            );
        });
    };

    const filterClubs = (competition) => {
        clubCards.forEach((card) => {
            const isVisible =
                competition === 'all' ||
                card.dataset.competition === competition;

            card.classList.toggle(
                'hidden',
                !isVisible
            );
        });
    };

    filterButtons.forEach((button) => {
        button.addEventListener(
            'click',
            () => {
                const competition =
                    button.dataset.competition;

                updateButtonState(button);
                filterClubs(competition);
                showcase.querySelector(
                    '[data-clubs-track]'
                )?.scrollTo({
                    left: 0,
                    behavior: 'smooth',
                });
            }
        );
    });
};