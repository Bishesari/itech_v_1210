<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {

    public string $context = 'modal'; // modal | page

    public string $user_name = '';
    public string $password = '';
    public bool $remember = false;

    public function mount(string $context = 'modal'): void
    {
        $this->context = $context;
    }
    public function login(): void
    {
        if (! session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }
        $key = Str::lower($this->user_name ?: 'guest') . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('user_name', 'تلاش‌های ناموفق زیاد! یک دقیقه دیگر تلاش کنید.');
            return;
        }

        if (Auth::attempt(['user_name' => $this->user_name, 'password' => $this->password], $this->remember)) {

            session()->regenerate();

            RateLimiter::clear($key);
            $roles = Auth::user()->getAllRolesWithBranches();
            if ($roles->count() === 1) {
                $role = $roles->first();
                session([
                    'active_role_id' => $role->role_id,
                    'active_branch_id' => $role->branch_id, // null برای global
                ]);
                if ($this->context === 'modal') {
                    $this->dispatch('reloadPage');
                } else {
                    $this->redirectIntended('dashboard', navigate: true);
                }
            } else {
                $this->redirect('select_role', navigate: true);
            }
            return;
        }
        RateLimiter::hit($key);
        $this->addError('password', 'نام کاربری یا رمز عبور اشتباه است.');
    }
};

?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('ورود به حساب کاربری')"
                   :description="__('نام کاربری (کدملی) و پسورد قبلا پیامک شده است.')"/>
    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>
    <form wire:submit.prevent="login" class="space-y-4 flex flex-col gap-3" autocomplete="off">
        <x-my.flt_lbl name="user_name" label="{{__('نام کاربری:')}}" dir="ltr" maxlength="25"
                      class="tracking-wider font-semibold" autofocus required/>

        <x-my.flt_lbl name="password" type="password" label="{{__('کلمه عبور:')}}" dir="ltr" maxlength="25"
                      class="tracking-wider font-semibold" required/>

        <div class="flex justify-between">
            <!-- Remember Me -->
            <flux:field variant="inline">
                <flux:checkbox wire:model="remember" class="cursor-pointer"/>
                <flux:label class="cursor-pointer">{{__('بخاطرسپاری')}}</flux:label>
            </flux:field>
            <flux:link class="text-sm" :href="route('forgotten.password')" wire:navigate tabindex="-1">
                {{ __('ریست کلمه عبور') }}
            </flux:link>
        </div>

        <div class="flex items-center justify-end">
            <flux:button variant="primary" color="violet" type="submit" class="w-full cursor-pointer"
                         data-test="login-button">
                {{ __('ورود') }}
            </flux:button>
        </div>
    </form>

    @if($context === 'modal')
        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('حساب کاربری ندارید؟') }}</span>
            <flux:modal.trigger name="register">
                <flux:button variant="ghost" icon:trailing="arrow-up-left"
                             x-on:click="$flux.modal('login').close()" size="sm"
                             class="cursor-pointer">{{ __('ثبت نام کنید.') }}</flux:button>
            </flux:modal.trigger>
        </div>
    @else
        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('حساب کاربری ندارید؟') }}</span>
            <flux:button :href="route('register')" wire:navigate variant="ghost" icon:trailing="arrow-up-left" size="sm"
                         class="cursor-pointer">{{ __('ثبت نام کنید.') }}
            </flux:button>
        </div>
    @endif

</div>
