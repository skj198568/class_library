<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2018/1/3
 * Time: 9:38
 */

namespace ClassLibrary;

/**
 * 依据ip获取地址
 * Class ClIp
 * @package ClassLibrary
 */
class ClIp {

    /**
     * 错误码
     * @var array
     */
    private static $error_code = [
        0   => '正常',
        1   => '该服务响应超时或系统内部错误，如遇此问题，请到官方论坛进行反馈',
        10  => 'Post上传数据不能超过8M',
        101 => '请求消息没有携带AK参数',
        102 => '对于Mobile类型的应用请求需要携带mcode参数，该错误码代表服务器没有解析到mcode',
        200 => 'AK有误请检查再重试	根据请求的ak，找不到对应的APP',
        201 => 'APP被用户自己禁用，请在控制台解禁',
        202 => '恶意APP被管理员删除',
        203 => '当前API控制台支持Server(类型1), Mobile(类型2, 新版控制台区分为Mobile_Android(类型21)及Mobile_IPhone（类型22）及Browser（类型3），除此之外的其他类型被认为是APP类型错误',
        210 => '在申请Server类型应用的时候选择IP校验，需要填写IP白名单，如果当前请求的IP地址不在IP白名单或者不是0.0.0.0/0就认为IP校验失败',
        211 => 'SERVER类型APP有两种校验方式：IP校验和SN校验，当用户请求的SN和服务端计算出来的SN不相等的时候，提示SN校验失败',
        220 => '浏览器类型的APP会校验referer字段是否存在，且在referer白名单里面，否则返回该错误码',
        230 => '服务器能解析到mcode，但和数据库中不一致，请携带正确的mcode',
        240 => '用户在API控制台中创建或设置某APP的时候禁用了某项服务',
        250 => '根据请求的user_id, 数据库中找不到该用户的信息，请携带正确的user_id',
        251 => '该用户处于未激活状态',
        252 => '恶意用户被加入黑名单',
        260 => '服务器解析不到用户请求的服务名称',
        261 => '该服务已下线',
        301 => '配额超限，如果想增加配额请联系我们',
        302 => '配额超限，如果想增加配额请联系我们',
        401 => '并发控制超限，请控制并发量请联系我们',
        402 => '当前并发量已经超过约定并发配额，并且服务总并发量也已经超过设定的总并发配额，限制访问	并发控制超限，请控制并发量请联系我们'
    ];

    /**
     * 获取错误地址，正确返回array，错误直接返回错误信息
     * @param string $ip
     * @param string $bai_du_developer_key 百度开发者key
     * @param int $duration 缓存时间
     * @return mixed|string
     */
    public static function getAddress($ip, $bai_du_developer_key, $duration = 0) {
        if (!ClVerify::isIp($ip)) {
            return 'ip地址错误：' . $ip;
        }
        if ($duration > 0) {
            $key    = ClCache::getKey($ip);
            $result = cache($key);
            if ($result === false) {
                $result = self::getAddressWithBaiDu($ip, $bai_du_developer_key);
                //存储
                cache($key, $result, $duration);
            }
        } else {
            $result = self::getAddressWithBaiDu($ip, $bai_du_developer_key);
        }
        return $result;
    }

    /**
     * 按百度获取
     * @param string $ip
     * @param string $bai_du_developer_key
     * @param integer $duration 缓存时间
     * @return mixed
     */
    private static function getAddressWithBaiDu($ip, $bai_du_developer_key, $duration = 3600 * 24) {
        $r = ClHttp::request(sprintf('http://api.map.baidu.com/location/ip?ip=%s&ak=%s&coor=bd09ll', $ip, $bai_du_developer_key), [], false, $duration);
        if ($r['status'] == 0) {
            return $r['content'];
        } else {
            return self::$error_code[$r['status']];
        }
    }

}