<?php
function j_d_stamp_en()
{
    return jdate('Y/m/d H:i:s', '', '', 'asia/Tehran', 'en');
}

function fa_num($number): string
{
    $en = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
    $fa = array("۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹");
    return str_replace($en, $fa, $number);
}

function mob_form(string $mobile): string
{
    $part1 = substr($mobile, 0, 4);
    $part2 = substr($mobile, 4, 3);
    $part3 = substr($mobile, 7, 4);

    return $part1 . ' ' . $part2 . ' ' . $part3;
}


function currency($num)
{
    if (strlen($num) > 0) {
        return number_format($num, 0, ".", ", ");
    } else {
        return $num;
    }
}

function mask_mobile(string $mobile): string
{
    return substr($mobile, -2) . '******' . substr($mobile, 0, 3) ;
}


function j_date($gd){
    $ts = strtotime($gd);
    $df =  jdate('Y/m/d H:i:s', $ts, '', '', 'en');
    return $df;
}
