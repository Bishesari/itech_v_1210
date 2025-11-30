<?php

use App\Models\Institute;
use App\Models\Role;
use Flux\Flux;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

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
    #[On('institute-created')]
    public function institutes()
    {
        $query = Institute::query();
        // فقط برای کاربران غیر سوپرادمین، آموزشگاه‌های مرتبط با نقش فعال را نمایش بده
        if (session('active_role_id') !== Role::where('name_en', 'SuperAdmin')->value('id')) {
            $activeInstituteId = session('active_institute_id');
            $query->where('id', $activeInstituteId);
        }
        // مرتب‌سازی
        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }
        return $query->paginate(10);
    }

    #[On('institute-created')]
    public function reset_page(): void
    {
        $this->resetPage();
    }


    public string $short_name = '';
    public string $full_name = '';
    public string $abb = '';
    public int $remain_credit;

    public int $editing_id = 0;

    public function edit(Institute $institute): void
    {
        $this->editing_id = $institute['id'];
        $this->short_name = $institute['short_name'];
        $this->full_name = $institute['full_name'];
        $this->abb = $institute['abb'];
        $this->remain_credit = $institute['remain_credit'];
        $this->modal('edit-institute')->show();
    }

    public function update(): void
    {
        $editing_institute = Institute::find($this->editing_id);

        if (($editing_institute['abb'] != $this->abb)) {
            $this->abb = strtoupper($this->abb);
            $validated = $this->validate([
                'short_name' => 'required|min:2',
                'full_name' => 'required|min:3',
                'abb' => 'required|unique:institutes|size:3',
                'remain_credit' => 'required|max:5',
            ]);
            $editing_institute->update($validated);
        } else {
            $validated = $this->validate([
                'short_name' => 'required|min:2',
                'full_name' => 'required|min:3',
                'abb' => 'required|size:3',
                'remain_credit' => 'required|max:5',
            ]);
            $editing_institute->update($validated);
        }
        $this->modal('edit-institute')->close();
        Flux::toast(
            heading: 'انجام شد.',
            text: 'اطلاعات آموزشگاه با موفقیت ویرایش شد.',
            variant: 'success'
        );
    }

    public function reset_edit(): void
    {
        $this->editing_id = 0;
    }

}; ?>

<section class="w-full">
    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{ __('آموزشگاههای من') }}</flux:heading>
        <flux:subheading size="lg" class="mb-2">{{ __('بخش مدیریت آموزشگاهها') }}</flux:subheading>
        <flux:separator variant="subtle"/>
    </div>
    <div class="mb-2">
        <livewire:institute.create.sa/>
        <flux:separator class="mt-2" variant="subtle"/>
    </div>

    <flux:table :paginate="$this->institutes" class="text-center inline">
        <flux:table.columns>
            <flux:table.column align="center" sortable :sorted="$sortBy === 'id'" :direction="$sortDirection"
                               wire:click="sort('id')">
                {{__('#')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'short_name'" :direction="$sortDirection"
                               wire:click="sort('short_name')">
                {{__('نام کوتاه')}}
            </flux:table.column>


            <flux:table.column align="center" sortable :sorted="$sortBy === 'full_name'" :direction="$sortDirection"
                               wire:click="sort('full_name')">
                {{__('نام کامل')}}
            </flux:table.column>

            <flux:table.column>{{__('موسس')}}</flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'abb'" :direction="$sortDirection"
                               wire:click="sort('abb')">
                {{__('نام اختصاری')}}
            </flux:table.column>

            <flux:table.column align="center" sortable :sorted="$sortBy === 'remain_credit'" :direction="$sortDirection"
                               wire:click="sort('remain_credit')">
                {{__('مانده اعتبار')}}
            </flux:table.column>

            <flux:table.column align="center">{{__('لوگو')}}</flux:table.column>

            <flux:table.column align="center">{{__('عملیات')}}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->institutes as $institute)

                <flux:table.row class="dark:hover:bg-zinc-900 transition hover:bg-zinc-100" wire:key="$institute->id">
                    <flux:table.cell class="whitespace-nowrap">{{ $institute->id }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $institute->short_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $institute->full_name }}</flux:table.cell>
                    <flux:table.cell>
                        @foreach($institute->usersByRole('Founder')->get() as $founder)
                            {{$founder->profile->f_name_fa}}
                            {{$founder->profile->l_name_fa}}
                            <br>
                        @endforeach

                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $institute->abb }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $institute->remain_credit }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $institute->logo_url }}</flux:table.cell>

                    <flux:table.cell>
                        <flux:button tooltip="ویرایش" wire:click="edit({{$institute}})" variant="ghost" size="sm"
                                     class="cursor-pointer">
                            <flux:icon.pencil-square variant="solid" class="text-amber-500 dark:text-amber-300 size-5"/>
                        </flux:button>
                        <flux:button tooltip="موسسان"
                                     href="{{URL::signedRoute('institute_founders', ['institute'=>$institute->id])}}"
                                     variant="ghost"
                                     size="sm" class="cursor-pointer" wire:navigate>
                            <flux:icon.users class="text-blue-500 size-5"/>
                        </flux:button>

                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <!-- Edit Modal -->
    <flux:modal @close="reset_edit" variant="flyout" position="left" name="edit-institute" :show="$errors->isNotEmpty()"
                focusable :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('ویرایش آموزشگاه') }}</flux:heading>
                <flux:text class="mt-2">{{ __('اطلاعات مربوط به آموزشگاه را وارد نمایید.') }}</flux:text>
            </div>
            <form wire:submit="update" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="short_name" :label="__('نام کوتاه فارسی')" type="text" class:input="text-center"
                            maxlength="25" required autofocus/>

                <flux:input wire:model="full_name" :label="__('نام کامل')" type="text" class:input="text-center"
                            maxlength="50" required/>

                <flux:input wire:model="abb" :label="__('علامت اختصاری')" type="text" class:input="text-center"
                            maxlength="3" required style="direction:ltr"/>

                <flux:input wire:model="remain_credit" :label="__('مانده اعتبار')" type="text" class:input="text-center"
                            maxlength="5" required style="direction:ltr"/>

                <div class="flex justify-between space-x-2 rtl:space-x-reverse flex-row-reverse">
                    <flux:button variant="primary" color="orange" type="submit" size="sm"
                                 class="cursor-pointer">{{ __('ویرایش') }}</flux:button>
                    <flux:modal.close>
                        <flux:button variant="filled" size="sm" class="cursor-pointer">{{ __('انصراف') }}</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </div>
    </flux:modal>
</section>


