<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('ورود به حساب کاربری')" :description="__('نام کاربری (کدملی) و پسورد قبلا پیامک شده است.')" />
        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />
        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6" autocomplete="off">
            @csrf
            <x-my.flt_lbl name="user_name" label="{{__('نام کاربری:')}}" dir="ltr" maxlength="25"
                          class="tracking-wider font-semibold" autofocus required value="{{old('user_name')}}"/>

            <x-my.flt_lbl name="password" type="password" label="{{__('کلمه عبور:')}}" dir="ltr" maxlength="25"
                          class="tracking-wider font-semibold" autofocus required/>

            <div class="flex justify-between">
                <!-- Remember Me -->
                <flux:field variant="inline">
                    <flux:checkbox name="remember" :checked="old('remember')" class="cursor-pointer"/>
                    <flux:label class="cursor-pointer">{{__('بخاطرسپاری')}}</flux:label>
                </flux:field>
                @if (Route::has('password.request'))
                    <flux:link class="text-sm" :href="route('forgotten.password')" wire:navigate tabindex="-1">
                        {{ __('ریست کلمه عبور') }}
                    </flux:link>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" color="violet" type="submit" class="w-full cursor-pointer" data-test="login-button">
                    {{ __('ورود') }}
                </flux:button>
            </div>
        </form>
        @if (Route::has('registration'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('حساب کاربری ندارید؟') }}</span>
                <flux:link :href="route('registration')" wire:navigate>{{ __('ثبت نام کنید.') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts.auth>
