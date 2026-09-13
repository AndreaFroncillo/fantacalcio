<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        {{ $title ?? config('app.name') }}
    </title>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    <x-navigation.navbar />

    <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>
</body>

</html>