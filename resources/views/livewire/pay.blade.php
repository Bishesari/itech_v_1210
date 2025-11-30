<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $amount = '12000';
    public string $resNum = '1qaz@WSX';
    public string $cellNumber = '9120000000';
    public ?string $response = null;

    public function pay()
    {
        // داده‌ها
        $data = [
            "action" => "token",
            "TerminalId" => "31266886", // مقدار واقعی ترمینال خودتون
            "Amount" => (int)$this->amount,
            "ResNum" => $this->resNum,
            "RedirectUrl" => 'https://itechit.ir/payment/callback', // استفاده از روت لاراول
            "CellNumber" => $this->cellNumber
        ];

        $jsonData = json_encode($data);

        // ارسال POST با cURL
        $ch = curl_init("https://sep.shaparak.ir/onlinepg/onlinepg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $this->response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->response = 'cURL error: ' . curl_error($ch);
        }

        curl_close($ch);
    }
}; ?>

<div>
    <flux:card>
        <flux:heading level="2">پرداخت SEP</flux:heading>

        <flux:input label="مبلغ" wire:model="amount" />
        <flux:input label="شناسه پرداخت" wire:model="resNum" />
        <flux:input label="شماره همراه" wire:model="cellNumber" />

        <flux:button wire:click="pay" variant="primary">پرداخت</flux:button>

        <div class="mt-4">
            <flux:text >{{ $response }}</flux:text>
        </div>
    </flux:card>
</div>
