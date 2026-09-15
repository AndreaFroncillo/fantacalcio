export const initializeMobileMenu = () => {
    const toggle = document.querySelector(
        '[data-mobile-menu-toggle]'
    );

    const menu = document.querySelector(
        '[data-mobile-menu]'
    );

    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = !menu.classList.contains('hidden');

        menu.classList.toggle('hidden', isOpen);

        toggle.setAttribute(
            'aria-expanded',
            String(!isOpen)
        );
    });
};