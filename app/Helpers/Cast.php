<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class Cast {

    public static function number( $num )
    {
        if(empty($num)) return 0;
        $num = @trim(@rtrim(@ltrim($num)));
        return floatval(preg_replace('#[^0-9\.\-]#i', '', $num));
    }

    public static function currency( $num, $decimal = 2 )
    {
        if(empty($num)) $num = 0;
        $num = self::number($num);
        return number_format($num, $decimal, '.', ',');
    }

    public static function money( $num, $decimal = 2 )
    {
        if(empty($num)) $num = 0;
        $num = self::number($num);
        return number_format($num, $decimal, '.', ',');
    }

    public static function absMoney( $num, $decimal = 2 )
    {
        if(empty($num)) $num = 0;
        $num = self::number($num);
        return str_replace('-', '', number_format($num, $decimal, '.', ','));
    }

    public static function date( $str, $format = 'd/m/y' )
    {
        if(in_array($str, [null,"","0000-00-00","1900-01-01"])) return '';
        return Carbon::parse($str)->format($format);
    }

    public static function datetime( $str, $format = 'd-M-y, H:i' )
    {
        if(in_array($str, [null,"","0000-00-00 00:00:00","1900-01-01 00:00:00"])) return '';
        return Carbon::parse($str)->format($format);
    }

    public static function monthForHuman( $str )
    {
        $year = substr($str, 0, 4);
        $month = substr($str, 4, 2);
        $dateFormat = $year.'-'.$month.'-01';
        $monthName = date('F', strtotime($dateFormat));
        return $monthName.' '.$year;
    }
}
