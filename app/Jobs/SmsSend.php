<?php

namespace App\Jobs;

use App\Services\ParsGreen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class SmsSend implements ShouldQueue
{
    use Dispatchable, Queueable;
    protected string $mobile;
    protected string $txt;
    /**
     * Create a new job instance.
     */
    public function __construct(string $mobile, string $txt)
    {
        $this->mobile = $mobile;
        $this->txt = $txt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sms = new ParsGreen();
        $sms->sendMessage($this->mobile, $this->txt);
    }
}
