<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Station</title>
    <script>
    (function(){
        var t = localStorage.getItem('station-theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
    </script>

    <!-- Station Dashboard CSS -->
    <link rel="stylesheet" href="/vendor/station/css/app.css">

    <!-- Ziggy Routes -->
    @routes

    @inertiaHead
</head>
<body class="font-sans antialiased bg-background text-foreground">
    @inertia

    <!-- Station Dashboard Assets -->
    <script type="module" src="/vendor/station/js/app.js"></script>
</body>
</html>
