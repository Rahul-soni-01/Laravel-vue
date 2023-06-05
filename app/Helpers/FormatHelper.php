<?php

namespace App\Helpers;

use App\Define\CommonDefine;
use App\Enums\Constant;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;

class FormatHelper
{
    public static function formatSetDate($date)
    {
        return $date ? Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d') : null;
    }

    public static function formatGetDate($date)
    {
        return $date ? Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y') : null;
    }

    public static function formatGetMonth($date)
    {
        return $date ? Carbon::createFromFormat('Y-m-d', $date)->format('Y/m') : null;
    }

    public static function formatDateTimeToDate($date)
    {
        return $date ? Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y/m/d') : null;
    }

    public static function formatGetDateTime($date)
    {
        return $date ? Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y/m/d H:i:s') : null;
    }

    public static function formatHour($hour)
    {
        return $hour != "" ? Carbon::createFromFormat('H:i:s', $hour)->format('H:i') : "";
    }

    public static function formatDayOfWeek($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date)->format('l');
    }

    public static function formatString($string)
    {
        return preg_replace("/[^a-zA-Z]+/", "", $string);
    }

    public static function sortByKey($data)
    {
        $data = collect($data)->sortKeys()->all();

        return array_values($data);
    }

    public static function slugAndUppercase($text)
    {
        return strtoupper(strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $text))));
    }


    public static function logInfoFile($info, $showTime = false)
    {
        if (app()->environment(['local', 'dev'])) {
            // Save info to file
            $logFile = fopen(
                storage_path('logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_info.log'),
                'a+'
            );
            $content = $showTime ? date('Y-m-d H:i:s') . ': ' . $info . PHP_EOL : $info . PHP_EOL;
            fwrite($logFile, $content);
            fclose($logFile);
        }
    }

    public static function showOrganizationName($item, &$result, $type = 'burden')
    {
        if ($type == 'burden' && isset($item->burdenOrganization->name)) {
            $result[] = $item->burdenOrganization->name;
            self::showOrganizationName($item->burdenOrganization, $result, $type);
        } elseif ($type == 'parent' && isset($item->parentOrganization->name)) {
            $result[] = $item->parentOrganization->name;
            self::showOrganizationName($item->parentOrganization, $result, $type);
        }

        return $result;
    }

    public static function logErrorMessage($exception)
    {
        $content = !empty($exception->getMessage()) ? $exception->getMessage() : $exception->getTraceAsString();
        $info = 'MESSAGE ERROR CONTENT: ' . $content . PHP_EOL;
        self::logInfoFile($info);
    }

    public static function logInfoSuccess($payload)
    {
        $info = 'API CODE:' . Route::currentRouteName() . PHP_EOL;
        $info .= 'RESULT: SUCCESS' . PHP_EOL;
        $info .= 'RESPONSE STATUS CODE: 200';
        $info .= 'RESPONSE CONTENT: ' . json_encode($payload) . PHP_EOL;
        $info .= '--------------------API END--------------------' . PHP_EOL;
        self::logInfoFile($info);
    }

    public static function logInfoError($errorCode, $response)
    {
        $response = json_encode($response);
        $info = 'API CODE: ' . Route::currentRouteName() . PHP_EOL;
        $info .= 'RESULT: ERROR' . PHP_EOL;
        $info .= 'RESPONSE STATUS CODE: ' . $errorCode . PHP_EOL;
        $info .= 'RESPONSE CONTENT: ' . $response . PHP_EOL;
        $info .= '--------------------API END--------------------' . PHP_EOL;
        self::logInfoFile($info);
    }

    public static function getLanguage($request)
    {
        $lang = $request->header('x-locale');
        if (!in_array($lang, CommonDefine::LANG_TYPE)) {
            return CommonDefine::LANG_TYPE_JA;
        }

        return $lang;
    }

    public static function sortDataCustom($data, $sortBy, $sortColumn)
    {
        if (strtolower($sortBy) == 'desc') {
            return collect($data)->sortByDesc($sortColumn)->values()->all();
        } else {
            return collect($data)->sortBy($sortColumn)->values()->all();
        }
    }

    public static function convertDateJapan($day)
    {
        $days = ["日", "月", "火", "水", "木", "金", "土"];
        $day = date('w', strtotime($day));
        $day = date('d/m/Y', strtotime($day)) . " ($days[$day])";

        return $day;
    }

    public static function paginate($data)
    {
        $items = $data->items();
        if (!is_array($items)) {
            $items = $items->toArray();
        }

        return [
            'list' => array_values($items),
            'paginate' => [
                'current_page' => $data->currentPage(),
                'from' => $data->firstItem(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
            ]
        ];
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function diffDate($startTime, $endTime)
    {
        $datetime1 = new \DateTime($startTime);
        $datetime2 = new \DateTime($endTime);
        $interval = $datetime1->diff($datetime2);
        return (int)$interval->format('%a');
    }

    public static function diffDateToHours($startTime, $endTime)
    {
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);

        return round(($endTime - $startTime) / 3600);
    }

    public static function paginateCustom($results, $pageSize)
    {
        $page = Paginator::resolveCurrentPage('page');

        $total = $results->count();

        $results = self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
        return self::paginate($results);
    }

    public static function showRegulationMessage($data, $type)
    {
        switch ($type) {
            case 'number_of_days':
                return '出張日数が規程の' . $data['value'] . '日を超えています';
            case 'passport_countries':
                return 'パスポートの残存有効期限が足りません' . $data['name'] . ': ' . $data['day'] . '日';
            case 'is_insurance_required':
                return '海外旅行傷害保険の手続きが必須です';
            case 'no_transit_areas':
                return $data['name'] . 'は乗り継ぎ禁止空港です';
            case 'banned_airlines':
                return $data['name'] . 'は利用禁止航空会社です';
            case 'banned_flight_section':
                return $data['from_name'] . 'から' . $data['to_name'] . 'のフライト区間は禁止されています';
            case 'attention_flight_section':
                return $data['from_name'] . 'から' . $data['to_name'] . 'のフライト区間は確認が必要な区間です';
            case 'transit_from_time':
                return $data['name'] . 'での乗り継ぎ時間が規程の' . $data['lower_limit_time'] . '時間未満となっています';
            case 'transit_until_time':
                return $data['name'] . 'での乗り継ぎ時間が規程の' . $data['maximum_time'] . '時間を超えています';
            case 'transit_caution_airlines':
                return $data['name'] . 'での' . $data['from_airline'] . 'から' . $data['to_airline'] . 'への乗り継ぎは注意が必要です';
            case 'number_of_connections_transit':
                return '乗り継ぎ回数が規程の' . $data['time'] . '回を超えています';
            case 'seat_class_domestic_flights':
                return $data['name'] . 'の座席クラスは国内線では利用できない座席クラスです';
            case 'seat_class_international_flights':
                return $data['name'] . 'の座席クラスは国際線では利用できない座席クラスです';
            case 'amount_of_money_less_than':
                return $data['name'] . 'の金額が規程の' . $data['amount'] . '円未満となっています';
            case 'amount_of_money_more_than':
                return $data['name'] . 'の金額が規程の' . $data['maximum_amount'] . '円を超えています';
            case 'amount_hotel_less_than_limit':
                return $data['name'] . 'の金額が規程の' . $data['amount'] . '円未満となっています';
            case 'amount_hotel_more_than_limit':
                return $data['name'] . 'の金額が規程の' . $data['amount'] . '円を超えています';
            case 'travel_costs_less_than_limit':
                return '出張費用の総額が規程の' . $data['amount'] . '円未満となっています';
            case 'travel_costs_more_than_limit':
                return '出張費用の総額が規程の' . $data['amount'] . '円を超えています';
        }
    }

    protected static function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(
            LengthAwarePaginator::class,
            compact(
                'items',
                'total',
                'perPage',
                'currentPage',
                'options'
            )
        );
    }
}
