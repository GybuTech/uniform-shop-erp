<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Tshaprint Solutions') }}</title>

        <!-- Offline System Font Stack & Global High-Visibility Caret -->
        <style>
            body {
                font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            }
            input:not([type="button"]):not([type="submit"]):not([type="reset"]):not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
            textarea,
            select,
            [contenteditable="true"] {
                caret-color: #0f172a !important;
                cursor: text !important;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-100">
            <div class="text-center">
                <a href="/" class="flex flex-col items-center">
                    <div class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-600/30 mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold text-slate-900 tracking-tight">{{ \App\Models\BusinessSetting::get('business_name', 'Retail POS & ERP') }}</span>
                    <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">{{ \App\Models\BusinessSetting::get('business_tagline', 'Multi-Branch Retail ERP & POS') }}</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
