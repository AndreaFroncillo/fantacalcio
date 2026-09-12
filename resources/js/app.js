import './echo';

import {
    bootstrapAuctionPage,
} from './auction/bootstrapAuctionPage.js';

const auctionPage = document.querySelector(
    '[data-auction-ulid]'
);

if (auctionPage) {
    bootstrapAuctionPage({
        element: auctionPage,
        echo: window.Echo,
    });
}
