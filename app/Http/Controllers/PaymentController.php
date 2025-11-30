<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        // 1) وضعیت پرداخت
        if ($request->State !== "OK") {
            return "پرداخت ناموفق بود: " . $request->State;
        }

        // 2) VerifyTransaction با TerminalNumber (نسخه صحیح)
        $verify = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post('https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction', [
            "RefNum"         => $request->RefNum,
            "TerminalNumber" => 31266886   // دقت کن همین مقدار صحیح است
        ]);

        $verifyResult = $verify->json();

        // 3) بررسی خطا
        if (!$verifyResult || !isset($verifyResult["ResultCode"])) {
            return "خطا در Verify (خروجی نامعتبر): " . json_encode($verifyResult, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }

        if ($verifyResult["ResultCode"] != 0) {
            return "تراکنش تایید نشد: " . json_encode($verifyResult, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }

        // 4) ذخیره سفارش
        Order::create([
            'product_id' => 1,
            'resnum'     => $request->ResNum,
            'refnum'     => $request->RefNum,
            'amount'     => $verifyResult["TransactionDetail"]["OrginalAmount"],
            'rrn'        => $verifyResult["TransactionDetail"]["RRN"],
            'status'     => 'paid',
        ]);

        // 5) نمایش نتیجه موفق
        $txn = $verifyResult["TransactionDetail"];

        return "<h2>پرداخت موفق بود 🎉</h2>
                <p>RefNum: {$request->RefNum}</p>
                <p>Rrn: {$txn['RRN']}</p>
                <p>شماره پیگیری: {$request->TraceNo}</p>
                <p>مبلغ: " . number_format($txn['OrginalAmount']) . " تومان</p>
                <p>کارت: {$txn['MaskedPan']}</p>";
    }
}
