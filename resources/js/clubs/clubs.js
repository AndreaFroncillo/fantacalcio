import {
    initializeCompetitionFilter,
} from './competition-filter.js';

import {
    initializeClubPreview,
} from './club-preview.js';


export const initializeClubs = () => {
    const showcases = document.querySelectorAll(
        '[data-clubs-showcase]'
    );

    showcases.forEach((showcase) => {
        initializeCompetitionFilter(showcase);
        initializeClubPreview(showcase);
    });
};

document.addEventListener(
    'DOMContentLoaded',
    initializeClubs
);