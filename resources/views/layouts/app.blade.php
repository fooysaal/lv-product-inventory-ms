<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Product Inventory System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite('resources/css/app.css')

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 text-gray-900 dark:text-gray-100 min-h-screen">
    <div id="app">
        @yield('content')
    </div>

    @vite('resources/js/app.js')
</body>

</html>
