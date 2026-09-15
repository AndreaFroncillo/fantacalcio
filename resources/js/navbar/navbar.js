import {
    initializeMobileMenu,
} from './mobile-menu.js';

import {
    initializeThemeToggle,
} from './theme-toggle.js';

export const initializeNavbar = () => {
    initializeMobileMenu();
    initializeThemeToggle();
};

document.addEventListener(
    'DOMContentLoaded',
    initializeNavbar
);