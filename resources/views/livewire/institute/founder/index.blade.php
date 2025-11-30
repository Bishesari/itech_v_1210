<?php

use App\Jobs\SmsPass;
use App\Models\Contact;
use App\Models\Institute;
use App\Models\InstituteRoleUser;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use App\Rules\NCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component {
    #[Locked]
    public Institute $institute;
    public ?int $user_id = null;
    public string $n_code = '';
    public string $f_name_fa = '';
    public string $l_name_fa = '';
    public array $mobiles = [];
    public string $mobile_nu = '';
    public ?Profile $profile = null;
    public Collection $founders;

    public function mount(): void
    {
        $this->getFounders();

    }

    public function getFounders(): void
    {
        $this->founders = $this->institute->usersByRole('founder')->get();
    }


    public function profile_existence(): void
    {
        $this->validate([
            'n_code' => ['required', 'digits:10', new NCode],
        ]);
        $profile = Profile::where('n_code', $this->n_code)->first();
        if ($profile) {
            $this->profile = $profile;
            $this->f_name_fa = $profile->f_name_fa ?? '';
            $this->l_name_fa = $profile->l_name_fa ?? '';
            $this->mobiles = $profile->user->contacts->pluck('mobile_nu')->toArray();
        }

        $this->modal('create-founder')->close();
        $this->modal('create-founder-profile')->show();
    }

    public function create_founder_role(): void
    {
        // ✅ 1. اعتبارسنجی ورودی‌ها
        $this->validate([
            'f_name_fa' => ['required', 'string', 'max:50'],
            'l_name_fa' => ['required', 'string', 'max:50'],
            'mobile_nu' => ['required', 'starts_with:09', 'digits:11'],
        ]);

        // ✅ 2. اگر پروفایل وجود دارد، بروزرسانی کن
        if ($this->profile) {
            $this->user_id = $this->profile->user_id;
            $this->profile->update([
                'f_name_fa' => $this->f_name_fa,
                'l_name_fa' => $this->l_name_fa,
            ]);
            $user = $this->profile->user;

        } else {
            // ✅ 3. ساخت کاربر جدید
            $pass = simple_pass(8);
            $user = User::create([
                'user_name' => $this->n_code,
                'password' => $pass
            ]);
            $this->user_id = $user->id;
            $user->profile()->create([
                'identifier_type' => 'national_id',
                'n_code' => $this->n_code,
                'f_name_fa' => $this->f_name_fa,
                'l_name_fa' => $this->l_name_fa,
            ]);
            SmsPass::dispatch($this->mobile_nu, $this->n_code, $pass);
        }

        // ✅ 4. ثبت یا بازیابی مخاطب (مطمئن شو همیشه وجود داره)
        $contact = Contact::firstOrCreate(['mobile_nu' => $this->mobile_nu]);
        $user->contacts()->syncWithoutDetaching([$contact->id]);

        // ✅ 5. گرفتن نقش founder از جدول roles
        $founderRoleId = Role::where('name_en', 'founder')->value('id');
        // اگر نقشی با این نام پیدا نشد، می‌تونیم جلوگیری کنیم
        if (!$founderRoleId) {
            $this->dispatch('toast', message: 'نقش founder در جدول roles یافت نشد.', type: 'error');
            return;
        }

        // ✅ 6. بررسی تکرار در جدول pivot
        $record = InstituteRoleUser::firstOrCreate([
            'institute_id' => $this->institute->id,
            'user_id' => $this->user_id,
            'role_id' => $founderRoleId,
            'assigned_by' => Auth::user()->id,
        ]);

        if (!$record->wasRecentlyCreated) {
            $this->dispatch('toast', message: 'این کاربر قبلاً به‌عنوان موسس ثبت شده است.', type: 'warning');
            return;
        }
        $this->dispatch('toast', message: 'موسس با موفقیت ثبت شد.', type: 'success');
        $this->modal('create-founder-profile')->close();
        $this->getFounders();
    }

}; ?>

<section class="w-full">
    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{$institute['full_name']}}</flux:heading>
        <flux:subheading size="lg" class="mb-2">{{__('لیست موسسان آموزشگاه')}}</flux:subheading>
        <flux:separator variant="subtle"/>
    </div>
    <div class="mb-2">
        <flux:modal.trigger name="create-founder">
            <flux:button variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('جدید')}}</flux:button>
        </flux:modal.trigger>
        <flux:button variant="ghost" size="sm" href="{{route('institutes.for.sa')}}" class="cursor-pointer"
                     wire:navigate>{{__('بازگشت')}}</flux:button>
        <flux:separator class="mt-2" variant="subtle"/>
    </div>

    <flux:table class="inline-block text-center">
        <flux:table.columns>
            <flux:table.column> {{__('شناسه')}} </flux:table.column>
            <flux:table.column> {{__('نام کاربری')}} </flux:table.column>
            <flux:table.column> {{__('نام و نام خانوادگی')}} </flux:table.column>

            <flux:table.column>{{__('عملیات')}}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($founders as $founder)
                <flux:table.row :key="$founder->id" class="dark:hover:bg-zinc-900 transition hover:bg-zinc-100">
                    <flux:table.cell>{{ $founder->id }}</flux:table.cell>
                    <flux:table.cell>{{ $founder->user_name }}</flux:table.cell>
                    <flux:table.cell>{{ $founder->profile->f_name_fa . '، ' . $founder->profile->l_name_fa }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button wire:click="edit({{ $founder }})" variant="primary" color="teal" size="xs"
                                     class="cursor-pointer">{{__('ویرایش')}}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>


    <flux:modal name="create-founder" :show="$errors->isNotEmpty()" focusable class="w-80 md:w-96"
                :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('کد ملی موسس را وارد کنید') }}</flux:heading>
                <flux:text class="mt-2">{{ __('اگر پروفایل موجود باشد، نمایش داده خواهد شد.') }}</flux:text>
            </div>
            <form wire:submit="profile_existence" class="flex flex-col gap-6" autocomplete="off">
                <flux:input wire:model="n_code" :label="__('کدملی:')" type="text" class:input="text-center"
                            maxlength="10" required autofocus style="direction:ltr"/>
                <div class="flex justify-between space-x-2 rtl:space-x-reverse flex-row-reverse">
                    <flux:button variant="primary" color="violet" type="submit"
                                 class="cursor-pointer">{{ __('ادامه') }}</flux:button>
                    <flux:modal.close>
                        <flux:button variant="filled" class="cursor-pointer">{{ __('انصراف') }}</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="create-founder-profile" :show="$errors->isNotEmpty()" focusable class="w-80 md:w-96"
                :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('تکمیل اطلاعات موسس.') }}</flux:heading>
                <flux:text class="mt-2">{{ __('شماره موبایل تکراری هم باشد، وارد کنید.') }}</flux:text>
            </div>
            <form wire:submit="create_founder_role" class="flex flex-col gap-5" autocomplete="off">
                <flux:input readonly wire:model="n_code" :label="__('کدملی:')" type="text" class:input="text-center"
                            maxlength="10" style="direction:ltr"/>
                <flux:input wire:model="f_name_fa" :label="__('نام:')" type="text"
                            class:input="text-center"
                            maxlength="30" required autofocus/>
                <flux:input wire:model="l_name_fa" :label="__('نام خانوادگی:')" type="text"
                            class:input="text-center"
                            maxlength="40" required/>
                <flux:input wire:model="mobile_nu" :label="__('شماره موبایل:')" type="text"
                            class:input="text-center"
                            maxlength="11"/>
                <flux:separator text="شماره های موجود"/>
                <div class="flex justify-around">
                    @foreach($mobiles as $mobile)
                        <flux:badge variant="pill">{{$mobile}}</flux:badge>
                    @endforeach
                </div>
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


</div>
