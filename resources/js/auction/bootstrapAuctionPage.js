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
                countdownElement.textContent = String(
                    Math.ceil(
                        remainingMilliseconds / 1000
                    )
                );
            }
        );
    }

    const snapshot = await client.loadSnapshot();

    renderSnapshot(snapshot);

    client.subscribe();

    return client;
}
