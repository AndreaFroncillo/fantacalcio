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
