<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Job Portal - React & Laravel</title>

        <!-- Vite Directives (Make sure app.jsx matches) -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
    <body>
        <!-- React will mount inside this div -->
        <div id="app"></div>
    </body>
</html>