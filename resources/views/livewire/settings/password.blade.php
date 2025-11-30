<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('بروزرسانی کلمه عبور')" :subheading="__('برای امنیت بیشتر کلمه عبور خود را تنظیم نمایید.')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6" autocomplete="off">
            <flux:input
                wire:model="current_password"
                :label="__('کلمه عبور فعلی')"
                type="password"
                required
                viewable
                class:input="text-center tracking-widest font-semibold"
                dir="ltr" maxlength="25" autofocus
            />
            <flux:input
                wire:model="password"
                :label="__('کلمه عبور جدید')"
                type="password"
                required
                viewable
                class:input="text-center tracking-widest font-semibold"
                dir="ltr" maxlength="25"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('تکرار کلمه عبور جدید')"
                type="password"
                required
                viewable
                class:input="text-center tracking-widest font-semibold"
                dir="ltr" maxlength="25"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" color="yellow" size="sm" type="submit" class="w-full cursor-pointer" data-test="update-password-button">
                        {{ __('ذخیره تغییرات') }}
                    </flux:button>
                </div>
                <x-action-message class="me-3 text-green-500" on="password-updated">
                    {{ __('ذخیره شد.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
