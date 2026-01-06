<?php

use App\Jobs\OtpSend;
use App\Jobs\SmsPass;
use App\Models\BranchRoleUser;
use App\Models\Contact;
use App\Models\OtpLog;
use App\Models\Profile;
use App\Models\User;
use App\Rules\NCode;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')]
class extends Component {
    // === Configuration ===
    private const OTP_RESEND_DELAY = 120;         // seconds until next allowed send
    private const OTP_TTL = 300;                  // seconds OTP is valid (e.g., 5 minutes)
    private const MAX_PER_N_CODE_PER_DAY = 5;
    private const MAX_UNIQUE_N_CODES_PER_IP_PER_DAY = 3;
    private const OTP_TABLE = 'otp_logs';

    // === Public properties (bound to UI) ===
    public int $step = 1;
    public string $n_code = '';
    public string $contact_value = '';
    public array $mobiles = [];
    public string $u_otp = '';
    public int $timer = 0; // front-end countdown
    public string $otp_log_check_err = '';

    protected function rules(): array
    {
        return [
            'n_code' => ['required'],
            'contact_value' => ['required']
        ];
    }

    // -------------------------
    // Step 1:
    // -------------------------

    public function check_n_code(): void
    {
        $this->validateOnly('n_code');
        $profile = Profile::where('n_code', $this->n_code)->first();
        if (!$profile) {
            $this->addError('n_code', 'کد ملی یافت نشد.');
            return;
        }
        $user = $profile->user;
        $this->mobiles = $user->contacts->pluck('contact_value')->toArray();
        if (empty($this->mobiles)) {
            $this->addError('n_code', 'هیچ شماره موبایلی برای این کد ملی ثبت نشده است.');
            return;
        }
        if (count($this->mobiles) == 1) {
            $this->contact_value = $this->mobiles[0];
            $this->otp_send();
        }
        $this->log_check();
        $this->u_otp = '';
        $this->step = 2;
    }


    // -------------------------
    // Step 2: send otp (rate-limited)
    // -------------------------
    public function otp_send(): void
    {
        // Validate inputs first (prevents abuse of endpoint)
        $this->validate();

        // If rate limits / timers fail, show errors
        if (!$this->log_check(showError: true)) {
            return;
        }

        $this->u_otp = '';

        // Generate numeric OTP
        $otp = NumericOTP();

        // Encrypt OTP for storage (so DB leak doesn't reveal codes)
        $encryptedOtp = encrypt($otp);

        // Create log record
        OtpLog::create([
            'ip' => request()->ip(),
            'n_code' => $this->n_code,
            'contact_value' => $this->contact_value,
            'otp' => $encryptedOtp,
            'otp_next_try_time' => time() + self::OTP_RESEND_DELAY,
            'otp_expires_at' => now()->addSeconds(self::OTP_TTL),
        ]);

        // Dispatch SMS job with plain OTP (job can be retried safely)
        OtpSend::dispatch($this->contact_value, $otp);

        // start client timer
        $this->timer = self::OTP_RESEND_DELAY;
        $this->dispatch('set_timer');

        $this->dispatch('focus-otp');
    }

    // -------------------------
    // Rate-limit + timer inspection
    // -------------------------
    /**
     * log_check
     *
     * @param bool $showError whether to populate $otp_log_check_err (true for otp_send; false for check_inputs)
     * @return bool true => OK to send, false => blocked
     */
    public function log_check(bool $showError = true): bool
    {
        $this->otp_log_check_err = '';
        $this->timer = 0;

        $ip = request()->ip();
        $n_code = $this->n_code;
        $oneDayAgo = now()->subDay();

        // last record for this n_code in last 24 hours
        $latest = DB::table(self::OTP_TABLE)
            ->where('n_code', $n_code)
            ->where('created_at', '>=', $oneDayAgo)
            ->latest('id')
            ->first();

        // total sends for this n_code in last 24h
        $countForNCode = DB::table(self::OTP_TABLE)
            ->where('n_code', $n_code)
            ->where('created_at', '>=', $oneDayAgo)
            ->count();

        // distinct n_codes for this ip in last 24h
        $uniqueNcodesForIp = DB::table(self::OTP_TABLE)
            ->selectRaw('COUNT(DISTINCT n_code) as cnt')
            ->where('ip', $ip)
            ->where('created_at', '>=', $oneDayAgo)
            ->value('cnt') ?? 0;

        // If we have a last record, check resend window and per-n-code limit
        if ($latest) {
            // If resend wait still active => block and set timer
            if (!empty($latest->otp_next_try_time) && $latest->otp_next_try_time > time()) {
                $this->timer = $latest->otp_next_try_time - time();
                $this->dispatch('set_timer');

                if ($showError) {
                    $this->otp_log_check_err = 'تا زمان امکان ارسال مجدد لطفاً منتظر بمانید.';
                }
                return false;
            }

            // Per-n-code daily limit
            if ($countForNCode >= self::MAX_PER_N_CODE_PER_DAY) {
                if ($showError) {
                    $this->otp_log_check_err = 'در ۲۴ ساعت گذشته حداکثر تعداد ارسال برای این کد ملی انجام شده است.';
                }
                return false;
            }

            // allowed
            return true;
        }

        // if no latest record (first send for this n_code in 24h), check ip uniqueness limit
        if ((int)$uniqueNcodesForIp >= self::MAX_UNIQUE_N_CODES_PER_IP_PER_DAY) {
            if ($showError) {
                $this->otp_log_check_err = 'این IP در ۲۴ ساعت گذشته بیش از حد مجاز ثبت‌نام انجام داده است.';
            }
            return false;
        }

        return true;
    }

    // -------------------------
    // Verify OTP and create user
    // -------------------------
    public function otp_verify(): void
    {
        $this->otp_log_check_err = '';

        // Find latest OTP record for this n_code + mobile
        $latest = DB::table(self::OTP_TABLE)
            ->where('n_code', $this->n_code)
            ->where('contact_value', $this->contact_value)
            ->latest('id')
            ->first();

        if (!$latest) {
            $this->otp_log_check_err = 'هنوز کدی ارسال نشده است.';
            return;
        }
        // بازسازی تایمر برای جلوگیری از Expire اشتباه
        if ($latest->otp_next_try_time > time()) {
            $this->timer = $latest->otp_next_try_time - time();
            $this->dispatch('set_timer');
        }

        // Check OTP expiry (use otp_expires_at field)
        if (empty($latest->otp_expires_at) || now()->greaterThan($latest->otp_expires_at)) {
            $this->otp_log_check_err = 'کد پیامکی منقضی شده است.';
            return;
        }

        // Compare decrypted OTP
        try {
            $storedOtp = decrypt($latest->otp);
        } catch (\Throwable $e) {
            // corrupted/invalid ciphertext -> treat as non-match / expired
            $this->otp_log_check_err = 'کد پیامکی نامعتبر یا منقضی است.';
            return;
        }

        if (!hash_equals((string)$storedOtp, (string)$this->u_otp)) {
            $this->otp_log_check_err = 'کد پیامکی اشتباه است.';
            return;
        }

        // success: reset user password
        DB::transaction(function () use ($latest) {

            $tempPass =simple_pass(6);

            $user = Profile::where('n_code', $this->n_code)->first()->user;
            $user->password = $tempPass;
            $user->save();

            // remove OTP logs for this n_code + mobile
            DB::table(self::OTP_TABLE)
                ->where('n_code', $this->n_code)
                ->where('contact_value', $this->contact_value)
                ->delete();

            // send the temporary password via SmsPass job
            SmsPass::dispatch($this->contact_value, $user->user_name, $tempPass);
        });

        // stop client timer and redirect or reload
        $this->dispatch('stop_timer');
        $this->redirect(route('login', absolute: false));
    }

    public function reset_all(): void
    {
        $this->reset([
            'n_code',
            'contact_value',
            'mobiles',
            'u_otp',
            'step',
            'timer',
            'otp_log_check_err'
        ]);

        $this->resetErrorBag();
        $this->step = 1; // بازگشت به مرحله اول
        $this->dispatch('stop_timer');
    }
};
?>


<div class="flex flex-col gap-4">
    <x-auth-session-status class="text-center" :status="session('status')"/>
    @if($step === 1)
        <x-auth-header :title="__('بازگردانی کلمه عبور')" :description="__('مرحله اول: دریافت کد ملی')"/>
        <form wire:submit.prevent="check_n_code" class="space-y-4 flex flex-col gap-4" autocomplete="off">
            <x-my.flt_lbl name="n_code" label="{{__('کدملی:')}}" dir="ltr" maxlength="10"
                          class="tracking-wider font-semibold" autofocus required/>
            <flux:button type="submit" variant="primary" color="teal" class="w-full cursor-pointer">
                {{ __('ادامه') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('یا بازگردید به ') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('صفحه ورود') }}</flux:link>
        </div>
    @endif


    {{-------------------------- OTP VERIFY --------------------------}}
    @if($step === 2)
        <x-auth-header color="text-yellow-600" :title="__('بازگردانی کلمه عبور')" :description="__('مرحله دوم: انتخاب شماره موبایل و ارسال otp')"/>
        <flux:text class="text-center">{{__('نام کاربری و کلمه عبور جدید پیامک خواهد شد.')}}</flux:text>

        <form wire:submit.prevent="otp_verify" class="space-y-8">
            <!-- National Code and Mobile -->
            <div class="grid grid-cols-2 gap-4">
                <flux:text class="mt-2 text-center">{{__('کدملی: ')}}{{$n_code}}</flux:text>
                @if(count($mobiles) > 1)
                    <flux:select wire:model="contact_value" variant="listbox" placeholder="انتخاب موبایل">
                        @foreach($mobiles as $mobile)
                            <flux:select.option value="{{$mobile}}"
                                                style="text-align: center">{{mask_mobile($mobile)}}</flux:select.option>
                        @endforeach
                    </flux:select>
                @else
                    <flux:text class="mt-2 text-center">{{__('موبایل: ')}}{{mask_mobile($contact_value)}}</flux:text>
                @endif
            </div>

            <flux:otp wire:model="u_otp" id="otp-input-wrapper" submit="auto" :error:icon="false" error:class="text-center" class="mx-auto" dir="ltr">
                <flux:otp.input autofocus/> <flux:otp.input /> <flux:otp.input />
                <flux:otp.separator />
                <flux:otp.input /> <flux:otp.input /><flux:otp.input />
            </flux:otp>

            @if($otp_log_check_err)
                <flux:text class="text-center" color="rose">{{$otp_log_check_err}}</flux:text>
            @endif

            <div class="space-y-4">
                @if ($timer > 0)
                    <flux:button wire:click="otp_send" class="w-full" disabled>
                        <span id="timer">{{$timer}}</span>{{ __(' ثانیه تا ارسال مجدد') }}
                    </flux:button>
                @else
                    <flux:button wire:click="otp_send" variant="primary" color="teal"
                                 class="w-full cursor-pointer">{{ __('ارسال پیامک') }}</flux:button>
                @endif
            </div>
        </form>
    @endif

    @script
    <script>
        let interval;
        let timer;

        function clearTimerInterval() {
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        }

        Livewire.on('set_timer', () => {
            // read timer from Livewire
            timer = Number($wire.get('timer')) || 0;

            clearTimerInterval();

            if (timer <= 0) {
                document.getElementById('timer') && (document.getElementById('timer').innerHTML = '0');
                return;
            }

            interval = setInterval(() => {
                timer--;
                const el = document.getElementById('timer');
                if (el) el.innerHTML = timer;
                if (timer <= 0) {
                    clearTimerInterval();
                    $wire.set('timer', 0);
                }
            }, 1000);
        });

        Livewire.on('stop_timer', () => {
            clearTimerInterval();
            $wire.set('timer', 0);
        });

        // If modal closed unexpectedly, ensure cleanup (Flux modal event)
        document.addEventListener('flux:modal:close', (e) => {
            // depending on flux implementation event name may differ — this is a safeguard
            clearTimerInterval();
            $wire.set('timer', 0);
        });

        // کد برای فوکوس
        Livewire.on('focus-otp', () => {
            // یک تاخیر کوتاه (مثلا ۳۰۰ میلی‌ثانیه) می‌دهیم تا مدال کامل باز شود
            setTimeout(() => {
                // پیدا کردن کانتینر اصلی
                const wrapper = document.getElementById('otp-input-wrapper');
                if (wrapper) {
                    // پیدا کردن اولین اینپوت داخل کانتینر
                    const firstInput = wrapper.querySelector('input');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }
            }, 300);
        });

    </script>
    @endscript
</div>

