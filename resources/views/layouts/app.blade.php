<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#060608">
    <link rel="icon" href="{{ $brandFaviconUrl ?? asset('favicon.ico') }}">

    <script>
        // Apply saved theme before paint to avoid a flash
        if (localStorage.getItem('theme') === 'light') document.documentElement.classList.add('light');
    </script>

    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'QuizArena') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans text-ink-200 antialiased">
    @auth
        @if (Auth::user()->type == 'admin')
            @include('layouts.partials.admin-shell')
        @else
            @include('layouts.partials.student-shell')
        @endif

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    @else
        @include('layouts.partials.guest-shell')
    @endauth

    @stack('scripts')
</body>

</html>
