<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="scroll-smooth">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="color-scheme"
        content="light dark">

    <title>
        {{ $title ?? config('app.name') }}
    </title>

    <script>
        (() => {
            const storedTheme = localStorage.getItem('theme');

            const prefersDark = window.matchMedia(
                '(prefers-color-scheme: dark)'
            ).matches;

            const theme =
                storedTheme ??
                (prefersDark ? 'dark' : 'light');

            document.documentElement.classList.toggle(
                'dark',
                theme === 'dark'
            );

            document.documentElement.dataset.theme = theme;
        })();
    </script>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])
</head>

<body
    class="
        min-h-screen
        bg-surface-50
        text-surface-900
        antialiased

        dark:bg-dark-950
        dark:text-white
    ">

    <x-navigation.navbar />

    <main
        class="
            mx-auto
            w-full max-w-7xl
            px-4 py-8
            sm:px-6
            lg:px-8
        ">
        {{ $slot }}
    </main>

</body>

</html>