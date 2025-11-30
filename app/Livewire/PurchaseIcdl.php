<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class PurchaseIcdl extends Component
{
    public Product $product;
    public string $cellNumber = '';

    public function mount()
    {
        // فرض می‌کنیم محصول ICDL با ID = 1 داریم
        $this->product = Product::find(1);
    }

    public function initiatePayment()
    {
        $resNum = Str::uuid(); // شماره سفارش یکتا

        // آماده‌سازی payload
        $payload = [
            "action" => "token",                // حروف کوچک
            "TerminalId" => "31266886",         // شماره ترمینال شما
            "Amount" => (int) $this->product->price,  // اطمینان از integer بودن
            "ResNum" => $resNum,
            "RedirectUrl" => route('payment.callback'), // مسیر callback
            "CellNumber" => $this->cellNumber ?: "09100000000", // شماره تستی اگر خالی باشد
        ];

        try {
            // درخواست POST برای دریافت توکن
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post('https://sep.shaparak.ir/onlinepg/onlinepg', $payload);

            $result = $response->json();

            if (isset($result['token'])) {
                // هدایت کاربر به صفحه پرداخت (نسخه جدید SEP)
                return redirect("https://sep.shaparak.ir/OnlinePG/SendToken?token={$result['token']}");
            } else {
                session()->flash('error', 'خطا در دریافت توکن: ' . ($result['errorDesc'] ?? json_encode($result)));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'خطا در اتصال به درگاه: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchase-icdl');
    }
}
