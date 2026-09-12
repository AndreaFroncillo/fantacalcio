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
