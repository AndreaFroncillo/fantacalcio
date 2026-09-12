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
