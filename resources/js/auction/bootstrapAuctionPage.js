import AuctionClient from './AuctionClient.js';

export async function bootstrapAuctionPage({
    element,
    echo,
    AuctionClientClass = AuctionClient,
}) {
    const auctionUlid = element.dataset.auctionUlid;

    const client = new AuctionClientClass(
        auctionUlid,
        echo
    );

    let currentSnapshot = null;

    let isBidPending = false;

    let isPresidentModerationPending = false;

    const escapeHtml = (value) => {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    const renderSnapshot = (snapshot) => {
        currentSnapshot = snapshot;

        const bidErrorElement = element.querySelector(
            '[data-auction-bid-error]'
        );

        if (
            bidErrorElement &&
            !snapshot.active_nomination
        ) {
            bidErrorElement.textContent = '';
        }

        const statusElement = element.querySelector(
            '[data-auction-status]'
        );

        if (statusElement) {
            statusElement.textContent =
                snapshot.status ?? '';
        }

        const roleElement = element.querySelector(
            '[data-auction-role]'
        );

        if (roleElement) {
            roleElement.textContent =
                snapshot.active_role_phase?.role ?? '';
        }

        const playerElement = element.querySelector(
            '[data-auction-player]'
        );

        if (playerElement) {
            playerElement.textContent =
                snapshot.active_nomination?.player?.display_name ?? '';
        }

        const currentBidElement = element.querySelector(
            '[data-auction-current-bid]'
        );

        if (currentBidElement) {
            const currentBidAmount =
                snapshot.active_nomination?.current_bid?.amount;

            currentBidElement.textContent =
                currentBidAmount !== undefined &&
                    currentBidAmount !== null
                    ? String(currentBidAmount)
                    : '';
        }

        const participantsElement = element.querySelector(
            '[data-auction-participants]'
        );

        if (participantsElement) {
            const participants =
                snapshot.participants ?? [];

            if (participants.length === 0) {
                participantsElement.innerHTML =
                    '<p>Nessun partecipante</p>';
            } else {
                participantsElement.innerHTML =
                    participants
                        .map((participant) => {
                            const team =
                                participant.team ?? {
                                    name: 'Squadra non disponibile',
                                    current_balance: '',
                                };

                            const isCurrentNominator =
                                snapshot.active_nomination
                                    ?.nominator
                                    ?.ulid === participant.ulid;

                            const isCurrentHighestBidder =
                                snapshot.active_nomination
                                    ?.current_bid
                                    ?.participant_ulid === participant.ulid;

                            return `
            <div>
                <span>${escapeHtml(team.name)}</span>
                <span>${team.current_balance}</span>
                <span>
                    Posizione ${participant.nomination_position}
                </span>
                ${isCurrentNominator
                                    ? '<span>Nominatore corrente</span>'
                                    : ''
                                }
                ${isCurrentHighestBidder
                                    ? '<span>Miglior offerente</span>'
                                    : ''
                                }
            </div>
        `;
                        })
                        .join('');
            }
        }

        const bidControlsElement = element.querySelector(
            '[data-auction-bid-controls]'
        );

        if (bidControlsElement) {
            const currentParticipantUlid =
                snapshot.current_participant_ulid;

            const currentBidParticipantUlid =
                snapshot.active_nomination
                    ?.current_bid
                    ?.participant_ulid;

            if (
                !snapshot.active_nomination ||
                currentParticipantUlid === null ||
                (
                    currentParticipantUlid &&
                    currentBidParticipantUlid ===
                    currentParticipantUlid
                )
            ) {
                bidControlsElement.innerHTML = '';

                return;
            }

            bidControlsElement.innerHTML = `
        <button
            type="button"
            data-bid-increment="1"
        >
            +1
        </button>

        <button
            type="button"
            data-bid-increment="5"
        >
            +5
        </button>

        <button
            type="button"
            data-bid-increment="10"
        >
            +10
        </button>
    `;
        }

        const presidentControlsElement = element.querySelector(
            '[data-auction-president-controls]'
        );

        if (presidentControlsElement) {
            if (!snapshot.active_nomination) {
                presidentControlsElement.innerHTML = '';
            } else {
                const canConfirm =
                    snapshot.permissions
                        ?.can_confirm_nomination === true;

                const canReject =
                    snapshot.permissions
                        ?.can_reject_nomination === true;

                presidentControlsElement.innerHTML = `
            ${canConfirm
                        ? '<button type="button" data-auction-confirm-nomination>Conferma</button>'
                        : ''
                    }
            ${canReject
                        ? '<button type="button" data-auction-reject-nomination>Rifiuta</button>'
                        : ''
                    }
        `;
            }
        }
    };

    client.setSnapshotListener(
        (snapshot) => {
            renderSnapshot(snapshot);
        }
    );

    const countdownElement = element.querySelector(
        '[data-auction-countdown]'
    );

    if (countdownElement) {
        client.setNominationCountdownListener(
            (remainingMilliseconds) => {
                countdownElement.textContent =
                    remainingMilliseconds > 0
                        ? String(
                            Math.ceil(
                                remainingMilliseconds / 1000
                            )
                        )
                        : '';
            }
        );
    }

    const bidErrorElement = element.querySelector(
        '[data-auction-bid-error]'
    );

    const bidControlsElement = element.querySelector(
        '[data-auction-bid-controls]'
    );

    if (bidControlsElement) {
        bidControlsElement.addEventListener(
            'click',
            async (event) => {
                const increment =
                    Number(
                        event.target
                            ?.dataset
                            ?.bidIncrement
                    );

                if (!increment) {
                    return;
                }

                if (isBidPending) {
                    return;
                }

                const activeNomination =
                    currentSnapshot
                        ?.active_nomination;

                if (!activeNomination) {
                    return;
                }

                const currentParticipantUlid =
                    currentSnapshot
                        ?.current_participant_ulid;

                if (currentParticipantUlid === null) {
                    return;
                }

                const currentBidParticipantUlid =
                    activeNomination
                        .current_bid
                        ?.participant_ulid;

                if (
                    currentParticipantUlid &&
                    currentBidParticipantUlid ===
                    currentParticipantUlid
                ) {
                    return;
                }

                const currentBidAmount =
                    activeNomination
                        .current_bid
                        ?.amount;

                const amount =
                    currentBidAmount !== undefined &&
                        currentBidAmount !== null
                        ? currentBidAmount + increment
                        : Math.max(
                            activeNomination.opening_price,
                            increment
                        );

                if (bidErrorElement) {
                    bidErrorElement.textContent = '';
                }

                const bidButtons =
                    bidControlsElement.querySelectorAll?.(
                        '[data-bid-increment]'
                    ) ?? [];

                bidButtons.forEach((button) => {
                    button.disabled = true;
                });

                isBidPending = true;

                try {
                    await client.placeBid(
                        activeNomination.ulid,
                        amount
                    );
                } catch (error) {
                    if (bidErrorElement) {
                        bidErrorElement.textContent =
                            error instanceof Error
                                ? error.message
                                : 'Unable to place auction bid.';
                    }
                } finally {
                    bidButtons.forEach((button) => {
                        button.disabled = false;
                    });

                    isBidPending = false;
                }
            }
        );
    }

    const presidentErrorElement = element.querySelector(
        '[data-auction-president-error]'
    );

    const presidentControlsElement = element.querySelector(
        '[data-auction-president-controls]'
    );

    if (presidentControlsElement) {
        presidentControlsElement.addEventListener(
            'click',
            async (event) => {
                const isConfirmAction =
                    event.target
                        ?.dataset
                        ?.auctionConfirmNomination !== undefined;

                const isRejectAction =
                    event.target
                        ?.dataset
                        ?.auctionRejectNomination !== undefined;

                if (
                    !isConfirmAction &&
                    !isRejectAction
                ) {
                    return;
                }

                if (isPresidentModerationPending) {
                    return;
                }

                const activeNomination =
                    currentSnapshot
                        ?.active_nomination;

                if (!activeNomination) {
                    return;
                }

                if (presidentErrorElement) {
                    presidentErrorElement.textContent = '';
                }

                const presidentButtons =
                    presidentControlsElement.querySelectorAll?.(
                        '[data-auction-confirm-nomination], [data-auction-reject-nomination]'
                    ) ?? [];

                presidentButtons.forEach((button) => {
                    button.disabled = true;
                });

                isPresidentModerationPending = true;

                try {
                    if (isConfirmAction) {
                        await client.confirmNomination(
                            activeNomination.ulid
                        );

                        return;
                    }

                    await client.rejectNomination(
                        activeNomination.ulid
                    );
                } catch (error) {
                    if (presidentErrorElement) {
                        presidentErrorElement.textContent =
                            error instanceof Error
                                ? error.message
                                : 'Unable to moderate auction nomination.';
                    }
                } finally {
                    presidentButtons.forEach((button) => {
                        button.disabled = false;
                    });

                    isPresidentModerationPending = false;
                }
            }
        );
    }

    await client.loadSnapshot();

    client.subscribe();

    return client;
}
