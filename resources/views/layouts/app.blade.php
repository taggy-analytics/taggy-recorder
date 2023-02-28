<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <wireui:scripts />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <livewire:styles />

</head>
<body class="font-sans antialiased overscroll-none">
<div class="min-h-screen flex flex-col">
    <div class="flex-1">
        {{ $slot }}
    </div>
</div>
<livewire:scripts />
</body>
</html>
