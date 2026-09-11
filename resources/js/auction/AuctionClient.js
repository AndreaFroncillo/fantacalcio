export default class AuctionClient {
    constructor(auctionUlid, echo = null) {
        if (!auctionUlid) {
            throw new Error('Auction ULID is required.');
        }

        this.auctionUlid = auctionUlid;
        this.echo = echo;
        this.state = null;
        this.nominationRemainingMilliseconds = 0;
        this.nominationCountdownIntervalId = null;
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

        this.updateNominationCountdown();

        return this.state;
    }

    async resync() {
        return this.loadSnapshot();
    }

    subscribe() {
        if (!this.echo) {
            throw new Error('Echo instance is required.');
        }

        const channel = this.echo.private(
            `auction.${this.auctionUlid}`
        );

        let shouldResyncOnConnect = false;

        this.echo.connector.pusher.connection.bind(
            'state_change',
            async ({ previous, current }) => {
                if (
                    previous === 'connected' &&
                    current !== 'connected'
                ) {
                    shouldResyncOnConnect = true;

                    return;
                }

                if (
                    current === 'connected' &&
                    shouldResyncOnConnect
                ) {
                    shouldResyncOnConnect = false;

                    try {
                        await this.resync();
                    } catch {
                        return;
                    }
                }
            }
        );

        channel.listen(
            '.auction.started',
            async () => {
                await this.loadSnapshot();
            }
        );

        channel.listen(
            '.auction.nomination.started',
            async () => {
                await this.loadSnapshot();
            }
        );

        channel.listen(
            '.auction.bid.placed',
            async () => {
                await this.loadSnapshot();
            }
        );

        channel.listen(
            '.auction.nomination.finalized',
            async () => {
                await this.loadSnapshot();
            }
        );

        channel.listen(
            '.auction.role-phase.advanced',
            async () => {
                await this.loadSnapshot();
            }
        );

        channel.listen(
            '.auction.completed',
            async () => {
                await this.loadSnapshot();
            }
        );

        return channel;
    }

    getRemainingNominationMilliseconds(now = Date.now()) {
        const expiresAt =
            this.state?.active_nomination?.expires_at;

        if (!expiresAt) {
            return 0;
        }

        return Math.max(
            0,
            new Date(expiresAt).getTime() - now
        );
    }

    updateNominationCountdown(now = Date.now()) {
        this.nominationRemainingMilliseconds =
            this.getRemainingNominationMilliseconds(now);

        return this.nominationRemainingMilliseconds;
    }

    startNominationCountdown() {
        if (this.nominationCountdownIntervalId !== null) {
            return this.nominationCountdownIntervalId;
        }

        this.nominationCountdownIntervalId = setInterval(
            () => {
                const remainingMilliseconds =
                    this.updateNominationCountdown();

                if (remainingMilliseconds === 0) {
                    this.stopNominationCountdown();
                }
            },
            1000
        );

        return this.nominationCountdownIntervalId;
    }

    stopNominationCountdown() {
        clearInterval(
            this.nominationCountdownIntervalId
        );

        this.nominationCountdownIntervalId = null;
    }

    getState() {
        return this.state;
    }
}
