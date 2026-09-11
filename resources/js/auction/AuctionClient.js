export default class AuctionClient {
    constructor(auctionUlid) {
        if (!auctionUlid) {
            throw new Error('Auction ULID is required.');
        }

        this.auctionUlid = auctionUlid;
        this.state = null;
    }

    async loadSnapshot() {
        const response = await fetch(
            `/api/auctions/${this.auctionUlid}`,
            {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            }
        );

        if (!response.ok) {
            throw new Error(
                `Unable to load auction snapshot (${response.status}).`
            );
        }

        const payload = await response.json();

        if (!payload.data) {
            throw new Error(
                'Auction snapshot response does not contain data.'
            );
        }

        this.state = payload.data;

        return this.state;
    }

    getState() {
        return this.state;
    }
}
