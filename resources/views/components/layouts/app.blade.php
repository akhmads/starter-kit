<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Icons --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">

    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @filepondScripts
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-inter antialiased bg-base-200">

    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav id="nav" sticky full-width class="h-[65px] z-50">
        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>

            {{-- Brand --}}
            <x-app-brand />
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <div class="flex items-center gap-0.5 py-1">
                <x-theme-toggle class="btn btn-ghost btn-sm" />
                @if($user = auth()->user())
                {{-- <div wire:key="notification-navbar">
                    <livewire:notification.menu lazy />
                </div> --}}
                <x-dropdown>
                    <x-slot:trigger>
                        <x-button class="btn-ghost btn-sm" responsive>
                            <x-avatar :title="\Illuminate\Support\Str::limit($user->name, 20)" image="{{ $user->avatar ?? asset('assets/img/default-avatar.png') }}" class="h-6" />
                        </x-button>
                    </x-slot:trigger>
                    <x-menu-item title="My Profile" link="{{ route('users.profile') }}" />
                    <x-menu-separator />
                    <x-menu-item title="Log Out" link="{{ route('users.logout') }}" no-wire-navigate />
                </x-dropdown>
                @endif
            </div>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main with-nav full-width>

        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" class="bg-base-100 lg:bg-white lg:border-r lg:border-gray-200 dark:lg:bg-inherit dark:lg:border-none">

            {{-- MENU --}}
            <x-menu activate-by-route class="text-[13px] font-light">
                <x-menu-item title="Home" icon="o-home" link="/" />
                <x-menu-item title="Product" icon="o-computer-desktop" link="{{ route('product.index') }}" :hidden="auth()->user()->cannot('view product')" />

                <x-menu-sub title="Users" icon="o-users">
                    <x-menu-item title="Users" link="{{ route('users.index') }}" :hidden="auth()->user()->cannot('view users')" />
                    <x-menu-item title="Roles" link="{{ route('roles.index') }}" :hidden="auth()->user()->cannot('view roles')" />
                    <x-menu-item title="Permissions" link="{{ route('permissions.index') }}" :hidden="auth()->user()->cannot('view permissions')" />
                </x-menu-sub>
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    {{-- Theme toggle --}}
    <x-theme-toggle class="hidden" />

    @livewireScriptConfig
</body>
</html>
