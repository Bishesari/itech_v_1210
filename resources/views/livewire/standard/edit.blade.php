<?php

use App\Models\Standard;
use Livewire\Volt\Component;

new class extends Component {
    public int $id;
    public Standard $standard;

    public int $field_id;
    public string $code;
    public string $name_fa;
    public string $name_en;
    public string $nazari_h = '0';
    public string $amali_h = '0';
    public string $karvarzi_h = '0';
    public string $project_h = '0';
    public string $sum_h = '0';

    public function mount(Standard $standard)
    {
        $this->standard = $standard;
        $this->id = $standard['id'];

        $this->field_id = $standard['field_id'];
        $this->code = $standard['code'];
        $this->name_fa = $standard['name_fa'];
        $this->name_en = $standard['name_en'];
        $this->nazari_h = $standard['nazari_h'];
        $this->amali_h = $standard['amali_h'];
        $this->karvarzi_h = $standard['karvarzi_h'];
        $this->project_h = $standard['project_h'];
        $this->sum_h = $standard['sum_h'];
    }
    protected function rules(): array
    {
        return [
            'field_id' => ['required'],
            'code' => ['required'],
            'name_fa' => ['required', 'min:2'],
            'name_en' => ['required', 'min:2'],
            'nazari_h' => ['required', 'numeric'],
            'amali_h' => ['required', 'numeric'],
            'karvarzi_h' => ['required', 'numeric'],
            'project_h' => ['required', 'numeric'],
            'sum_h' => ['required', 'numeric'],
        ];
    }
    public function calc_sum(): void
    {
        $this->validateOnly('nazari_h');
        $this->validateOnly('amali_h');
        $this->validateOnly('karvarzi_h');
        $this->validateOnly('project_h');
        $this->sum_h = $this->nazari_h + $this->amali_h + $this->karvarzi_h + $this->project_h;
    }

    public function update()
    {
        $this->calc_sum();
        Standard::find($this->id)->update($this->validate());
        $this->redirectRoute('standards');
    }

}; ?>

<div>
    <section class="w-full">

        <div class="relative w-full mb-2">
            <flux:heading size="xl" level="1">{{ __('استانداردهای آموزشی') }}</flux:heading>
            <flux:text color="teal" size="lg" class="my-2">{{ __('بخش ویرایش استاندارد') }}</flux:subheading>
            <flux:separator variant="subtle"/>
        </div>

        <form wire:submit="update" class="grid gap-5 mt-5 sm:w-[400px]" autocomplete="off" autofocus>

            <flux:input wire:model="code" label="کد استاندارد" type="text" class:input="text-center" autofocus required dir="ltr"/>
            <flux:select wire:model="field_id" variant="listbox" placeholder="یک رشته را انتخاب کنید ..." label="رشته"
                         searchable>
                @foreach (\App\Models\Field::all() as $field)
                    <flux:select.option value="{{$field->id}}">{{ $field->title }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input wire:model="name_fa" label="نام فارسی" type="text" class:input="text-center" required/>
            <flux:input wire:model="name_en" label="نام لاتین" type="text" class:input="text-center" required dir="ltr"/>

            <div class="flex space-x-3">
                <flux:input wire:model="nazari_h" label="ساعت نظری" type="text" class:input="text-center" required dir="ltr"/>
                <flux:input wire:model="amali_h" label="ساعت عملی" type="text" class:input="text-center" required dir="ltr"/>
                <flux:input wire:model="karvarzi_h" label="ساعت کارورزی" type="text" class:input="text-center" required dir="ltr"/>
            </div>

            <div class="flex space-x-3">
                <flux:input wire:model="project_h" label="ساعت پروژه" type="text" class:input="text-center" required dir="ltr"/>
                <flux:input wire:model="sum_h" label="مجموع(اتوماتیک)" type="text" class:input="text-center" disabled dir="ltr"/>
            </div>

            <div class="flex justify-between flex-row-reverse">
                <flux:button type="submit" variant="primary" color="teal" size="sm"
                             class="cursor-pointer">{{__('ویرایش')}}</flux:button>
                <flux:button wire:click="calc_sum" variant="primary" color="yellow" size="sm"
                             class="cursor-pointer">{{__('محاسبه مجموع')}}</flux:button>
                <flux:button href="{{route('standards')}}" variant="primary" color="zinc" wire:navigate
                             size="sm">{{__('انصراف')}}</flux:button>
            </div>
        </form>
    </section>
</div>
