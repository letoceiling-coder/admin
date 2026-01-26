<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script>
        (function () {
            var t = localStorage.getItem('theme') || 'light';
            var html = document.documentElement;
            if (t === 'dark') {
                html.setAttribute('data-theme', 'dark');
                html.classList.add('dark');
                html.style.colorScheme = 'dark';
            } else {
                html.setAttribute('data-theme', 'light');
                html.style.colorScheme = 'light';
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
