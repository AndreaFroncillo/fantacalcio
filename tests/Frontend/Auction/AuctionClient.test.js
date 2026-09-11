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

test('initial nomination countdown is zero', () => {
    const client = new AuctionClient(
        '01TESTAUCTIONULID'
    );

    assert.equal(
        client.nominationRemainingMilliseconds,
        0
    );
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
        connector: {
            pusher: {
                connection: {
                    bind() { },
                },
            },
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
        connector: {
            pusher: {
                connection: {
                    bind() { },
                },
            },
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
        connector: {
            pusher: {
                connection: {
                    bind() { },
                },
            },
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
        connector: {
            pusher: {
                connection: {
                    bind() { },
                },
            },
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
        connector: {
            pusher: {
                connection: {
                    bind() { },
                },
            },
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
        connector: {
            pusher: {
                connection: {
                    bind() { },
                },
            },
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
        connector: {
            pusher: {
                connection: {
                    bind() { },
                },
            },
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

test('it resyncs the auction when realtime connection reconnects', async () => {
    const originalFetch = global.fetch;

    const listeners = {};
    const connectionListeners = {};

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
        connector: {
            pusher: {
                connection: {
                    bind(eventName, callback) {
                        connectionListeners[eventName] = callback;
                    },
                },
            },
        },
    };

    const snapshot = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_role_phase: null,
        active_nomination: null,
        participants: [],
    };

    let fetchCalls = 0;

    global.fetch = async () => {
        fetchCalls++;

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
            '01TESTAUCTIONULID',
            echo
        );

        client.subscribe();

        assert.equal(
            typeof connectionListeners.state_change,
            'function'
        );

        await connectionListeners.state_change({
            previous: 'connecting',
            current: 'connected',
        });

        assert.equal(fetchCalls, 0);
        assert.equal(client.getState(), null);

        await connectionListeners.state_change({
            previous: 'connected',
            current: 'unavailable',
        });

        await connectionListeners.state_change({
            previous: 'unavailable',
            current: 'connecting',
        });

        await connectionListeners.state_change({
            previous: 'connecting',
            current: 'connected',
        });

        assert.equal(fetchCalls, 1);

        assert.deepEqual(
            client.getState(),
            snapshot
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it handles realtime resync failure without unhandled rejection', async () => {
    const originalFetch = global.fetch;

    const connectionListeners = {};

    const channel = {
        listen() {
            return this;
        },
    };

    const echo = {
        private() {
            return channel;
        },
        connector: {
            pusher: {
                connection: {
                    bind(eventName, callback) {
                        connectionListeners[eventName] = callback;
                    },
                },
            },
        },
    };

    global.fetch = async () => ({
        ok: false,
        status: 500,
    });

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID',
            echo
        );

        client.subscribe();

        await connectionListeners.state_change({
            previous: 'connected',
            current: 'unavailable',
        });

        await assert.doesNotReject(
            async () => {
                await connectionListeners.state_change({
                    previous: 'connecting',
                    current: 'connected',
                });
            }
        );

        assert.equal(
            client.getState(),
            null
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it calculates remaining nomination time from expires_at', () => {
    const client = new AuctionClient(
        '01TESTAUCTIONULID'
    );

    client.state = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_nomination: {
            ulid: '01TESTNOMINATIONULID',
            expires_at: '2026-09-11T20:00:10.000Z',
        },
    };

    const now = new Date(
        '2026-09-11T20:00:00.000Z'
    ).getTime();

    const remainingMilliseconds =
        client.getRemainingNominationMilliseconds(now);

    assert.equal(
        remainingMilliseconds,
        10000
    );
});

test('it returns zero remaining time without an active nomination expiry', () => {
    const client = new AuctionClient(
        '01TESTAUCTIONULID'
    );

    client.state = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_nomination: null,
    };

    const now = new Date(
        '2026-09-11T20:00:00.000Z'
    ).getTime();

    assert.equal(
        client.getRemainingNominationMilliseconds(now),
        0
    );
});

test('it returns zero remaining time when nomination is expired', () => {
    const client = new AuctionClient(
        '01TESTAUCTIONULID'
    );

    client.state = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_nomination: {
            ulid: '01TESTNOMINATIONULID',
            expires_at: '2026-09-11T19:59:50.000Z',
        },
    };

    const now = new Date(
        '2026-09-11T20:00:00.000Z'
    ).getTime();

    assert.equal(
        client.getRemainingNominationMilliseconds(now),
        0
    );
});

test('it updates nomination countdown locally', () => {
    const client = new AuctionClient(
        '01TESTAUCTIONULID'
    );

    client.state = {
        ulid: '01TESTAUCTIONULID',
        status: 'live',
        active_nomination: {
            ulid: '01TESTNOMINATIONULID',
            expires_at: '2026-09-11T20:00:10.000Z',
        },
    };

    const now = new Date(
        '2026-09-11T20:00:04.000Z'
    ).getTime();

    const remainingMilliseconds =
        client.updateNominationCountdown(now);

    assert.equal(
        remainingMilliseconds,
        6000
    );

    assert.equal(
        client.nominationRemainingMilliseconds,
        6000
    );
});

test('it starts the nomination countdown ticker', () => {
    const originalSetInterval = global.setInterval;

    let intervalCallback = null;
    let intervalMilliseconds = null;

    global.setInterval = (callback, milliseconds) => {
        intervalCallback = callback;
        intervalMilliseconds = milliseconds;

        return 123;
    };

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        client.state = {
            ulid: '01TESTAUCTIONULID',
            status: 'live',
            active_nomination: {
                ulid: '01TESTNOMINATIONULID',
                expires_at: '2026-09-11T20:00:10.000Z',
            },
        };

        const result =
            client.startNominationCountdown();

        assert.equal(
            intervalMilliseconds,
            1000
        );

        assert.equal(
            typeof intervalCallback,
            'function'
        );

        assert.equal(
            result,
            123
        );
    } finally {
        global.setInterval = originalSetInterval;
    }
});

test('it updates the nomination countdown on each ticker interval', () => {
    const originalSetInterval = global.setInterval;
    const originalDateNow = Date.now;

    let intervalCallback = null;

    global.setInterval = (callback) => {
        intervalCallback = callback;

        return 123;
    };

    Date.now = () =>
        new Date(
            '2026-09-11T20:00:04.000Z'
        ).getTime();

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        client.state = {
            ulid: '01TESTAUCTIONULID',
            status: 'live',
            active_nomination: {
                ulid: '01TESTNOMINATIONULID',
                expires_at: '2026-09-11T20:00:10.000Z',
            },
        };

        client.startNominationCountdown();

        intervalCallback();

        assert.equal(
            client.nominationRemainingMilliseconds,
            6000
        );
    } finally {
        global.setInterval = originalSetInterval;
        Date.now = originalDateNow;
    }
});

test('it stops the nomination countdown ticker', () => {
    const originalSetInterval = global.setInterval;
    const originalClearInterval = global.clearInterval;

    let clearedIntervalId = null;

    global.setInterval = () => 123;

    global.clearInterval = (intervalId) => {
        clearedIntervalId = intervalId;
    };

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        client.startNominationCountdown();

        client.stopNominationCountdown();

        assert.equal(
            clearedIntervalId,
            123
        );
    } finally {
        global.setInterval = originalSetInterval;
        global.clearInterval = originalClearInterval;
    }
});

test('it clears the nomination countdown interval id when stopped', () => {
    const originalSetInterval = global.setInterval;
    const originalClearInterval = global.clearInterval;

    global.setInterval = () => 123;
    global.clearInterval = () => { };

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        client.startNominationCountdown();

        assert.equal(
            client.nominationCountdownIntervalId,
            123
        );

        client.stopNominationCountdown();

        assert.equal(
            client.nominationCountdownIntervalId,
            null
        );
    } finally {
        global.setInterval = originalSetInterval;
        global.clearInterval = originalClearInterval;
    }
});

test('it does not start a duplicate nomination countdown ticker', () => {
    const originalSetInterval = global.setInterval;

    let setIntervalCalls = 0;

    global.setInterval = () => {
        setIntervalCalls++;

        return 123;
    };

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        const firstIntervalId =
            client.startNominationCountdown();

        const secondIntervalId =
            client.startNominationCountdown();

        assert.equal(
            setIntervalCalls,
            1
        );

        assert.equal(
            firstIntervalId,
            123
        );

        assert.equal(
            secondIntervalId,
            123
        );

        assert.equal(
            client.nominationCountdownIntervalId,
            123
        );
    } finally {
        global.setInterval = originalSetInterval;
    }
});

test('it stops the nomination countdown ticker when countdown expires', () => {
    const originalSetInterval = global.setInterval;
    const originalClearInterval = global.clearInterval;
    const originalDateNow = Date.now;

    let intervalCallback = null;
    let clearedIntervalId = null;

    global.setInterval = (callback) => {
        intervalCallback = callback;

        return 123;
    };

    global.clearInterval = (intervalId) => {
        clearedIntervalId = intervalId;
    };

    Date.now = () =>
        new Date(
            '2026-09-11T20:00:10.000Z'
        ).getTime();

    try {
        const client = new AuctionClient(
            '01TESTAUCTIONULID'
        );

        client.state = {
            ulid: '01TESTAUCTIONULID',
            status: 'live',
            active_nomination: {
                ulid: '01TESTNOMINATIONULID',
                expires_at: '2026-09-11T20:00:10.000Z',
            },
        };

        client.startNominationCountdown();

        intervalCallback();

        assert.equal(
            client.nominationRemainingMilliseconds,
            0
        );

        assert.equal(
            clearedIntervalId,
            123
        );

        assert.equal(
            client.nominationCountdownIntervalId,
            null
        );
    } finally {
        global.setInterval = originalSetInterval;
        global.clearInterval = originalClearInterval;
        Date.now = originalDateNow;
    }
});
