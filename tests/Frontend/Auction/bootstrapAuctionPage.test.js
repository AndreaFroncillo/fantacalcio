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
        async loadSnapshot() {
            return {
                ulid: '01TESTAUCTIONULID',
                status: 'active',

                active_role_phase: {
                    ulid: '01TESTROLEPHASE',
                    role: 'P',
                    position: 1,
                    status: 'active',
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
        async loadSnapshot() {
            return {
                ulid: '01TESTAUCTIONULID',
                status: 'active',

                active_nomination: {
                    ulid: '01TESTNOMINATION',
                    player: {
                        display_name: 'Mario Rossi',
                    },
                },
            };
        }

        subscribe() {}
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
        async loadSnapshot() {
            return {
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
        }

        subscribe() {}
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
