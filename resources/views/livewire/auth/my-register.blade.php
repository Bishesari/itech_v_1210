<?php

use App\Jobs\OtpSend;
use App\Jobs\SmsPass;
use App\Models\BranchRoleUser;
use App\Models\Contact;
use App\Models\OtpLog;
use App\Models\User;
use App\Rules\NCode;
use Flux\Flux;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {
    // === Configuration ===
    private const OTP_RESEND_DELAY = 120;         // seconds until next allowed send
    private const OTP_TTL = 300;                  // seconds OTP is valid (e.g., 5 minutes)
    private const MAX_PER_N_CODE_PER_DAY = 5;
    private const MAX_UNIQUE_N_CODES_PER_IP_PER_DAY = 3;
    private const OTP_TABLE = 'otp_logs';

    // === Public properties (bound to UI) ===
    public string $context = 'modal'; // modal | page
    public string $f_name_fa = '';
    public string $l_name_fa = '';
    public string $n_code = '';
    public string $contact_value = '';
    public string $u_otp = '';
    public int $timer = 0; // front-end countdown
    public string $otp_log_check_err = '';

    protected function rules(): array
    {
        return [
            'f_name_fa' => ['required', 'min:2', 'max:30'],
            'l_name_fa' => ['required', 'min:2', 'max:30'],
            'n_code' => ['required', 'digits:10', new NCode, 'unique:profiles'],
            'contact_value' => ['required', 'starts_with:09', 'digits:11'],
        ];
    }

    // -------------------------
    // Step 1: show modal (no error shown here)
    // -------------------------
    public function check_inputs(): void
    {
        $this->validate();

        // Read current timer state for this n_code (do not show error to the user yet)
        $this->log_check(showError: false);

        $this->u_otp = '';
        $this->otp_send();
        $this->modal('otp_verify')->show();
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
                    $this->otp_log_check_err = 'تا زمان امکان ارسال مجدد لطفاً منتظر بمانید!';
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
            $this->otp_log_check_err = 'هنوز کدی به این شماره ارسال نشده است.';
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

        // success: create user inside transaction
        DB::transaction(function () use ($latest) {

            $tempPass = $this->n_code;

            $user = User::create([
                'user_name' => $this->n_code,
                'password' => $tempPass,
            ]);

            BranchRoleUser::create([
                'user_id' => $user->id,
                'role_id' => 1,
                'assigned_by' => $user->id,
            ]);

            // remove OTP logs for this n_code + mobile
            DB::table(self::OTP_TABLE)
                ->where('n_code', $this->n_code)
                ->where('contact_value', $this->contact_value)
                ->delete();

            // contact - search by mobile only, set verified flag if new
            $contact = Contact::firstOrCreate(
                ['contact_value' => $this->contact_value],
                ['verified' => 1]
            );

            // create profile
            $user->profile()->create([
                'identifier_type' => 'national_id',
                'n_code' => $this->n_code,
                'f_name_fa' => $this->f_name_fa,
                'l_name_fa' => $this->l_name_fa,
            ]);

            $user->contacts()->syncWithoutDetaching([$contact->id]);

            // send the temporary password via SmsPass job
            SmsPass::dispatch($this->contact_value, $this->n_code, $tempPass);

            event(new Registered($user));
            Auth::login($user);

            session()->regenerate();
            session([
                'active_role_id' => 1,
                'active_branch_id' => null,
            ]);
        });

        // stop client timer and redirect or reload
        $this->dispatch('stop_timer');

        if ($this->context === 'modal') {
            $this->dispatch('reloadPage');
        } else {
            $this->redirect('home');
        }
    }

    public function reset_all(): void
    {
        $this->reset();
        $this->resetErrorBag();
        $this->otp_log_check_err = '';
        $this->timer = 0;
        $this->dispatch('stop_timer');
    }
};
?>


<div class="flex flex-col">
    <x-auth-header :title="__('ایجاد حساب کاربری')" :description="__('جهت ایجاد حساب، اطلاعات را وارد نمایید.')"/>
    <x-auth-session-status class="text-center" :status="session('status')"/>
    <form wire:submit.prevent="check_inputs" class="flex flex-col gap-6 mt-5" autocomplete="off">
        <x-my.flt_lbl name="f_name_fa" label="{{__('نام:')}}" maxlength="30"
                      class="tracking-wider font-semibold" autofocus required/>

        <x-my.flt_lbl name="l_name_fa" label="{{__('نام خانوادگی:')}}" maxlength="40"
                      class="tracking-wider font-semibold" required/>

        <x-my.flt_lbl name="n_code" label="{{__('کدملی:')}}" dir="ltr" maxlength="10"
                      class="tracking-wider font-semibold" required/>
        <x-my.flt_lbl name="contact_value" label="{{__('شماره موبایل:')}}" dir="ltr" maxlength="11"
                      class="tracking-wider font-semibold" required/>
        <flux:button type="submit" variant="primary" color="teal" class="w-full cursor-pointer">
            {{ __('ادامه ثبت نام') }}
        </flux:button>
    </form>

    @if($context === 'modal')
        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('حساب کاربری داشته اید؟') }}</span>
            <flux:modal.trigger name="login">
                <flux:button variant="ghost" icon:trailing="arrow-down-left"
                             x-on:click="$flux.modal('register').close()" size="sm"
                             class="cursor-pointer">{{ __('وارد شوید.') }}</flux:button>
            </flux:modal.trigger>
        </div>
    @else
        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400 mt-4">
            <span>{{ __('حساب کاربری داشته اید؟') }}</span>
            <flux:button :href="route('login')" wire:navigate variant="ghost" icon:trailing="arrow-down-left" size="sm"
                         class="cursor-pointer">{{ __('وارد شوید.') }}
            </flux:button>
        </div>
    @endif


    {{-------------------------- OTP VERIFY Modal --------------------------}}
    <flux:modal name="otp_verify" class="md:w-96" :dismissible="false" focusable>
        <form wire:submit.prevent="otp_verify" class="space-y-8">
            <div class="max-w-72 mx-auto space-y-2">
                <flux:heading size="lg" class="text-center">{{__('تایید کد پیامکی')}}</flux:heading>
                <flux:text class="text-center">{{__('کد پیامک شده را وارد کنید.')}}</flux:text>
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
    </flux:modal>

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

