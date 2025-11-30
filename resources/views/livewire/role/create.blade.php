<?php

use App\Models\Role;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {

    #[Validate('required|unique:roles|min:2')]
    public string $name_fa = '';
    #[Validate('required|unique:roles|min:3')]
    public string $name_en = '';

    public function create_role(): void
    {
        $this->validate();
        Role::create([
            'name_fa' => $this->name_fa,
            'name_en' => $this->name_en,
        ]);
        $this->modal('create_role')->close();
        $this->dispatch('role-created');
        $this->reset();
        Flux::toast(
            heading: 'انجام شد.',
            text: 'نقش کاربری جدیدی افزوده شد.',
            variant: 'success',
            position: 'bottom left'
        );
    }


}; ?>

<section>
    <flux:modal.trigger name="create_role">
        <flux:button variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('جدید')}}</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create_role" :show="$errors->isNotEmpty()" focusable class="w-80 md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('درج نقش جدید') }}</flux:heading>
                <flux:text class="mt-2">{{ __('توجه کنید این نقش را قبلا تعریف نکرده باشید.') }}</flux:text>
            </div>
            <form wire:submit="create_role" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="name_fa" :label="__('عنوان فارسی')" type="text" class:input="text-center"
                            maxlength="35" required autofocus/>
                <flux:input wire:model="name_en" :label="__('عنوان لاتین')" type="text" class:input="text-center"
                            maxlength="35" required style="direction:ltr"/>

                <div class="flex justify-between space-x-2 rtl:space-x-reverse flex-row-reverse">
                    <flux:button variant="primary" color="green" type="submit"
                                 class="cursor-pointer">{{ __('ثبت') }}</flux:button>
                    <flux:modal.close>
                        <flux:button variant="filled" class="cursor-pointer">{{ __('انصراف') }}</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
