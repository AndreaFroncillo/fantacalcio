const DESKTOP_BREAKPOINT = 1024;
const PREVIEW_WIDTH = 288;
const PREVIEW_GAP = 12;
const VIEWPORT_PADDING = 16;
const CLOSE_DELAY = 150;

export const initializeClubPreview = (
    showcase
) => {
    const preview = showcase.querySelector(
        '[data-club-preview]'
    );

    const cards = showcase.querySelectorAll(
        '[data-club-card]'
    );

    if (!preview || !cards.length) {
        return;
    }

    const nameElement = preview.querySelector(
        '[data-club-preview-name]'
    );

    const competitionElement = preview.querySelector(
        '[data-club-preview-competition]'
    );

    const playersElement = preview.querySelector(
        '[data-club-preview-players]'
    );

    const roleLabels = {
        P: 'P',
        D: 'D',
        C: 'C',
        A: 'A',
    };

    const renderPlayers = (card) => {
        if (!playersElement) {
            return;
        }

        let players = {};

        try {
            players = JSON.parse(
                card.dataset.clubPlayers ?? '{}'
            );
        } catch {
            players = {};
        }

        playersElement.replaceChildren();

        Object.entries(roleLabels).forEach(
            ([role, label]) => {
                const rolePlayers =
                    players[role] ?? [];

                if (!rolePlayers.length) {
                    return;
                }

                const row =
                    document.createElement('div');

                row.className =
                    'flex items-start gap-3';

                const badge =
                    document.createElement('span');

                badge.className =
                    'flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-black text-brand-700 dark:bg-accent-400/10 dark:text-accent-400';

                badge.textContent = label;

                const names =
                    document.createElement('p');

                names.className =
                    'pt-1 text-xs leading-5 text-surface-600 dark:text-surface-300';

                names.textContent =
                    rolePlayers.join(', ');

                row.append(
                    badge,
                    names
                );

                playersElement.append(row);
            }
        );
    };

    const hidePreview = () => {
        preview.classList.add(
            'opacity-0'
        );

        preview.setAttribute(
            'aria-hidden',
            'true'
        );
    };

    const showPreview = (card) => {
        if (
            window.innerWidth <
            DESKTOP_BREAKPOINT
        ) {
            return;
        }

        preview.style.maxHeight = '';
        preview.style.overflowY = '';

        nameElement.textContent =
            card.dataset.clubName ?? '';

        competitionElement.textContent =
            card.dataset.clubCompetition ?? '';

        renderPlayers(card);

        const cardRect =
            card.getBoundingClientRect();

        const showcaseRect =
            showcase.getBoundingClientRect();

        const previewWidth =
            preview.offsetWidth;

        const previewHeight =
            preview.offsetHeight;

        let left =
            cardRect.left -
            showcaseRect.left +
            cardRect.width / 2 -
            previewWidth / 2;

        left = Math.max(
            VIEWPORT_PADDING,
            Math.min(
                left,
                showcaseRect.width -
                previewWidth -
                VIEWPORT_PADDING
            )
        );

        const spaceAbove =
            cardRect.top - VIEWPORT_PADDING;

        const spaceBelow =
            window.innerHeight -
            cardRect.bottom -
            VIEWPORT_PADDING;

        const fitsAbove =
            previewHeight + PREVIEW_GAP <= spaceAbove;

        const fitsBelow =
            previewHeight + PREVIEW_GAP <= spaceBelow;

        let top;

        if (fitsAbove) {
            top =
                cardRect.top -
                showcaseRect.top -
                previewHeight -
                PREVIEW_GAP;
        } else if (fitsBelow) {
            top =
                cardRect.bottom -
                showcaseRect.top +
                PREVIEW_GAP;
        } else {
            const placeAbove =
                spaceAbove >= spaceBelow;

            const availableSpace =
                Math.max(
                    placeAbove
                        ? spaceAbove
                        : spaceBelow,
                    200
                );

            preview.style.maxHeight =
                `${availableSpace - PREVIEW_GAP}px`;

            preview.style.overflowY =
                'auto';

            if (placeAbove) {
                top =
                    cardRect.top -
                    showcaseRect.top -
                    Math.min(
                        previewHeight,
                        availableSpace - PREVIEW_GAP
                    ) -
                    PREVIEW_GAP;
            } else {
                top =
                    cardRect.bottom -
                    showcaseRect.top +
                    PREVIEW_GAP;
            }
        }

        preview.style.left = `${left}px`;
        preview.style.top = `${top}px`;

        preview.classList.remove(
            'opacity-0'
        );

        preview.setAttribute(
            'aria-hidden',
            'false'
        );
    };

    let closeTimeout = null;

    const cancelClose = () => {
        if (!closeTimeout) {
            return;
        }

        clearTimeout(closeTimeout);
        closeTimeout = null;
    };

    const scheduleClose = () => {
        cancelClose();

        closeTimeout = setTimeout(() => {
            hidePreview();
            closeTimeout = null;
        }, CLOSE_DELAY);
    };

    cards.forEach((card) => {
        card.addEventListener(
            'mouseenter',
            () => {
                cancelClose();
                showPreview(card);
            }
        );

        card.addEventListener(
            'mouseleave',
            scheduleClose
        );

        card.addEventListener(
            'focus',
            () => {
                cancelClose();
                showPreview(card);
            }
        );

        card.addEventListener(
            'blur',
            scheduleClose
        );
    });

    preview.addEventListener(
        'mouseenter',
        cancelClose
    );

    preview.addEventListener(
        'mouseleave',
        scheduleClose
    );
};