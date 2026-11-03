<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Tshaprint Solutions') }} - Commercial Retail ERP</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Offline-Safe Vite Asset Resolution -->
    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @media print {
            .no-print, header, aside, nav, button, .btn, .card-tools, form {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
        }
    </style>
    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">
    <div class="min-h-screen flex">
        @include('layouts.sidebar')
        <div class="flex-1 flex flex-col min-w-0">
            @include('layouts.navigation')
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @yield('scripts')
</body>
</html>
