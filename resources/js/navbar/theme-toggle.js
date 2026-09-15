export const initializeThemeToggle = () => {
    const toggleButtons = document.querySelectorAll(
        '[data-theme-toggle]'
    );

    if (!toggleButtons.length) {
        return;
    }

    const renderThemeIcons = () => {
        const isDark =
            document.documentElement.classList.contains('dark');

        toggleButtons.forEach((button) => {
            const lightIcon = button.querySelector(
                '[data-theme-light-icon]'
            );

            const darkIcon = button.querySelector(
                '[data-theme-dark-icon]'
            );

            lightIcon?.classList.toggle(
                'hidden',
                !isDark
            );

            darkIcon?.classList.toggle(
                'hidden',
                isDark
            );
        });
    };

    renderThemeIcons();

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const isDark =
                document.documentElement.classList.contains(
                    'dark'
                );

            const nextTheme =
                isDark ? 'light' : 'dark';

            document.documentElement.classList.toggle(
                'dark',
                nextTheme === 'dark'
            );

            document.documentElement.dataset.theme =
                nextTheme;

            localStorage.setItem(
                'theme',
                nextTheme
            );

            renderThemeIcons();
        });
    });
};