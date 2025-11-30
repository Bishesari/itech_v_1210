<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('تم صفحات')" :subheading=" __('تم تیره یا روشن را برای صفحات خود انتخاب کنید.')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun" class="cursor-pointer">{{ __('روشن') }}</flux:radio>
            <flux:radio value="dark" icon="moon" class="cursor-pointer">{{ __('تیره') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop" class="cursor-pointer">{{ __('سیستم') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
