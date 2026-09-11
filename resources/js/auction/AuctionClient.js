export default class AuctionClient {
    constructor(auctionUlid, echo = null) {
        if (!auctionUlid) {
            throw new Error('Auction ULID is required.');
        }

        this.auctionUlid = auctionUlid;
        this.echo = echo;
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

    subscribe() {
        if (!this.echo) {
            throw new Error('Echo instance is required.');
        }

        const channel = this.echo.private(
            `auction.${this.auctionUlid}`
        );

        channel.listen(
            '.auction.started',
            async () => {
                await this.loadSnapshot();
            }
        );

        return channel;
    }

    getState() {
        return this.state;
    }
}
