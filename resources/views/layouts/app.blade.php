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
    <body class="font-sans antialiased bg-slate-100 text-slate-900">
        <div x-data="{ mobileOpen: false }" class="min-h-screen lg:flex lg:h-screen lg:overflow-hidden">
            <div class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:hidden">
                <button
                    type="button"
                    @click="mobileOpen = true"
                    class="inline-flex items-center justify-center rounded-md border border-slate-200 p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-800"
                    aria-label="Open sidebar"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <p class="text-sm font-semibold tracking-wide text-slate-700">RNM Hardware Management System</p>
            </div>

            <div
                x-show="mobileOpen"
                x-transition.opacity
                @click="mobileOpen = false"
                class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
                x-cloak
            ></div>

            <x-sidebar />

            <div class="flex-1 lg:h-screen lg:overflow-y-auto">
                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    @isset($header)
                        <header class="mb-5 border-b border-slate-200 pb-4">
                            <div class="flex items-center justify-between gap-6">
                                <div class="flex-shrink">
                                    {{ $header }}
                                </div>
                                <div class="text-right">
                                    @php
                                        $user = auth()->user();
                                        $role = null;
                                        if ($user) {
                                            $role = $user->role ?? ($user->getRoleNames()->first() ?? null);
                                        }
                                    @endphp
                                    @if($user)
                                        <div class="font-medium">{{ $user->name }}</div>
                                        @if($role)
                                            <div class="text-xs text-slate-500">{{ $role }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </header>
                    @endisset

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
