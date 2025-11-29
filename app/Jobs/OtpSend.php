<?php

namespace App\Jobs;

use App\Services\ParsGreen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OtpSend implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected string $mobile;
    protected string $code;
    /**
     * Create a new job instance.
     */
    public function __construct(string $mobile, string $code)
    {
        $this->mobile = $mobile;
        $this->code = $code;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sms = new ParsGreen();
        $sms->sendOtp($this->mobile, $this->code);
    }
}
