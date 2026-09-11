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

    const listeners = {};

    const channel = {
        listen(eventName, callback) {
            listeners[eventName] = callback;

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
            typeof listeners['.auction.started'],
            'function'
        );

        await listeners['.auction.started']({
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

test('it reloads the snapshot when auction nomination started is received', async () => {
    const originalFetch = global.fetch;

    const listeners = {};

    const channel = {
        listen(eventName, callback) {
            listeners[eventName] = callback;

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
        active_nomination: {
            ulid: '01TESTNOMINATIONULID',
            player_season_ulid: '01TESTPLAYERSEASONULID',
            turn_number: 1,
            opening_price: 1,
            status: 'active',
            timer_started_at: '2026-09-11T10:00:00.000000Z',
            expires_at: '2026-09-11T10:01:00.000000Z',
            player: {},
            nominator: {
                ulid: '01TESTPARTICIPANTULID',
                nomination_position: 1,
            },
            current_bid: null,
        },
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
            typeof listeners['.auction.nomination.started'],
            'function'
        );

        await listeners['.auction.nomination.started']({
            auction_ulid: '01TESTAUCTIONULID',
            nomination_ulid: '01TESTNOMINATIONULID',
            player_season_ulid: '01TESTPLAYERSEASONULID',
            auction_participant_ulid: '01TESTPARTICIPANTULID',
            turn_number: 1,
            opening_price: 1,
            timer_started_at: '2026-09-11T10:00:00.000000Z',
            expires_at: '2026-09-11T10:01:00.000000Z',
        });

        assert.deepEqual(
            client.getState(),
            snapshot
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it reloads the snapshot when auction bid placed is received', async () => {
    const originalFetch = global.fetch;

    const listeners = {};

    const channel = {
        listen(eventName, callback) {
            listeners[eventName] = callback;

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
        active_nomination: {
            ulid: '01TESTNOMINATIONULID',
            player_season_ulid: '01TESTPLAYERSEASONULID',
            turn_number: 1,
            opening_price: 1,
            status: 'active',
            timer_started_at: '2026-09-11T10:00:00.000000Z',
            expires_at: '2026-09-11T10:01:10.000000Z',
            player: {},
            nominator: {
                ulid: '01TESTPARTICIPANTULID',
                nomination_position: 1,
            },
            current_bid: {
                ulid: '01TESTBIDULID',
                amount: 10,
                sequence_number: 1,
                participant_ulid: '01TESTPARTICIPANTULID',
                placed_at: '2026-09-11T10:00:20.000000Z',
            },
        },
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
            typeof listeners['.auction.bid.placed'],
            'function'
        );

        await listeners['.auction.bid.placed']({
            auction_ulid: '01TESTAUCTIONULID',
            nomination_ulid: '01TESTNOMINATIONULID',
            bid_ulid: '01TESTBIDULID',
            auction_participant_ulid: '01TESTPARTICIPANTULID',
            amount: 10,
            sequence_number: 1,
            placed_at: '2026-09-11T10:00:20.000000Z',
            expires_at: '2026-09-11T10:01:10.000000Z',
        });

        assert.deepEqual(
            client.getState(),
            snapshot
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it reloads the snapshot when auction nomination finalized is received', async () => {
    const originalFetch = global.fetch;

    const listeners = {};

    const channel = {
        listen(eventName, callback) {
            listeners[eventName] = callback;

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
            typeof listeners['.auction.nomination.finalized'],
            'function'
        );

        await listeners['.auction.nomination.finalized']({
            auction_ulid: '01TESTAUCTIONULID',
            nomination_ulid: '01TESTNOMINATIONULID',
            status: 'completed',
            close_reason: 'won',
            closed_at: '2026-09-11T10:02:00.000000Z',
        });

        assert.deepEqual(
            client.getState(),
            snapshot
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it reloads the snapshot when auction role phase advanced is received', async () => {
    const originalFetch = global.fetch;

    const listeners = {};

    const channel = {
        listen(eventName, callback) {
            listeners[eventName] = callback;

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
            ulid: '01TESTNEXTPHASEULID',
            role: 'D',
            position: 2,
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
            typeof listeners['.auction.role-phase.advanced'],
            'function'
        );

        await listeners['.auction.role-phase.advanced']({
            auction_ulid: '01TESTAUCTIONULID',
            previous_phase_ulid: '01TESTPREVIOUSPHASEULID',
            previous_role: 'P',
            next_phase_ulid: '01TESTNEXTPHASEULID',
            next_role: 'D',
            started_at: '2026-09-11T10:05:00.000000Z',
        });

        assert.deepEqual(
            client.getState(),
            snapshot
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it reloads the snapshot when auction completed is received', async () => {
    const originalFetch = global.fetch;

    const listeners = {};

    const channel = {
        listen(eventName, callback) {
            listeners[eventName] = callback;

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
        status: 'completed',
        active_role_phase: null,
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
            typeof listeners['.auction.completed'],
            'function'
        );

        await listeners['.auction.completed']({
            auction_ulid: '01TESTAUCTIONULID',
            status: 'completed',
            completed_at: '2026-09-11T10:10:00.000000Z',
        });

        assert.deepEqual(
            client.getState(),
            snapshot
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it resyncs the auction snapshot', async () => {
    const originalFetch = global.fetch;

    const snapshot = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_role_phase: null,
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
            '01TESTAUCTIONULID'
        );

        const result = await client.resync();

        assert.deepEqual(result, snapshot);
        assert.deepEqual(client.getState(), snapshot);
    } finally {
        global.fetch = originalFetch;
    }
});
