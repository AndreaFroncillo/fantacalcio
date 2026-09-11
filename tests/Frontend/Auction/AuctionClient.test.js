import test from 'node:test';
import assert from 'node:assert/strict';

import AuctionClient from '../../../resources/js/auction/AuctionClient.js';

test('it requires an auction ulid', () => {
    assert.throws(
        () => new AuctionClient(),
        {
            message: 'Auction ULID is required.',
        }
    );
});

test('initial state is null', () => {
    const client = new AuctionClient('01TESTAUCTIONULID');

    assert.equal(client.getState(), null);
});

test('it loads and stores auction snapshot', async () => {
    const originalFetch = global.fetch;

    const snapshot = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_role_phase: null,
        active_nomination: null,
        participants: [],
    };

    global.fetch = async (url, options) => {
        assert.equal(
            url,
            '/api/auctions/01TESTAUCTIONULID'
        );

        assert.deepEqual(options, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        });

        return {
            ok: true,
            async json() {
                return {
                    data: snapshot,
                };
            },
        };
    };

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        const result = await client.loadSnapshot();

        assert.deepEqual(result, snapshot);
        assert.deepEqual(client.getState(), snapshot);
    } finally {
        global.fetch = originalFetch;
    }
});

test('it fails when snapshot request is not successful', async () => {
    const originalFetch = global.fetch;

    global.fetch = async () => ({
        ok: false,
        status: 403,
    });

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        await assert.rejects(
            () => client.loadSnapshot(),
            {
                message:
                    'Unable to load auction snapshot (403).',
            }
        );

        assert.equal(client.getState(), null);
    } finally {
        global.fetch = originalFetch;
    }
});

test('it fails when snapshot response does not contain data', async () => {
    const originalFetch = global.fetch;

    global.fetch = async () => ({
        ok: true,
        async json() {
            return {};
        },
    });

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        await assert.rejects(
            () => client.loadSnapshot(),
            {
                message:
                    'Auction snapshot response does not contain data.',
            }
        );

        assert.equal(client.getState(), null);
    } finally {
        global.fetch = originalFetch;
    }
});

test('it subscribes to the auction private channel', () => {
    let subscribedChannel = null;

    const channel = {
        listen() {
            return this;
        },
    };

    const echo = {
        private(channelName) {
            subscribedChannel = channelName;

            return channel;
        },
    };

    const client = new AuctionClient(
        '01TESTAUCTIONULID',
        echo
    );

    const result = client.subscribe();

    assert.equal(
        subscribedChannel,
        'auction.01TESTAUCTIONULID'
    );

    assert.equal(result, channel);
});

test('it reloads the snapshot when auction started is received', async () => {
    const originalFetch = global.fetch;

    let listenedEvent = null;
    let eventHandler = null;

    const channel = {
        listen(eventName, callback) {
            listenedEvent = eventName;
            eventHandler = callback;

            return this;
        },
    };

    const echo = {
        private() {
            return channel;
        },
    };

    const snapshot = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_role_phase: {
            ulid: '01TESTPHASEULID',
            role: 'P',
            position: 1,
            status: 'active',
        },
        active_nomination: null,
        participants: [],
    };

    global.fetch = async () => ({
        ok: true,
        async json() {
            return {
                data: snapshot,
            };
        },
    });

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID',
            echo
        );

        client.subscribe();

        assert.equal(
            listenedEvent,
            '.auction.started'
        );

        assert.equal(
            typeof eventHandler,
            'function'
        );

        await eventHandler({
            auction_ulid: '01TESTAUCTIONULID',
            status: 'live',
            started_at: '2026-09-11T07:00:00.000000Z',
            active_role_phase_ulid: '01TESTPHASEULID',
            active_role: 'P',
        });

        assert.deepEqual(
            client.getState(),
            snapshot
        );
    } finally {
        global.fetch = originalFetch;
    }
});
