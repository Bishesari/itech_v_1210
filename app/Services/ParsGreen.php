<?php
namespace App\Services;
use Exception;
class ParsGreen
{
    // 🔹 تنظیمات API پارس‌گرین
    private string $apiUrl = 'https://sms.parsgreen.ir/';
    private string $apiKey = 'C1407D9A-998C-4C8A-99FA-F38CD24FA212';

    /**
     * ارسال کد فعال‌سازی (OTP)
     *
     * @param string $mobile شماره موبایل
     * @param string $code کد فعال‌سازی
     * @param int $templateId شناسه قالب (۰ تا ۶)
     * @param bool $addName اضافه کردن نام شرکت به انتهای پیام
     */
    public function sendOtp(string $mobile, string $code, int $templateId = 0, bool $addName = true)
    {
        $req = (object)[
            'Mobile' => $mobile,
            'SmsCode' => $code,
            'TemplateId' => $templateId,
            'AddName' => $addName,
        ];

        return $this->exec("Message/SendOtp", $req);
    }

    /**
     * ارسال پیامک معمولی (متن دلخواه)
     *
     * @param string $mobile شماره گیرنده
     * @param string $text متن پیامک
     */
    public function sendMessage(string|array $mobiles, string $text)
    {
        if (is_string($mobiles)) {
            $mobiles = [$mobiles];
        }
        $req = (object)[
            'SmsBody' => $text,
            'Mobiles' => $mobiles,
        ];

        return $this->exec("Message/SendSms", $req);
    }



    /**
     * تابع عمومی برای اجرای درخواست API
     */
    private function exec(string $urlPath, object|array $req)
    {
        try {
            $url = rtrim($this->apiUrl, '/') . '/Apiv2/' . ltrim($urlPath, '/');

            $ch = curl_init($url);
            $jsonDataEncoded = json_encode($req);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $headers = [
                'authorization: BASIC APIKEY:' . $this->apiKey,
                'Content-Type: application/json;charset=utf-8'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            curl_close($ch);

            return json_decode($result);
        } catch (Exception $ex) {
            return (object)[
                'R_Success' => false,
                'R_Code' => -1,
                'R_Message' => 'Exception: ' . $ex->getMessage(),
            ];
        }
    }
}
