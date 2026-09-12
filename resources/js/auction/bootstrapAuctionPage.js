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

    const renderSnapshot = (snapshot) => {
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

            participantsElement.innerHTML =
                participants
                    .map((participant) => {
                        const team =
                            participant.team;

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
                        <span>${team.name}</span>
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

    await client.loadSnapshot();

    client.subscribe();

    return client;
}
