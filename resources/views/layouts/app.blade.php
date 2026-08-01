<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-950 text-white" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen lg:flex">
            <!-- Desktop sidebar -->
            <aside class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-white/10 lg:bg-slate-900">
                @include('layouts.sidebar')
            </aside>

            <!-- Mobile slide-over sidebar -->
            <div x-show="sidebarOpen" class="relative z-50 lg:hidden" style="display: none;">
                <div x-show="sidebarOpen"
                     x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-950/80" @click="sidebarOpen = false"></div>

                <div class="fixed inset-0 flex">
                    <div x-show="sidebarOpen"
                         x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                         x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                         class="relative flex w-full max-w-xs flex-1 bg-slate-900 ring-1 ring-white/10">
                        <button type="button" class="absolute right-3 top-3 text-slate-400 hover:text-white" @click="sidebarOpen = false">
                            <x-sidebar-icon name="x" class="h-6 w-6" />
                        </button>
                        @include('layouts.sidebar')
                    </div>
                </div>
            </div>

            <div class="flex-1 lg:pl-64">
                <!-- Top bar -->
                <div class="sticky top-0 z-30 flex items-center gap-4 border-b border-white/10 bg-slate-950/90 px-4 py-3 backdrop-blur sm:px-6 lg:px-8">
                    <button type="button" class="text-slate-400 hover:text-white lg:hidden" @click="sidebarOpen = true">
                        <x-sidebar-icon name="menu" class="h-6 w-6" />
                    </button>

                    <div class="flex-1"></div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 rounded-md px-2 py-1.5 text-sm font-medium text-slate-300 hover:text-white focus:outline-none">
                                <span>{{ Auth::user()->name }}</span>
                                <x-sidebar-icon name="chevron" class="h-4 w-4 rotate-90" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="border-b border-white/10 bg-slate-900/30">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
