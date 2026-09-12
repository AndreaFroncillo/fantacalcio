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
