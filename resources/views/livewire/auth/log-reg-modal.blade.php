<?php

use App\Jobs\OtpSend;
use App\Jobs\SmsPass;
use App\Models\Contact;
use App\Models\InstituteRoleUser;
use App\Models\OtpLog;
use App\Models\User;
use App\Rules\NCode;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
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
        if (Auth::attempt(['user_name' => $this->user_name, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            RateLimiter::clear($key);   // موفق شد → ریست

            $roles = Auth::user()->getAllRolesWithInstitutes();

            if ($roles->count() === 1) {
                $role = $roles->first();
                session([
                    'active_role_id' => $role->role_id,
                    'active_institute_id' => $role->institute_id,
                ]);
                $this->dispatch('reloadPage');
                return;
            } else {
                $this->redirectRoute('select_role');
            }
        }
        RateLimiter::hit($key);  // اشتباه → افزایش شمارنده
        $this->addError('password', __('نام کاربری یا رمز عبور اشتباه است.'));
    }


    public string $n_code = '';
    public string $mobile_nu = '';
    public int $timer = 0;
    public string $otp_log_check_err = '';
    public string $u_otp = '';

    protected function rules(): array
    {
        return [
            'n_code' => ['required', 'digits:10', new NCode, 'unique:profiles'],
            'mobile_nu' => ['required', 'starts_with:09', 'digits:11'],
        ];
    }

    public function check_inputs(): void
    {
        $this->validate();
        // مقدار تایمر برای این کد ملی باید مشخص شود.
        $this->log_check();
        $this->u_otp = '';
        $this->modal('otp_verify')->show();
    }

    public function otp_send(): void
    {
        if ($this->log_check()) {
            $this->u_otp = '';
            $otp = NumericOTP();
            OtpSend::dispatch($this->mobile_nu, $otp);
            OtpLog::create([
                'ip' => request()->ip(),
                'n_code' => $this->n_code,
                'mobile_nu' => $this->mobile_nu,
                'otp' => $otp,
                'otp_next_try_time' => time() + 120,
            ]);
            $this->timer = 120;
            $this->dispatch('set_timer');
        }
    }

    public function log_check(): bool
    {
        $this->timer = 0;
        $ip = request()->ip();
        $n_code = $this->n_code;
        $oneDayAgo = now()->subDay(); // 24 ساعت قبل

        // 3️⃣ آخرین رکورد برای n_code در 24 ساعت گذشته
        $latest_n_code = DB::table('otp_logs')
            ->where('n_code', $n_code)
            ->where('created_at', '>=', $oneDayAgo)
            ->latest('id')
            ->first();

        // 1️⃣ تعداد کدملی‌های یکتا برای این IP در 24 ساعت گذشته
        $unique_n_codes_for_ip = DB::table('otp_logs')
            ->where('ip', $ip)
            ->where('created_at', '>=', $oneDayAgo)
            ->distinct('n_code')
            ->count('n_code');

        // 2️⃣ تعداد کل رکوردهای n_code در 24 ساعت گذشته
        $total_n_code_count = DB::table('otp_logs')
            ->where('n_code', $n_code)
            ->where('created_at', '>=', $oneDayAgo)
            ->count();

        if ($latest_n_code) {
            if ($latest_n_code->otp_next_try_time - time() > 0) {
                $this->timer = $latest_n_code->otp_next_try_time - time();
                $this->dispatch('set_timer');
                $this->otp_log_check_err = '';
                return True;
            }
            if ($total_n_code_count < 5) {
                $this->otp_log_check_err = '';
                return True;
            } else {
                $this->otp_log_check_err = 'محدودیت کد ملی تا 24 ساعت';
                return false;
            }
        } else {
            if ($unique_n_codes_for_ip < 3) {
                $this->otp_log_check_err = '';
                return True;
            } else {
                $this->otp_log_check_err = 'محدودیت آی پی تا 24 ساعت';
                return false;
            }
        }
    }

    public function otp_verify(): void
    {
        $latest_otp = DB::table('otp_logs')
            ->where('n_code', $this->n_code)
            ->where('mobile_nu', $this->mobile_nu)
            ->latest('id')
            ->first();
        if (!$latest_otp) {
            $this->otp_log_check_err = 'هنوز کدی ارسال نشده است.';
            return;
        }

        if ($latest_otp->otp == $this->u_otp and time() < $latest_otp->otp_next_try_time) {
            $this->dispatch('stop_timer');
            $pass = simple_pass(6);
            $user = User::create([
                'user_name' => $this->n_code,
                'password' => $pass
            ]);
            InstituteRoleUser::create([
                'user_id' => $user->id,
                'role_id' => 1,
                'assigned_by' => $user->id,
            ]);
            DB::table('otp_logs')->where('n_code', $this->n_code)->where('mobile_nu', $this->mobile_nu)->delete();
            $contact = Contact::firstOrCreate(['mobile_nu' => $this->mobile_nu, 'verified' => 1]);
            $user->profile()->create([
                'identifier_type' => 'national_id',
                'n_code' => $this->n_code,
            ]);
            $user->contacts()->syncWithoutDetaching([$contact->id]);

            SmsPass::dispatch($this->mobile_nu, $this->n_code, $pass);
            $this->otp_log_check_err = '';

            event(new Registered($user));
            Auth::login($user);
            session()->regenerate();
            session([
                'active_role_id' => 1,
            ]);
            $this->dispatch('reloadPage');

        }
        if ($latest_otp->otp != $this->u_otp) {
            $this->otp_log_check_err = 'کد پیامکی اشتباه است.';
            return;
        }
        if ($latest_otp->otp_next_try_time) {
            $this->otp_log_check_err = 'کد منقضی شده است.';
        }
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
            </flux:menu>
        </flux:dropdown>
    @endauth

    @guest
        {{---------- Login Button ----------}}
        <flux:modal.trigger name="login">
            <flux:button variant="subtle" size="sm" class="cursor-pointer">
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
                        <flux:link class="text-sm" :href="route('forgotten.password')" wire:navigate tabindex="-1">
                            {{ __('ریست کلمه عبور') }}
                        </flux:link>
                    </div>

                    <flux:button type="submit" variant="primary" color="violet" class="w-full cursor-pointer">
                        {{ __('ورود') }}
                    </flux:button>

                </form>

                <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                    <span>{{ __('حساب کاربری ندارید؟') }}</span>
                    <flux:modal.trigger name="register">
                        <flux:button variant="ghost" x-on:click="$flux.modal('login').close()"
                                     icon:trailing="arrow-up-left" size="sm"
                                     class="cursor-pointer">{{ __('ثبت نام کنید.') }}</flux:button>
                    </flux:modal.trigger>
                </div>

            </div>

        </flux:modal>

        {{-------------------------- Registration Part --------------------------}}
        <flux:modal.trigger name="register">
            <flux:button variant="subtle" size="sm" class="cursor-pointer">{{__('ثبت نام')}}</flux:button>
        </flux:modal.trigger>

        {{-------------------------- Registration Modal --------------------------}}
        <flux:modal name="register" focusable :dismissible="false" @close="reset_all" flyout>
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{__('ایجاد حساب کاربری')}}</flux:heading>
                    <flux:text class="mt-2">{{__('جهت ایجاد حساب، اطلاعات را وارد نمایید.')}}</flux:text>
                </div>
                <form wire:submit="check_inputs" class="flex flex-col gap-6" autocomplete="off">
                    <x-my.flt_lbl name="n_code" label="{{__('کدملی:')}}" dir="ltr" maxlength="10"
                                  class="tracking-wider font-semibold" autofocus required/>
                    <x-my.flt_lbl name="mobile_nu" label="{{__('شماره موبایل:')}}" dir="ltr" maxlength="11"
                                  class="tracking-wider font-semibold" required/>
                    <flux:button type="submit" variant="primary" color="teal" class="w-full cursor-pointer">
                        {{ __('ادامه ثبت نام') }}
                    </flux:button>
                </form>
                <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                    <span>{{ __('حساب کاربری داشته اید؟') }}</span>
                    <flux:modal.trigger name="login">
                        <flux:button variant="ghost" icon:trailing="arrow-down-left"
                                     x-on:click="$flux.modal('register').close()" size="sm"
                                     class="cursor-pointer">{{ __('وارد شوید') }}</flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </flux:modal>

        {{-------------------------- OTP VERIFY Modal --------------------------}}
        <flux:modal name="otp_verify" class="md:w-96" :dismissible="false">
            <form wire:submit="otp_verify" class="space-y-8">
                <div class="max-w-72 mx-auto space-y-2">
                    <flux:heading size="lg" class="text-center">{{__('تایید کد پیامکی')}}</flux:heading>
                    <flux:text class="text-center">{{__('دکمه ارسال را کلیک نموده و کد دریافتی را وارد کنید.')}}</flux:text>
                </div>
                <flux:otp wire:model="u_otp" submit="auto" length="6" label="OTP Code" label:sr-only :error:icon="false"
                          error:class="text-center" class="mx-auto" dir="ltr"/>
                @if($otp_log_check_err)
                    <flux:text class="text-center" color="rose">{{$otp_log_check_err}}</flux:text>
                @endif
                <div class="space-y-4">
                    @if ($timer > 0)
                        <!-- دکمه شمارنده ارسال پیامک -->
                        <flux:button wire:click="otp_send" class="w-full" disabled>
                            <span id="timer">{{$timer}}</span>{{ __(' ثانیه تا ارسال مجدد') }}
                        </flux:button>
                    @else
                        <!-- دکمه ارسال پیامک -->
                        <flux:button wire:click="otp_send" variant="primary" color="teal"
                                     class="w-full cursor-pointer">{{ __('ارسال پیامک') }}</flux:button>
                    @endif
                </div>
            </form>
        </flux:modal>
    @endguest

    @script
    <script>
        let interval;
        let timer;
        Livewire.on('set_timer', () => {
            // مقدار جدید Livewire رو بگیر
            timer = $wire.get('timer');
            // شمارش قبلی رو پاک کن
            if (interval) clearInterval(interval);
            interval = setInterval(() => {
                timer--;
                document.getElementById('timer').innerHTML = timer;
                if (timer <= 0) {
                    clearInterval(interval);
                    interval = null; // cleanup
                    $wire.set('timer', 0);
                }
            }, 1000);
        });
        Livewire.on('stop_timer', () => {
            clearInterval(interval);
        });
    </script>
    @endscript

</div>
