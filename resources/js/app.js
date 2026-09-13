import './echo';

import Alpine from 'alpinejs';

import {
    bootstrapAuctionPage,
} from './auction/bootstrapAuctionPage.js';

window.Alpine = Alpine;

Alpine.start();

const auctionPage = document.querySelector(
    '[data-auction-ulid]'
);

if (auctionPage) {
    bootstrapAuctionPage({
        element: auctionPage,
        echo: window.Echo,
    });
}