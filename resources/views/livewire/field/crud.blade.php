<?php

use App\Models\Field;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public $sortBy = 'id';
    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
    #[Computed]
    public function fields()
    {
        return Field::query()
            ->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    #[On('field-created')]
    public function reset_page(): void
    {
        $this->resetPage();
        $this->reset('sortBy');
        $this->reset('sortDirection');
    }

    public string $title = '';

    protected function rules(): array
    {
        return [
            'title' => ['required', 'min:2']
        ];
    }

    public function create_field(): void
    {
        Field::create($this->validate());
        $this->modal('create-field')->close();
        $this->dispatch('field-created');
        $this->reset();
        Flux::toast(
            heading: 'انجام شد.',
            text: 'رشته جدیدی افزوده شد.',
            variant: 'success',
            position: 'top end',
            duration: 3000
        );
    }

    public ?Field $editing_field = null;

    public function edit(Field $field): void
    {
        $this->editing_field = $field;
        $this->title = $field['title'];
        $this->modal('edit-field')->show();
    }

    public function update(): void
    {
        $this->editing_field->update($this->validate());
        $this->modal('edit-field')->close();
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
        $this->reset('title');
    }

}; ?>

<section class="w-full">
    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{ __('رشته های آموزشی') }}</flux:heading>
        <flux:subheading size="lg" class="mb-2">{{ __('بخش مدیریت رشته های آموزشی') }}</flux:subheading>
        <flux:separator variant="subtle"/>
    </div>
    <div class="mb-2">

        <flux:modal.trigger name="create-field">
            <flux:button variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('جدید')}}</flux:button>
        </flux:modal.trigger>

        <flux:separator class="mt-2" variant="subtle"/>
    </div>

    <flux:table :paginate="$this->fields" class="inline-block text-center">
        <flux:table.columns >
            <flux:table.column align="center" sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{__('شناسه')}}
            </flux:table.column>
            <flux:table.column align="center" sortable :sorted="$sortBy === 'title'" :direction="$sortDirection"
                               wire:click="sort('title')">{{__('عنوان')}}
            </flux:table.column>
            <flux:table.column>{{__('عملیات')}}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->fields as $field)
                <flux:table.row :key="$field->id" class="dark:hover:bg-zinc-900 transition hover:bg-zinc-100">
                    <flux:table.cell>{{ $field->id }}</flux:table.cell>
                    <flux:table.cell>{{ $field->title }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button wire:click="edit({{ $field }})" variant="primary" color="teal" size="xs"
                                     class="cursor-pointer">{{__('ویرایش')}}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <!-- Create Modal -->
    <flux:modal name="create-field" focusable class="w-80 md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">درج رشته جدید</flux:heading>
                <flux:text class="mt-2">اطلاعات رشته آموزشی</flux:text>
            </div>
            <form wire:submit="create_field" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="title" label="عنوان رشته آموزشی" autofocus class:input="text-center"
                            maxlength="30" required/>
                <div class="flex">
                    <flux:spacer/>
                    <flux:button type="submit" variant="primary" color="sky" size="sm"
                                 class="cursor-pointer">{{__('ذخیره')}}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Edit Modal -->
    <flux:modal @close="reset_prop" name="edit-field" focusable class="w-80 md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش رشته</flux:heading>
                <flux:text class="mt-2">اطلاعات رشته آموزشی</flux:text>
            </div>
            <form wire:submit="update" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="title" label="عنوان رشته آموزشی" autofocus class:input="text-center"
                            maxlength="30" required/>
                <div class="flex">
                    <flux:spacer/>
                    <flux:button type="submit" variant="primary" color="teal" size="sm"
                                 class="cursor-pointer">{{__('ویرایش')}}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</section>
