<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')
</head>

<body class="min-h-screen flex flex-col bg-white dark:bg-zinc-800 antialiased">
<flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">

    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:brand href="#" class="max-lg:hidden">
        <x-slot name="logo" class="size-22">
            <x-logo class="text-zinc-700 dark:text-zinc-300"/>
        </x-slot>
    </flux:brand>

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>{{ __('صفحه اول') }}</flux:navbar.item>
        <flux:navbar.item icon="inbox" :href="route('exams')" :current="request()->routeIs('exams')" wire:navigate>{{ __('آزمونها') }}</flux:navbar.item>
    </flux:navbar>

    <flux:spacer />

    <flux:navbar class="me-4">
        <flux:navbar.item icon="magnifying-glass" href="#" label="Search" />
        <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" class="cursor-pointer" />
    </flux:navbar>
    <livewire:auth.log-reg-modal/>
</flux:header>



<flux:sidebar sticky collapsible="mobile" class="lg:hidden bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.header>
        <flux:brand href="#">
            <x-slot name="logo" class="size-16">
                <x-logo class="text-zinc-700 dark:text-zinc-300"/>
            </x-slot>
        </flux:brand>

        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>{{ __('صفحه اول') }}</flux:sidebar.item>
        <flux:sidebar.item icon="inbox" :href="route('exams')" :current="request()->routeIs('exams')" wire:navigate>{{ __('آزمونها') }}</flux:sidebar.item>
    </flux:sidebar.nav>
</flux:sidebar>

<flux:main>
    {{ $slot }}
</flux:main>



@fluxScripts
</body>
</html>
