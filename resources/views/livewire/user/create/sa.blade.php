<?php

use App\Models\Institute;
use App\Models\Profile;
use App\Models\Role;
use App\Rules\NCode;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component {

    public string $n_code = '';
    public string $mobile = '';

    public $institueId = null;

    public $roleId = null;

    #[Computed]
    public function shouldShowInstitute()
    {
        if (!$this->roleId) return true;

        $role = Role::find($this->roleId);

        return !in_array($role->name_en, ['SuperAdmin', 'Newbie']);
    }

}; ?>

<div>
    {{--    Create User Modal   --}}

    <flux:modal.trigger name="create-user">
        <flux:button variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('جدید')}}</flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-user" :show="$errors->isNotEmpty()" focusable class="w-80 md:w-96"
                :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('کد ملی را وارد کنید') }}</flux:heading>
                <flux:text class="mt-2">{{ __('اگر پروفایل موجود باشد، نمایش داده خواهد شد.') }}</flux:text>
            </div>
            <form wire:submit="profile_existence" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="n_code" :label="__('کدملی:')" type="text" class:input="text-center"
                            maxlength="10" required autofocus style="direction:ltr"/>

                <flux:input wire:model="mobile_nu" :label="__('شماره موبایل:')" type="text"
                            class:input="text-center"
                            maxlength="11"/>

                <flux:select wire:model.live="roleId" variant="listbox" :label="__('انتخاب نقش:')" placeholder="یک نقش انتخاب کنید..." searchable>
                    @foreach (Role::orderBy('name_fa')->get() as $role)
                        <flux:select.option value="{{ $role->id }}" wire:key="{{ $role->id }}">
                            {{ $role->name_fa }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                @if ($this->shouldShowInstitute)
                    <flux:select wire:model="instituteId" variant="listbox" :label="__('انتخاب آموزشگاه:')" placeholder="یک آموزشگاه انتخاب کنید..." searchable>
                        @foreach (Institute::orderBy('short_name')->get() as $institute)
                            <flux:select.option value="{{ $institute->id }}" wire:key="{{ $institute->id }}">
                                {{ $institute->short_name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                @endif


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
</div>
