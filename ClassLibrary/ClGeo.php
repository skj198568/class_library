<?php
/**
 * Created by PhpStorm.
 * User: skj19
 * Date: 2016/5/20
 * Time: 12:15
 */

namespace ClassLibrary;

/**
 * 地理位置
 * Class ClGeoLocation
 * @package ClassLibrary
 */
class ClGeo
{

    /**
     * 地球平均半径
     * @var int
     */
    private static $r = 6378137;

    /**
     * 腾讯地图请求key值，1万次/每日，5次/秒
     * @var string
     */
    private static $qq_key = '436BZ-ECMKU-6INVS-BH7GZ-OXYJ6-OFF77';

    /**
     * 计算两点间的距离
     * @param $a_wei a点纬度
     * @param $a_jing a点经度
     * @param $b_wei b点纬度
     * @param $b_jing b点经度
     * @return float
     */
    public static function getDistance($a_wei, $a_jing, $b_wei, $b_jing)
    {
        //将角度转为弧度
        $radLat1 = deg2rad($a_wei);
        $radLat2 = deg2rad($b_wei);
        $radLng1 = deg2rad($a_jing);
        $radLng2 = deg2rad($b_jing);
        //结果
        $s = acos(cos($radLat1) * cos($radLat2) * cos($radLng1 - $radLng2) + sin($radLat1) * sin($radLat2)) * self::$r;
        //精度
        $s = round($s * 10000) / 10000;
        return round($s);
    }

    /**
     * 依据地址获取经纬度
     * @param $address
     * @return mixed
     */
    public static function getByAddress($address)
    {
        $r = ClHttp::http(sprintf('http://apis.map.qq.com/ws/geocoder/v1/?address=%s&key=%s', urlencode($address), self::$qq_key));
        return isset($r['result']['location']) ? $r['result']['location'] : false;
    }
}