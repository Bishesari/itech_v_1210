<?php

use App\Models\Institute;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {

    #[Validate('required|min:2')]
    public string $short_name = '';
    #[Validate('required|min:3')]
    public string $full_name = '';
    #[Validate('required|unique:institutes|size:3')]
    public string $abb = '';
    public int $remain_credit = 0;

    public function create_institute(): void
    {
        $this->abb = strtoupper($this->abb);
        $this->validate();
        $institute = Institute::create([
            'short_name' => $this->short_name,
            'full_name' => $this->full_name,
            'abb' => $this->abb,
            'remain_credit' => $this->remain_credit,
        ]);

        // دریافت نقش موسس (Founder)
        $founderRoleId = \App\Models\Role::where('name_en', 'Founder')->value('id');

        // اتصال کاربر جاری به آموزشگاه به عنوان موسس
        auth()->user()->institutes()->attach($institute->id, [
            'role_id' => $founderRoleId,
            'assigned_by' => auth()->id(), // خود کاربر، یا می‌تونی null بزاری
        ]);

        $this->modal('create_institute')->close();
        $this->dispatch('institute-created');
        $this->reset();
        Flux::toast(
            heading: 'انجام شد.',
            text: 'آموزشگاه جدیدی افزوده شد.',
            variant: 'success'
        );
    }


}; ?>

<section>

    <flux:modal.trigger name="create_institute">
        <flux:button variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('افزودن')}}</flux:button>
    </flux:modal.trigger>
    <flux:modal name="create_institute" :show="$errors->isNotEmpty()" focusable class="w-80 md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('افزودن آموزشگاه جدید') }}</flux:heading>
                <flux:text class="mt-2">{{ __('اطلاعات مربوط به آموزشگاه را وارد نمایید.') }}</flux:text>
            </div>
            <form wire:submit="create_institute" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="short_name" :label="__('نام کوتاه فارسی')" type="text" class:input="text-center"
                            maxlength="25" required autofocus/>

                <flux:input wire:model="full_name" :label="__('نام کامل')" type="text" class:input="text-center"
                            maxlength="50" required/>

                <flux:input wire:model="abb" :label="__('علامت اختصاری')" type="text" class:input="text-center"
                            maxlength="3" required style="direction:ltr"/>

                <div class="flex justify-between space-x-2 rtl:space-x-reverse flex-row-reverse">
                    <flux:button variant="primary" color="green" type="submit" size="sm"
                                 class="cursor-pointer">{{ __('ثبت') }}</flux:button>
                    <flux:modal.close>
                        <flux:button size="sm" variant="filled" class="cursor-pointer">{{ __('انصراف') }}</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
