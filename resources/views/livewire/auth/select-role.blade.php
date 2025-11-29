<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.guest')]
#[Title('انتخاب نقش')]
class extends Component {
    public $roles = [];
    public string $selectedRoleId = ''; // نقش انتخابی

    public function mount(): void
    {
        $this->roles = Auth::user()->getAllRolesWithInstitutes();
    }

    public function setRole($roleId, $instituteId): void
    {
        $this->selectedRoleId = $roleId;
        session([
            'active_role_id' => $roleId,
            'active_institute_id' => $instituteId ?? '',
        ]);
    }

    public function dashboard(): void
    {
        if (empty($this->selectedRoleId)) {
            $this->addError('role_id', 'لطفاً یک نقش انتخاب کنید.');
            return;
        }
        // ✅ همه‌چیز اوکیه، هدایت به داشبورد
        $this->redirectRoute('dashboard');
    }

}; ?>
<div class="flex flex-col gap-6">

    <!-- Header -->
    <div class="text-center space-y-2">
        <h1 class="text-xl text-gray-800 dark:text-gray-200 font-bold">
            {{ __('انتخاب نقش کاربری') }}
        </h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">
            {{ __('برای ورود، یکی از نقش‌های زیر را انتخاب کنید') }}
        </p>
    </div>

    <!-- Roles -->
    <flux:radio.group variant="cards" class="grid gap-4">
        @forelse($roles as $r)
            <flux:radio
                wire:click="setRole({{ $r->role_id }}, {{ $r->institute_id ?? 'null' }})"
                class="cursor-pointer {{ $selectedRoleId == $r->role_id ? 'text-green-600 dark:text-green-500' : 'text-gray-600 dark:text-gray-400' }} "
            >
                <div class="flex items-center justify-between w-full">
                    <!-- نقش (سمت راست) -->
                    <span class="font-semibold">
                        {{ $r->role_name }}
                    </span>

                    <!-- آموزشگاه (سمت چپ) -->
                    @if($r->institute_name)
                        <span class="text-sm">
                           {{__('آموزشگاه ')}} {{ $r->institute_name }}
                        </span>
                    @endif
                </div>
            </flux:radio>
        @empty
            <p class="text-center text-gray-500 dark:text-gray-400">شما هیچ نقشی ندارید.</p>
        @endforelse
    </flux:radio.group>

    <!-- Error -->
    @error('role_id')
    <p class="text-red-500 text-sm text-center">{{ $message }}</p>
    @enderror

    <!-- CTA Button -->
    <flux:button
        wire:click="dashboard"
        variant="primary"
        color="sky"
        class="cursor-pointer w-full py-2 text-sm font-medium mt-4"
    >
        {{ __('ادامه با نقش انتخابی') }}
    </flux:button>

</div>
