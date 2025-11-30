<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestSepController extends Controller
{
    public function start()
    {
        return view('sep-test');
    }

    public function pay(Request $request)
    {
        $resNum = uniqid(); // شماره خرید

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post('https://sep.shaparak.ir/onlinepg/onlinepg', [
            "action" => "Token",
            "TerminalId" => 31266886,
            "Amount" => 12000,
            "ResNum" => $resNum,
            "RedirectUrl" => route('sep.callback'),
//            "RedirectUrl" => 'https://itechit.ir/payment/callback',
            "CellNumber" =>"09177755924",

        ]);

        $result = $response->json();

        if (!isset($result['status']) || $result['status'] != 1) {
            return "خطا در دریافت توکن: " . json_encode($result);
        }

        $token = $result['token'];

        return redirect("https://sep.shaparak.ir/OnlinePG/SendToken?token=$token");
    }

    public function callback(Request $request)
    {
        if ($request->State !== "OK") {
            return "پرداخت ناموفق بود: " . $request->State;
        }

        // VerifyTransaction
        $verify = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post('https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction', [
            "RefNum" => $request->RefNum,
            "TerminalNumber" => env('SEP_MID')
        ]);

        $verifyResult = $verify->json();

        if (!isset($verifyResult["ResultCode"]) || $verifyResult["ResultCode"] != 0) {
            return "خطا در Verify: " . json_encode($verifyResult);
        }

        return "
            <h2>پرداخت موفق بود 🎉</h2>
            <p>رسید دیجیتال (RefNum): $request->RefNum</p>
            <p>RRN: " . $verifyResult["TransactionDetail"]["RRN"] . "</p>
            <p>مبلغ: " . $verifyResult["TransactionDetail"]["OrginalAmount"] . "</p>
        ";
    }
}
