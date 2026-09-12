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

    const snapshot = await client.loadSnapshot();

    const statusElement = element.querySelector(
        '[data-auction-status]'
    );

    if (statusElement) {
        statusElement.textContent = snapshot.status;
    }

    const roleElement = element.querySelector(
        '[data-auction-role]'
    );

    if (roleElement) {
        roleElement.textContent =
            snapshot.active_role_phase?.role ?? '';
    }

    client.subscribe();

    return client;
}
