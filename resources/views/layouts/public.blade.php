<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=syne:400,500,600,700,800|pacifico:400&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-slate-950 text-white">
        <div class="flex min-h-screen flex-col">
            <x-public-nav />
            <main class="flex-1">
                {{ $slot }}
            </main>
            <x-public-footer />
        </div>

        <button
            type="button"
            x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => { show = window.scrollY > 400 })"
            x-show="show"
            style="display: none;"
            x-transition
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            aria-label="Back to top"
            class="fixed bottom-6 right-6 z-30 flex h-11 w-11 items-center justify-center rounded-xl bg-lime-400 text-slate-950 shadow-lg shadow-lime-500/20 hover:bg-lime-300"
        >
            <x-sidebar-icon name="chevron" class="h-5 w-5 -rotate-90" />
        </button>
    </body>
</html>
