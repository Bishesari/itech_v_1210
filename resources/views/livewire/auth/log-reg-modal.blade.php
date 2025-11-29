<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public string $mode = '';
    public string $user_name = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $key = Str::lower($this->user_name) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('user_name', __('تلاش‌های ناموفق زیاد! یک دقیقه دیگر تلاش کنید.'));
            return;
        }
        if (Auth::attempt(['user_name' => $this->user_name, 'password' => $this->password], $this->remember))
        {
            session()->regenerate();
            RateLimiter::clear($key);   // موفق شد → ریست

            $roles = Auth::user()->getAllRolesWithInstitutes();

            if ($roles->count() === 1) {
                $role = $roles->first();
                session([
                    'active_role_id' => $role->role_id,
                    'active_institute_id' => $role->institute_id,
                ]);
                return;
            }
            else{
                $this->redirectRoute('select_role');
            }
        }
        RateLimiter::hit($key);  // اشتباه → افزایش شمارنده
        $this->addError('password', __('نام کاربری یا رمز عبور اشتباه است.'));
    }
    public function set_mode($mode): void
    {
        # mode is login or register
        $this->mode = $mode;
    }

    public function reset_all(): void
    {
        $this->reset();
        $this->resetErrorBag();
    }

}; ?>

<div>
    @auth
        <flux:dropdown position="top" align="start">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.png"/>
            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>Olivia Martin</flux:menu.radio>
                    <flux:menu.radio>Truly Delta</flux:menu.radio>
                </flux:menu.radio.group>
                <flux:menu.separator/>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                                    data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>

                <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    @endauth

    @guest
        {{---------- Login Button ----------}}
        <flux:modal.trigger name="login">
            <flux:button variant="subtle" size="sm" class="cursor-pointer" wire:click="set_mode('login')">
                {{__('ورود')}}
            </flux:button>
        </flux:modal.trigger>

        {{---------- Login Modal ----------}}
        <flux:modal name="login" class="w-96" focusable :dismissible="false" @close="reset_all">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{__('ورود به حساب کاربری')}}</flux:heading>
                    <flux:text class="mt-2">{{__('نام کاربری (کدملی) و پسورد قبلا پیامک شده است.')}}</flux:text>
                </div>

                <form wire:submit="login" class="flex flex-col gap-6" autocomplete="off">
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
                        @if (Route::has('password.request'))
                            <flux:link class="text-sm" :href="route('register')" wire:navigate tabindex="-1">
                                {{ __('ریست کلمه عبور') }}
                            </flux:link>
                        @endif
                    </div>

                    <div class="flex items-center justify-end">
                        <flux:button variant="primary" color="violet" type="submit" class="w-full cursor-pointer"
                                     data-test="login-button">
                            {{ __('ورود') }}
                        </flux:button>
                    </div>
                </form>
                @if (Route::has('register'))
                    <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('حساب کاربری ندارید؟') }}</span>
                        <flux:link :href="route('register')" wire:navigate>{{ __('ثبت نام کنید.') }}</flux:link>
                    </div>
                @endif
            </div>

        </flux:modal>

        {{-- Registration Part --}}
        <flux:modal.trigger name="register">
            <flux:button variant="subtle" size="sm" class="cursor-pointer">{{__('ثبت نام')}}</flux:button>
        </flux:modal.trigger>

    @endguest
</div>
