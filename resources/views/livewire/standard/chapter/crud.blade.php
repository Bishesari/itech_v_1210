<?php

use App\Models\Chapter;
use App\Models\Standard;
use Flux\Flux;
use Livewire\Volt\Component;

new class extends Component {
    public Standard $standard;

    public function mount(Standard $standard): void
    {
        $this->standard = $standard;
    }

    public $number;
    public string $title;
    protected function rules(): array
    {
        return [
            'number' => ['required', 'numeric'],
            'title' => ['required', 'min:2']
        ];
    }

    public function create_chapter(): void
    {
        $validated = $this->validate();
        $validated['standard_id'] = $this->standard->id;
        Chapter::create($validated);
        $this->reset_prop();
        $this->modal('create-chapter')->close();
        Flux::toast(
            heading: 'انجام شد.',
            text: 'سرفصل جدیدی افزوده شد.',
            variant: 'success',
            position: 'top end',
            duration: 3000
        );
    }

    public ?Chapter $editing_chapter = null;

    public function edit(Chapter $chapter): void
    {
        $this->editing_chapter = $chapter;

        $this->number = $chapter['number'];
        $this->title = $chapter['title'];
        $this->modal('edit-chapter')->show();
    }

    public function update(): void
    {
        $this->editing_chapter->update($this->validate());
        $this->modal('edit-chapter')->close();
        Flux::toast(
            heading: 'ویرایش انجام شد.',
            text: null,
            variant: 'warning',
            position: 'top end',
            duration: 3000
        );
    }

    public function reset_prop(): void
    {
        $this->resetExcept('standard');
    }

}; ?>

<section class="w-full">
    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{ __('سرفصلهای استاندارد آموزشی') }}</flux:heading>
        <flux:subheading size="lg" class="mb-2">
            <span dir="rtl">{{$standard->field->title . '، ' . $standard->name_fa .'، '}}</span>
            <span dir="ltr">{{'کد: ' . $standard->code }}</span>
        </flux:subheading>
        <flux:separator variant="subtle"/>
    </div>

    <div class="mb-2">

        <flux:modal.trigger name="create-chapter">
            <flux:button variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('جدید')}}</flux:button>
        </flux:modal.trigger>

        <flux:separator class="mt-2" variant="subtle"/>
    </div>

    <flux:table class="inline-block text-center">
        <flux:table.columns>
            <flux:table.column>{{__('شناسه')}}</flux:table.column>
            <flux:table.column>{{__('شماره فصل')}}</flux:table.column>
            <flux:table.column>{{__('عنوان')}}</flux:table.column>
            <flux:table.column>{{__('عملیات')}}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->standard->chapters as $chapter)
                <flux:table.row :key="$chapter->id" class="dark:hover:bg-zinc-900 transition hover:bg-zinc-100">
                    <flux:table.cell>{{ $chapter->id }}</flux:table.cell>
                    <flux:table.cell>{{ $chapter->number }}</flux:table.cell>
                    <flux:table.cell>{{ $chapter->title }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button wire:click="edit({{ $chapter }})" variant="primary" color="teal" size="xs"
                                     class="cursor-pointer">{{__('ویرایش')}}</flux:button>
                        <flux:button href="{{URL::signedRoute('create_question', ['sid'=>$chapter->standard->id, 'cid'=>$chapter->id] )}}"
                                     variant="primary" color="green" size="xs" class="cursor-pointer" wire:navigate>
                            {{__('سوالات')}}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <!-- Create Modal -->
    <flux:modal name="create-chapter" focusable class="w-80 md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">درج فصل جدید</flux:heading>
                <flux:text class="mt-2" color="sky">اطلاعات سرفصل آموزشی</flux:text>
            </div>
            <form wire:submit="create_chapter" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="number" label="شماره فصل" autofocus class:input="text-center" dir="ltr" required
                            maxlength="3"/>
                <flux:input wire:model="title" label="عنوان سرفصل" class:input="text-center" maxlength="100" required/>
                <div class="flex">
                    <flux:spacer/>
                    <flux:button type="submit" variant="primary" color="sky" size="sm"
                                 class="cursor-pointer">{{__('ذخیره')}}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Modal -->
    <flux:modal @close="reset_prop" variant="flyout" name="edit-chapter" focusable class="w-80 md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش سرفصل</flux:heading>
                <flux:text class="mt-2" color="teal">اطلاعات سرفصلهای آموزشی</flux:text>
            </div>
            <form wire:submit="update" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="number" label="شماره فصل" autofocus class:input="text-center" dir="ltr" required
                            maxlength="3"/>
                <flux:input wire:model="title" label="عنوان سرفصل" class:input="text-center" maxlength="100" required/>
                <div class="flex">
                    <flux:spacer/>
                    <flux:button type="submit" variant="primary" color="teal" size="sm"
                                 class="cursor-pointer">{{__('ویرایش')}}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>


</section>
