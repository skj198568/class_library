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
     * 百度key
     * @var string
     */
    private static $baidu_key = 'B57ddde27d7b2603d4387ba49f708b4d';

    /**
     * 计算两点间的距离
     * @param integer $a_wei a点纬度
     * @param integer $a_jing a点经度
     * @param integer $b_wei b点纬度
     * @param integer $b_jing b点经度
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
     * 依据地址获取经纬度，建议缓存查询结果
     * @param string $address 地址
     * @param string $key qq开发者key
     * @return bool|mixed
     */
    public static function getByAddressWithQQMap($address, $key = '')
    {
        $r = ClHttp::http(sprintf('http://apis.map.qq.com/ws/geocoder/v1/?address=%s&key=%s', urlencode($address), empty($key) ? self::$qq_key : $key));
        return $r['status'] == 0 ? $r['result'] : false;
    }

    /**
     * 依据地址获取经纬度，建议缓存查询结果
     * @param $address
     * @param string $key
     * @return bool|mixed
     */
    public static function getByAddressWithBaiDu($address, $key = ''){
        $r = ClHttp::http(sprintf('http://api.map.baidu.com/geocoder/v2/?address=%s&output=json&ak=%s', urlencode($address), empty($key) ? self::$baidu_key : $key));
        return $r['status'] == 0 ? $r['result'] : false;
    }
}