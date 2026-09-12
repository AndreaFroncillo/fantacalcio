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

    await client.loadSnapshot();

    client.subscribe();

    return client;
}
