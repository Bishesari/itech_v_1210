<?php

use App\Models\Standard;
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
    public function standards()
    {
        return Standard::query()
            ->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    #[On('standard-created')]
    public function reset_page(): void
    {
        $this->resetPage();
        $this->reset('sortBy');
        $this->reset('sortDirection');
    }


}; ?>

<section class="w-full">

    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{ __('استانداردهای آموزشی') }}</flux:heading>
        <flux:subheading size="lg" class="mb-2">{{ __('بخش مدیریت استانداردهای آموزشی') }}</flux:subheading>
        <flux:separator variant="subtle"/>
    </div>

    <div class="mb-2">
        <flux:modal.trigger name="create-standard">
            <flux:button href="{{route('create_standard')}}" variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('جدید')}}</flux:button>
        </flux:modal.trigger>
        <flux:separator class="mt-2" variant="subtle"/>
    </div>
    <flux:table :paginate="$this->standards" class="text-center">
        <flux:table.columns >

            <flux:table.column align="center" sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">{{__('شناسه')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'name_fa'" :direction="$sortDirection"
                               wire:click="sort('name_fa')">{{__('نام فارسی')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'code'" :direction="$sortDirection"
                               wire:click="sort('code')">{{__('کد استاندارد')}}
            </flux:table.column>

            <flux:table.column>{{__('رشته')}}</flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'is_active'" :direction="$sortDirection"
                               wire:click="sort('is_active')">{{__('فعال؟')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'nazari_h'" :direction="$sortDirection"
                               wire:click="sort('nazari_h')">{{__('نظری')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'amali_h'" :direction="$sortDirection"
                               wire:click="sort('amali_h')">{{__('عملی')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'karvarzi_h'" :direction="$sortDirection"
                               wire:click="sort('karvarzi_h')">{{__('کارورزی')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'project_h'" :direction="$sortDirection"
                               wire:click="sort('project_h')">{{__('پروژه')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'sum_h'" :direction="$sortDirection"
                               wire:click="sort('sum_h')">{{__('مجموع')}}
            </flux:table.column>

            <flux:table.column>{{__('عملیات')}}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->standards as $standard)
                <flux:table.row :key="$standard->id" class="dark:hover:bg-zinc-900 transition hover:bg-zinc-100">
                    <flux:table.cell>{{ $standard->id }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->name_fa }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->code }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->field->title }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->is_active }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->nazari_h }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->amali_h }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->karvarzi_h }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->project_h }}</flux:table.cell>
                    <flux:table.cell>{{ $standard->sum_h }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button href="{{URL::temporarySignedRoute('edit_standard',now()->addMinutes(10), ['standard'=>$standard])}}"
                                     variant="primary" color="teal" size="xs" class="cursor-pointer" wire:navigate>
                            {{__('ویرایش')}}
                        </flux:button>

                        <flux:button href="{{URL::temporarySignedRoute('chapters',now()->addMinutes(30), ['standard'=>$standard])}}"
                                     variant="primary" color="purple" size="xs" class="cursor-pointer" wire:navigate>
                            {{__('فصلها')}}
                        </flux:button>

                        <flux:button href="{{URL::signedRoute('questions', ['sid'=>$standard->id, 'cid'=>0] )}}"
                                     variant="primary" color="green" size="xs" class="cursor-pointer" wire:navigate>
                            {{__('سوالات')}}
                        </flux:button>

                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</section>
