<?php
function NumericOTP($length=6): string
{
    $chs = '0123456789';
    $result = '';
    for ($i = 1; $i <= $length; $i++) {
        $result .= substr($chs, (rand() % (strlen($chs))), 1);
    }
    return $result;
}

function otp_seperated($otp): string
{
    $result = '';
    for ($i = 0; $i < strlen($otp); $i++) {
        $result .= substr($otp, $i, 1);
        $result .= ' ';
    }
    return $result;
}
function alpha_numeric($length): string
{
    $chs = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNQRSTUVWXYZ!@#$&*';
    $result = '';
    for ($i = 1; $i <= $length; $i++) {
        $result .= substr($chs, (rand() % (strlen($chs))), 1);
    }

    return $result;
}
function simple_pass($length=6): string
{
    $digits = '123456789';
    $chs = 'abcdefghijklmnpqrstuvwxyz';
    $result = '';
    for ($i = 1; $i <= 2; $i++) {
        $result .= substr($digits, (rand() % (strlen($digits))), 1);
    }
    for ($i = 1; $i <= $length-4; $i++) {
        $result .= substr($chs, (rand() % (strlen($chs))), 1);
    }
    for ($i = 1; $i <= 2; $i++) {
        $result .= substr($digits, (rand() % (strlen($digits))), 1);
    }
    return $result;
}
