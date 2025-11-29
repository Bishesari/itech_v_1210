<?php

namespace App\Jobs;

use App\Services\ParsGreen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
class ResetPass implements ShouldQueue
{
    use Dispatchable, Queueable;
    protected string $mobile;
    protected string $user_name;
    protected string $passw;

    /**
     * Create a new job instance.
     */
    public function __construct(string $mobile, string $user_name, string $passw)
    {
        $this->mobile = $mobile;
        $this->user_name = $user_name;
        $this->passw = $passw;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sms = new ParsGreen();
        $txt = 'آی تک،' . "\n";
        $txt .= 'نام کاربری: ' . $this->user_name . "\n";
        $txt .= 'کلمه عبور جدید: ' . $this->passw;
        $sms->sendMessage($this->mobile, $txt);
    }
}
