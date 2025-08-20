<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Taggy</title>

        <link rel="icon" type="image/png" href="{{ Vite::asset('resources/images/favicon.png') }}">

        @fluxAppearance

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="bg-white shadow-2xl rounded-2xl p-8">
            {{ $slot }}
        </div>
    </div>
    @fluxScripts
    </body>
</html>
