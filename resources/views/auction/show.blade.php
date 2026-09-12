<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Asta Fantacalcio</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <main data-auction-ulid="{{ $auction->ulid }}">
        <h1>Asta</h1>

        <p>{{ $auction->ulid }}</p>
    </main>
</body>

</html>
