import test from 'node:test';
import assert from 'node:assert/strict';

import {
    bootstrapAuctionPage,
} from '../../../resources/js/auction/bootstrapAuctionPage.js';

test('it boots auction client from page auction ulid', async () => {
    const calls = {
        constructedWith: null,
        loadSnapshot: 0,
        subscribe: 0,
    };

    class FakeAuctionClient {
        constructor(auctionUlid, echo) {
            calls.constructedWith = {
                auctionUlid,
                echo,
            };
        }

        setSnapshotListener() { }

        async loadSnapshot() {
            calls.loadSnapshot++;
        }

        subscribe() {
            calls.subscribe++;
        }
    }

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector() {
            return null;
        },
    };

    const echo = {
        private() { },
    };

    await bootstrapAuctionPage({
        element,
        echo,
        AuctionClientClass: FakeAuctionClient,
    });

    assert.deepEqual(
        calls.constructedWith,
        {
            auctionUlid: '01TESTAUCTIONULID',
            echo,
        }
    );

    assert.equal(
        calls.loadSnapshot,
        1
    );

    assert.equal(
        calls.subscribe,
        1
    );
});

test('it renders initial auction status after loading snapshot', async () => {
    const statusElement = {
        textContent: '',
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-status]') {
                return statusElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        async loadSnapshot() {
            const snapshot = {
                ulid: '01TESTAUCTIONULID',
                status: 'active',
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        statusElement.textContent,
        'active'
    );
});

test('it renders active role phase after loading snapshot', async () => {
    const roleElement = {
        textContent: '',
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-role]') {
                return roleElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        async loadSnapshot() {
            const snapshot = {
                ulid: '01TESTAUCTIONULID',
                status: 'active',

                active_role_phase: {
                    ulid: '01TESTROLEPHASE',
                    role: 'P',
                    position: 1,
                    status: 'active',
                },
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        roleElement.textContent,
        'P'
    );
});

test('it renders active nomination player after loading snapshot', async () => {
    const playerElement = {
        textContent: '',
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-player]') {
                return playerElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        async loadSnapshot() {
            const snapshot = {
                ulid: '01TESTAUCTIONULID',
                status: 'active',

                active_nomination: {
                    ulid: '01TESTNOMINATION',

                    player: {
                        display_name: 'Mario Rossi',
                    },
                },
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        playerElement.textContent,
        'Mario Rossi'
    );
});

test('it renders current bid amount after loading snapshot', async () => {
    const currentBidElement = {
        textContent: '',
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-current-bid]') {
                return currentBidElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        async loadSnapshot() {
            const snapshot = {
                ulid: '01TESTAUCTIONULID',
                status: 'active',

                active_nomination: {
                    ulid: '01TESTNOMINATION',

                    current_bid: {
                        ulid: '01TESTBID',
                        amount: 25,
                        sequence_number: 3,
                        participant_ulid: '01TESTPARTICIPANT',
                        placed_at: '2026-09-12T20:00:00.000000Z',
                    },
                },
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        currentBidElement.textContent,
        '25'
    );
});

test('it renders nomination countdown updates', async () => {
    const countdownElement = {
        textContent: '',
    };

    let countdownListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-countdown]') {
                return countdownElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener() { }

        setNominationCountdownListener(listener) {
            countdownListener = listener;
        }

        async loadSnapshot() {
            return {
                ulid: '01TESTAUCTIONULID',
                status: 'active',
            };
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    countdownListener(6000);

    assert.equal(
        countdownElement.textContent,
        '6'
    );
});

test('it rerenders auction data when snapshot changes', async () => {
    const statusElement = {
        textContent: '',
    };

    const roleElement = {
        textContent: '',
    };

    const playerElement = {
        textContent: '',
    };

    const currentBidElement = {
        textContent: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-status]') {
                return statusElement;
            }

            if (selector === '[data-auction-role]') {
                return roleElement;
            }

            if (selector === '[data-auction-player]') {
                return playerElement;
            }

            if (
                selector ===
                '[data-auction-current-bid]'
            ) {
                return currentBidElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            return {
                status: 'active',
                active_role_phase: {
                    role: 'P',
                },
                active_nomination: {
                    player: {
                        display_name: 'Mario Rossi',
                    },
                    current_bid: {
                        amount: 25,
                    },
                },
            };
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    snapshotListener({
        status: 'active',
        active_role_phase: {
            role: 'D',
        },
        active_nomination: {
            player: {
                display_name: 'Luigi Bianchi',
            },
            current_bid: {
                amount: 40,
            },
        },
    });

    assert.equal(
        statusElement.textContent,
        'active'
    );

    assert.equal(
        roleElement.textContent,
        'D'
    );

    assert.equal(
        playerElement.textContent,
        'Luigi Bianchi'
    );

    assert.equal(
        currentBidElement.textContent,
        '40'
    );
});

test('it renders initial snapshot only once', async () => {
    let snapshotListener = null;
    let statusRenderCount = 0;

    const statusElement = {
        _textContent: '',

        set textContent(value) {
            this._textContent = value;
            statusRenderCount++;
        },

        get textContent() {
            return this._textContent;
        },
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-status]') {
                return statusElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'active',
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        statusElement.textContent,
        'active'
    );

    assert.equal(
        statusRenderCount,
        1
    );
});

test('it clears nomination ui when snapshot has no active nomination', async () => {
    const playerElement = {
        textContent: 'Mario Rossi',
    };

    const currentBidElement = {
        textContent: '25',
    };

    const countdownElement = {
        textContent: '6',
    };

    let snapshotListener = null;
    let countdownListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (selector === '[data-auction-player]') {
                return playerElement;
            }

            if (selector === '[data-auction-current-bid]') {
                return currentBidElement;
            }

            if (selector === '[data-auction-countdown]') {
                return countdownElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener(listener) {
            countdownListener = listener;
        }

        async loadSnapshot() {
            const snapshot = {
                status: 'active',
                active_nomination: {
                    player: {
                        display_name: 'Mario Rossi',
                    },
                    current_bid: {
                        amount: 25,
                    },
                },
            };

            snapshotListener(snapshot);
            countdownListener(6000);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    snapshotListener({
        status: 'active',
        active_nomination: null,
    });

    countdownListener(0);

    assert.equal(
        playerElement.textContent,
        ''
    );

    assert.equal(
        currentBidElement.textContent,
        ''
    );

    assert.equal(
        countdownElement.textContent,
        ''
    );
});

test('it updates auction ui after realtime bid event reloads snapshot', async () => {
    const originalFetch = global.fetch;

    const listeners = {};

    const currentBidElement = {
        textContent: '',
    };

    const playerElement = {
        textContent: '',
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-current-bid]'
            ) {
                return currentBidElement;
            }

            if (selector === '[data-auction-player]') {
                return playerElement;
            }

            return null;
        },
    };

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

    const snapshots = [
        {
            ulid: '01TESTAUCTIONULID',
            status: 'live',

            active_role_phase: {
                role: 'P',
            },

            active_nomination: {
                player: {
                    display_name: 'Mario Rossi',
                },

                current_bid: null,
            },

            participants: [],
        },

        {
            ulid: '01TESTAUCTIONULID',
            status: 'live',

            active_role_phase: {
                role: 'P',
            },

            active_nomination: {
                player: {
                    display_name: 'Mario Rossi',
                },

                current_bid: {
                    amount: 25,
                },
            },

            participants: [],
        },
    ];

    let fetchCount = 0;

    global.fetch = async () => ({
        ok: true,

        async json() {
            return {
                data: snapshots[fetchCount++],
            };
        },
    });

    try {
        await bootstrapAuctionPage({
            element,
            echo,
        });

        assert.equal(
            playerElement.textContent,
            'Mario Rossi'
        );

        assert.equal(
            currentBidElement.textContent,
            ''
        );

        assert.equal(
            typeof listeners['.auction.bid.placed'],
            'function'
        );

        await listeners['.auction.bid.placed']({
            auction_ulid: '01TESTAUCTIONULID',
            nomination_ulid: '01TESTNOMINATIONULID',
            bid_ulid: '01TESTBIDULID',
            auction_participant_ulid:
                '01TESTPARTICIPANTULID',
            amount: 25,
            sequence_number: 1,
            placed_at:
                '2026-09-12T10:00:00.000000Z',
            expires_at:
                '2026-09-12T10:00:10.000000Z',
        });

        assert.equal(
            fetchCount,
            2
        );

        assert.equal(
            playerElement.textContent,
            'Mario Rossi'
        );

        assert.equal(
            currentBidElement.textContent,
            '25'
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it renders auction participants with team balance', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: null,
                active_nomination: null,

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: 'Team Alpha',
                            short_name: 'ALP',
                            current_balance: 420,
                        },
                    },

                    {
                        ulid: '01PARTICIPANT2',
                        nomination_position: 2,

                        team: {
                            ulid: '01TEAM2',
                            name: 'Team Beta',
                            short_name: 'BET',
                            current_balance: 365,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /Team Alpha/
    );

    assert.match(
        participantsElement.innerHTML,
        /420/
    );

    assert.match(
        participantsElement.innerHTML,
        /Team Beta/
    );

    assert.match(
        participantsElement.innerHTML,
        /365/
    );
});

test('it updates participant balance when snapshot changes', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',
                active_role_phase: null,
                active_nomination: null,

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: 'Team Alpha',
                            short_name: 'ALP',
                            current_balance: 420,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /420/
    );

    snapshotListener({
        status: 'live',
        active_role_phase: null,
        active_nomination: null,

        participants: [
            {
                ulid: '01PARTICIPANT1',
                nomination_position: 1,

                team: {
                    ulid: '01TEAM1',
                    name: 'Team Alpha',
                    short_name: 'ALP',
                    current_balance: 395,
                },
            },
        ],
    });

    assert.match(
        participantsElement.innerHTML,
        /395/
    );

    assert.doesNotMatch(
        participantsElement.innerHTML,
        /420/
    );
});

test('it renders participant nomination position and current nominator', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    nominator: {
                        ulid: '01PARTICIPANT2',
                        nomination_position: 2,
                    },
                },

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: 'Team Alpha',
                            short_name: 'ALP',
                            current_balance: 420,
                        },
                    },

                    {
                        ulid: '01PARTICIPANT2',
                        nomination_position: 2,

                        team: {
                            ulid: '01TEAM2',
                            name: 'Team Beta',
                            short_name: 'BET',
                            current_balance: 365,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /Posizione 1/
    );

    assert.match(
        participantsElement.innerHTML,
        /Posizione 2/
    );

    assert.match(
        participantsElement.innerHTML,
        /Nominatore corrente/
    );
});

test('it renders current highest bidder in participants list', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    nominator: {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,
                    },

                    current_bid: {
                        amount: 25,
                        participant_ulid: '01PARTICIPANT2',
                    },
                },

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: 'Team Alpha',
                            short_name: 'ALP',
                            current_balance: 420,
                        },
                    },

                    {
                        ulid: '01PARTICIPANT2',
                        nomination_position: 2,

                        team: {
                            ulid: '01TEAM2',
                            name: 'Team Beta',
                            short_name: 'BET',
                            current_balance: 365,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /Miglior offerente/
    );
});

test('it moves current highest bidder when snapshot changes', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    nominator: {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,
                    },

                    current_bid: {
                        amount: 25,
                        participant_ulid: '01PARTICIPANT1',
                    },
                },

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: 'Team Alpha',
                            short_name: 'ALP',
                            current_balance: 420,
                        },
                    },

                    {
                        ulid: '01PARTICIPANT2',
                        nomination_position: 2,

                        team: {
                            ulid: '01TEAM2',
                            name: 'Team Beta',
                            short_name: 'BET',
                            current_balance: 365,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /Team Alpha[\s\S]*Miglior offerente/
    );

    snapshotListener({
        status: 'live',

        active_role_phase: {
            role: 'P',
        },

        active_nomination: {
            nominator: {
                ulid: '01PARTICIPANT1',
                nomination_position: 1,
            },

            current_bid: {
                amount: 30,
                participant_ulid: '01PARTICIPANT2',
            },
        },

        participants: [
            {
                ulid: '01PARTICIPANT1',
                nomination_position: 1,

                team: {
                    ulid: '01TEAM1',
                    name: 'Team Alpha',
                    short_name: 'ALP',
                    current_balance: 420,
                },
            },

            {
                ulid: '01PARTICIPANT2',
                nomination_position: 2,

                team: {
                    ulid: '01TEAM2',
                    name: 'Team Beta',
                    short_name: 'BET',
                    current_balance: 365,
                },
            },
        ],
    });

    assert.match(
        participantsElement.innerHTML,
        /Team Beta[\s\S]*Miglior offerente/
    );

    assert.doesNotMatch(
        participantsElement.innerHTML,
        /Team Alpha[\s\S]*Miglior offerente[\s\S]*Team Beta/
    );
});

test('it clears current highest bidder when nomination ends', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    nominator: {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,
                    },

                    current_bid: {
                        amount: 25,
                        participant_ulid: '01PARTICIPANT2',
                    },
                },

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: 'Team Alpha',
                            short_name: 'ALP',
                            current_balance: 420,
                        },
                    },

                    {
                        ulid: '01PARTICIPANT2',
                        nomination_position: 2,

                        team: {
                            ulid: '01TEAM2',
                            name: 'Team Beta',
                            short_name: 'BET',
                            current_balance: 365,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /Miglior offerente/
    );

    snapshotListener({
        status: 'live',

        active_role_phase: {
            role: 'P',
        },

        active_nomination: null,

        participants: [
            {
                ulid: '01PARTICIPANT1',
                nomination_position: 1,

                team: {
                    ulid: '01TEAM1',
                    name: 'Team Alpha',
                    short_name: 'ALP',
                    current_balance: 420,
                },
            },

            {
                ulid: '01PARTICIPANT2',
                nomination_position: 2,

                team: {
                    ulid: '01TEAM2',
                    name: 'Team Beta',
                    short_name: 'BET',
                    current_balance: 365,
                },
            },
        ],
    });

    assert.doesNotMatch(
        participantsElement.innerHTML,
        /Miglior offerente/
    );

    assert.doesNotMatch(
        participantsElement.innerHTML,
        /Nominatore corrente/
    );
});

test('it renders participants empty state when no participants exist', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'pending',
                active_role_phase: null,
                active_nomination: null,
                participants: [],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /Nessun partecipante/
    );
});

test('it escapes participant team name html', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',
                active_role_phase: null,
                active_nomination: null,

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: '<script>alert("xss")</script>',
                            short_name: 'XSS',
                            current_balance: 420,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.doesNotMatch(
        participantsElement.innerHTML,
        /<script>/
    );

    assert.match(
        participantsElement.innerHTML,
        /&lt;script&gt;/
    );
});

test('it handles participant without team', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',
                active_role_phase: null,
                active_nomination: null,

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,
                        team: null,
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await assert.doesNotReject(async () => {
        await bootstrapAuctionPage({
            element,
            echo: {},
            AuctionClientClass: FakeAuctionClient,
        });
    });

    assert.match(
        participantsElement.innerHTML,
        /Squadra non disponibile/
    );
});

test('it renders bid increment controls for active nomination', async () => {
    const bidControlsElement = {
        innerHTML: '',

        addEventListener() { },
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',

                    current_bid: {
                        amount: 25,
                        participant_ulid: '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        bidControlsElement.innerHTML,
        /data-bid-increment="1"/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /\+1/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /data-bid-increment="5"/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /\+5/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /data-bid-increment="10"/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /\+10/
    );
});

test('it clears bid controls when active nomination ends', async () => {
    const bidControlsElement = {
        innerHTML: '',

        addEventListener() { },
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',

                    current_bid: {
                        amount: 25,
                        participant_ulid: '01PARTICIPANT1',
                    },
                },

                participants: [
                    {
                        ulid: '01PARTICIPANT1',
                        nomination_position: 1,

                        team: {
                            ulid: '01TEAM1',
                            name: 'Team Alpha',
                            short_name: 'ALP',
                            current_balance: 420,
                        },
                    },
                ],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        bidControlsElement.innerHTML,
        /data-bid-increment="1"/
    );

    snapshotListener({
        status: 'live',

        active_role_phase: {
            role: 'P',
        },

        active_nomination: null,

        participants: [
            {
                ulid: '01PARTICIPANT1',
                nomination_position: 1,

                team: {
                    ulid: '01TEAM1',
                    name: 'Team Alpha',
                    short_name: 'ALP',
                    current_balance: 420,
                },
            },
        ],
    });

    assert.equal(
        bidControlsElement.innerHTML,
        ''
    );
});

test('it places current bid plus one from bid increment control', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const calls = {
        placeBid: [],
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid: '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid(
            nominationUlid,
            amount
        ) {
            calls.placeBid.push({
                nominationUlid,
                amount,
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.ok(clickListener);

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.deepEqual(
        calls.placeBid,
        [
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 26,
            },
        ]
    );

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '5',
            },
        },
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '10',
            },
        },
    });

    assert.deepEqual(
        calls.placeBid,
        [
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 26,
            },
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 30,
            },
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 35,
            },
        ]
    );
});

test('it places opening price from plus one when nomination has no current bid', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const calls = {
        placeBid: [],
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,
                    current_bid: null,
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid(
            nominationUlid,
            amount
        ) {
            calls.placeBid.push({
                nominationUlid,
                amount,
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '5',
            },
        },
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '10',
            },
        },
    });

    assert.deepEqual(
        calls.placeBid,
        [
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 1,
            },
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 5,
            },
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 10,
            },
        ]
    );
});

test('it does not place bid after active nomination ends', async () => {
    let clickListener = null;
    let snapshotListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const calls = {
        placeBid: [],
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid(
            nominationUlid,
            amount
        ) {
            calls.placeBid.push({
                nominationUlid,
                amount,
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid:
                '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    snapshotListener({
        status: 'live',

        active_role_phase: {
            role: 'P',
        },

        active_nomination: null,

        participants: [],
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.deepEqual(
        calls.placeBid,
        []
    );

    assert.equal(
        bidControlsElement.innerHTML,
        ''
    );
});

test('it does not place bid when current participant already has highest bid', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const calls = {
        placeBid: [],
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT1',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid(
            nominationUlid,
            amount
        ) {
            calls.placeBid.push({
                nominationUlid,
                amount,
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid:
                '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.ok(clickListener);

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.deepEqual(
        calls.placeBid,
        []
    );
});

test('it places bid when current participant is not highest bidder', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const calls = {
        placeBid: [],
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT2',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid(
            nominationUlid,
            amount
        ) {
            calls.placeBid.push({
                nominationUlid,
                amount,
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid:
                '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.deepEqual(
        calls.placeBid,
        [
            {
                nominationUlid:
                    '01NOMINATIONULID',
                amount: 26,
            },
        ]
    );
});

test('it renders bid error when placing bid fails', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const bidErrorElement = {
        textContent: '',
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT2',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid() {
            throw new Error(
                'Unable to place auction bid (422).'
            );
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid:
                '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            if (
                selector ===
                '[data-auction-bid-error]'
            ) {
                return bidErrorElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.equal(
        bidErrorElement.textContent,
        'Unable to place auction bid (422).'
    );
});

test('it clears previous bid error before placing a new bid', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const bidErrorElement = {
        textContent: 'Previous bid error',
    };

    let errorTextWhenBidStarted = null;

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT2',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid() {
            errorTextWhenBidStarted =
                bidErrorElement.textContent;

            return {};
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid:
                '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            if (
                selector ===
                '[data-auction-bid-error]'
            ) {
                return bidErrorElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.equal(
        errorTextWhenBidStarted,
        ''
    );

    assert.equal(
        bidErrorElement.textContent,
        ''
    );
});

test('it prevents duplicate bid while previous bid is pending', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    let resolveBid = null;

    const calls = {
        placeBid: 0,
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT2',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid() {
            calls.placeBid += 1;

            return new Promise((resolve) => {
                resolveBid = resolve;
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid:
                '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    const firstClickPromise = clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    const secondClickPromise = clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.equal(
        calls.placeBid,
        1
    );

    resolveBid();

    await firstClickPromise;
    await secondClickPromise;
});

test('it disables bid controls while bid request is pending', async () => {
    let clickListener = null;

    const bidButtons = [
        {
            disabled: false,
        },
        {
            disabled: false,
        },
        {
            disabled: false,
        },
    ];

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },

        querySelectorAll(selector) {
            if (
                selector ===
                '[data-bid-increment]'
            ) {
                return bidButtons;
            }

            return [];
        },
    };

    let resolveBid = null;

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT2',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid() {
            return new Promise((resolve) => {
                resolveBid = resolve;
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid:
                '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    const clickPromise = clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.deepEqual(
        bidButtons.map(
            (button) => button.disabled
        ),
        [
            true,
            true,
            true,
        ]
    );

    resolveBid();

    await clickPromise;

    assert.deepEqual(
        bidButtons.map(
            (button) => button.disabled
        ),
        [
            false,
            false,
            false,
        ]
    );
});

test('it updates auction ui after placing bid and receiving realtime bid event', async () => {
    let clickListener = null;

    const listeners = {};

    const currentBidElement = {
        textContent: '',
    };

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },

        querySelectorAll() {
            return [];
        },
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-current-bid]'
            ) {
                return currentBidElement;
            }

            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

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

    const snapshots = [
        {
            ulid: '01TESTAUCTIONULID',
            status: 'live',

            current_participant_ulid:
                '01PARTICIPANT2',

            active_role_phase: {
                role: 'P',
            },

            active_nomination: {
                ulid: '01NOMINATIONULID',
                opening_price: 1,

                current_bid: {
                    amount: 25,
                    participant_ulid:
                        '01PARTICIPANT1',
                },
            },

            participants: [],
        },

        {
            ulid: '01TESTAUCTIONULID',
            status: 'live',

            current_participant_ulid:
                '01PARTICIPANT2',

            active_role_phase: {
                role: 'P',
            },

            active_nomination: {
                ulid: '01NOMINATIONULID',
                opening_price: 1,

                current_bid: {
                    amount: 26,
                    participant_ulid:
                        '01PARTICIPANT2',
                },
            },

            participants: [],
        },
    ];

    const fetchCalls = [];

    const originalFetch = global.fetch;

    global.fetch = async (
        url,
        options = {}
    ) => {
        fetchCalls.push({
            url,
            options,
        });

        if (options.method === 'POST') {
            return {
                ok: true,

                async json() {
                    return {
                        data: {
                            ulid: '01BIDULID',
                            amount: 26,
                            sequence_number: 2,
                        },
                    };
                },
            };
        }

        return {
            ok: true,

            async json() {
                return {
                    data: snapshots.shift(),
                };
            },
        };
    };

    try {
        await bootstrapAuctionPage({
            element,
            echo,
        });

        assert.equal(
            currentBidElement.textContent,
            '25'
        );

        await clickListener({
            target: {
                dataset: {
                    bidIncrement: '1',
                },
            },
        });

        assert.equal(
            fetchCalls.length,
            2
        );

        assert.equal(
            fetchCalls[1].options.method,
            'POST'
        );

        assert.equal(
            currentBidElement.textContent,
            '25'
        );

        await listeners['.auction.bid.placed']({
            auction_ulid:
                '01TESTAUCTIONULID',
            nomination_ulid:
                '01NOMINATIONULID',
            bid_ulid:
                '01BIDULID',
            auction_participant_ulid:
                '01PARTICIPANT2',
            amount: 26,
            sequence_number: 2,
            placed_at:
                '2026-09-12T10:00:00.000000Z',
            expires_at:
                '2026-09-12T10:00:10.000000Z',
        });

        assert.equal(
            fetchCalls.length,
            3
        );

        assert.equal(
            currentBidElement.textContent,
            '26'
        );
    } finally {
        global.fetch = originalFetch;
    }
});

test('it renders bid controls when participants list is empty', async () => {
    const participantsElement = {
        innerHTML: '',
    };

    const bidControlsElement = {
        innerHTML: '',

        addEventListener() { },
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-participants]'
            ) {
                return participantsElement;
            }

            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,
                    current_bid: null,
                },

                participants: [],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.match(
        participantsElement.innerHTML,
        /Nessun partecipante/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /data-bid-increment="1"/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /data-bid-increment="5"/
    );

    assert.match(
        bidControlsElement.innerHTML,
        /data-bid-increment="10"/
    );
});

test('it does not render bid controls when current user is not an auction participant', async () => {
    const bidControlsElement = {
        innerHTML: '',

        addEventListener() { },
    };

    let snapshotListener = null;

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid: null,

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,
                    current_bid: null,
                },

                participants: [],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        bidControlsElement.innerHTML,
        ''
    );
});

test('it does not place bid when current user is not an auction participant', async () => {
    let clickListener = null;

    const bidControlsElement = {
        innerHTML: '',

        addEventListener(eventName, listener) {
            if (eventName === 'click') {
                clickListener = listener;
            }
        },
    };

    const calls = {
        placeBid: [],
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid: null,

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,
                    current_bid: null,
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        async placeBid(
            nominationUlid,
            amount
        ) {
            calls.placeBid.push({
                nominationUlid,
                amount,
            });
        }

        subscribe() { }
    }

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.ok(clickListener);

    await clickListener({
        target: {
            dataset: {
                bidIncrement: '1',
            },
        },
    });

    assert.deepEqual(
        calls.placeBid,
        []
    );
});

test('it does not render bid controls when current participant already has highest bid', async () => {
    const bidControlsElement = {
        innerHTML: '',

        addEventListener() { },
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            this.snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT1',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            this.snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        bidControlsElement.innerHTML,
        ''
    );
});

test('it clears bid error when active nomination ends', async () => {
    let snapshotListener = null;

    const bidErrorElement = {
        textContent: 'Unable to place auction bid (422).',
    };

    const bidControlsElement = {
        innerHTML: '',

        addEventListener() { },
    };

    const element = {
        dataset: {
            auctionUlid: '01TESTAUCTIONULID',
        },

        querySelector(selector) {
            if (
                selector ===
                '[data-auction-bid-error]'
            ) {
                return bidErrorElement;
            }

            if (
                selector ===
                '[data-auction-bid-controls]'
            ) {
                return bidControlsElement;
            }

            return null;
        },
    };

    class FakeAuctionClient {
        setSnapshotListener(listener) {
            snapshotListener = listener;
        }

        setNominationCountdownListener() { }

        async loadSnapshot() {
            const snapshot = {
                status: 'live',

                current_participant_ulid:
                    '01PARTICIPANT2',

                active_role_phase: {
                    role: 'P',
                },

                active_nomination: {
                    ulid: '01NOMINATIONULID',
                    opening_price: 1,

                    current_bid: {
                        amount: 25,
                        participant_ulid:
                            '01PARTICIPANT1',
                    },
                },

                participants: [],
            };

            snapshotListener(snapshot);

            return snapshot;
        }

        subscribe() { }
    }

    await bootstrapAuctionPage({
        element,
        echo: {},
        AuctionClientClass: FakeAuctionClient,
    });

    assert.equal(
        bidErrorElement.textContent,
        'Unable to place auction bid (422).'
    );

    snapshotListener({
        status: 'live',

        current_participant_ulid:
            '01PARTICIPANT2',

        active_role_phase: {
            role: 'P',
        },

        active_nomination: null,

        participants: [],
    });

    assert.equal(
        bidErrorElement.textContent,
        ''
    );
});
