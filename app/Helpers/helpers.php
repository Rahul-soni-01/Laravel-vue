<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

if (!function_exists('stripUnicode')) {
    function stripUnicode($str)
    {
        if (!$str) {
            return false;
        }
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd' => 'đ|Đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i' => 'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];
        foreach ($unicode as $ascii => $uni) {
            $arr = explode("|", $uni);
            $str = str_replace($arr, $ascii, $str);
        }
        return $str;
    }
}//end if

if (!function_exists('changeTitle')) {
    function changeTitle($str)
    {
        $str = trim($str);
        if ($str == "") {
            return "";
        }
        $str = str_replace('"', '', $str);
        $str = str_replace("'", '', $str);
        $str = str_replace(" ", '-', $str);
        $str = stripUnicode($str);
        $str = mb_convert_case($str, MB_CASE_LOWER, 'utf-8');
        return $str;
    }
}

if (!function_exists('jsonDecode')) {
    /**
     * @param $string
     * @return array
     */
    function jsonDecode($string): array
    {
        try {
            if (!$string) {
                return [];
            }

            return json_decode($string, true);
        } catch (Exception $exception) {
            Log::error(__METHOD__, ['string' => $string, 'Exception' => $exception]);

            return [];
        }
    }
}
