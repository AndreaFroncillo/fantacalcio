import {
    bootstrapAuctionPage,
} from './bootstrapAuctionPage.js';

export const initializeAuction = () => {
    const auctionPage = document.querySelector(
        '[data-auction-ulid]'
    );

    if (!auctionPage) {
        return;
    }

    bootstrapAuctionPage({
        element: auctionPage,
        echo: window.Echo,
    });
};

document.addEventListener(
    'DOMContentLoaded',
    initializeAuction
);