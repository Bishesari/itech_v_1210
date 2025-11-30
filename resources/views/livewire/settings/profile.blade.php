<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $user_name = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->user_name = Auth::user()->user_name;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'user_name' => [
                'required',
                'string',
                'max:30',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('نام کاربری')" :subheading="__('نام کاربری خود را ویرایش کنید.')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6" autocomplete="off">
            <flux:input wire:model="user_name" :label="__('نام کاربری')" type="text" required dir="ltr" class:input="text-center" maxlength="30"/>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" color="yellow" size="sm" type="submit" class="w-full cursor-pointer" data-test="update-password-button">
                        {{ __('ذخیره تغییرات') }}
                    </flux:button>
                </div>
                <x-action-message class="me-3 text-green-500" on="profile-updated">
                    {{ __('ذخیره شد.') }}
                </x-action-message>
            </div>
        </form>

        {{--        <livewire:settings.delete-user-form />--}}
    </x-settings.layout>
</section>

