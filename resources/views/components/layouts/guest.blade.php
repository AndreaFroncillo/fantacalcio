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
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>
</body>

</html>