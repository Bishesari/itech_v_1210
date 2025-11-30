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
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')]
class extends Component {
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
        $this->js("setTimeout(() => {window.dispatchEvent(new CustomEvent('focus-otp-input'))}, 100);");
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
        if ($latest_otp->otp == $this->u_otp and time() < $latest_otp->otp_next_try_time) {
            $this->dispatch('stop_timer');
            $pass = simple_pass(8);
            $user = User::create([
                'user_name' => $this->n_code,
                'password' => $pass
            ]);
            InstituteRoleUser::create([
                'user_id' => $user->id,
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
            Session::regenerate();
            session([
                'active_role_id' => 1,
            ]);
            $this->redirectIntended(route('dashboard', absolute: false), navigate: true);

        }
        if ($latest_otp->otp != $this->u_otp) {
            $this->otp_log_check_err = 'کد پیامکی اشتباه است.';
            return;
        }
        if ($latest_otp->otp_next_try_time) {
            $this->otp_log_check_err = 'کد منقضی شده است.';
        }
    }
}; ?>

<div>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('ایجاد حساب کاربری')"
                       :description="__('جهت ایجاد حساب، اطلاعات خواسته شده را وارد نمایید.')"/>
        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')"/>
        <form method="POST" wire:submit="check_inputs" class="flex flex-col gap-6" autocomplete="off">
            <x-my.flt_lbl name="n_code" label="{{__('کدملی:')}}" dir="ltr" maxlength="10"
                          class="tracking-wider font-semibold" autofocus required/>
            <x-my.flt_lbl name="mobile_nu" label="{{__('شماره موبایل:')}}" dir="ltr" maxlength="11"
                          class="tracking-wider font-semibold" required/>
            <flux:button type="submit" variant="primary" color="teal" class="w-full cursor-pointer">
                {{ __('ادامه ثبت نام') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('حساب کاربری داشته اید؟') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('وارد شوید') }}</flux:link>
        </div>

    </div>


    <flux:modal name="otp_verify" class="md:w-96" :dismissible="false">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-center text-indigo-500">{{__('تایید کد پیامکی')}}</flux:heading>
                <flux:text class="mt-2 text-center">{{__('ارسال را کلیک نموده و کد دریافتی را وارد کنید.')}}</flux:text>
            </div>
            <form method="POST" wire:submit="otp_verify" class="flex flex-col gap-6" autocomplete="off">
                <div x-data @focus-otp-input.window="$nextTick(() => $refs.otp.focus())"
                     class="grid grid-cols-2 gap-4">

                    @if ($timer > 0)
                        <!-- دکمه ارسال پیامک -->
                        <flux:button type="button" variant="filled" disabled>
                            <span id="timer">{{$timer}}</span>{{ __('تا ارسال مجدد') }}
                        </flux:button>
                    @else
                        <!-- دکمه ارسال پیامک -->
                        <flux:button type="button" variant="primary" color="teal" class="cursor-pointer"
                                     wire:click="otp_send">
                            {{ __('ارسال پیامک') }}
                        </flux:button>
                    @endif

                    <!-- 🔹 این input بعد از ارسال پیامک فوکوس می‌گیرد -->
                    <flux:input x-ref="otp" wire:model="u_otp" type="text" maxlength="6" required autofocus
                                :placeholder="__('کد پیامک شده')"
                                class:input="text-center font-semibold placeholder:font-normal"
                                style="direction:ltr"/>
                </div>


                <flux:text class="text-center" color="rose">{{$otp_log_check_err}}</flux:text>
                <flux:text
                    class="mt-2 text-center">{{__('کدملی: ')}}{{$n_code}}{{__(' و شماره: ')}}{{$mobile_nu}}</flux:text>
                <div class="flex items-center justify-end">
                    <flux:button type="submit" variant="primary" color="indigo" class="w-full cursor-pointer">
                        {{ __('تکمیل ثبت نام') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

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
