<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/6
 * Time: 10:07
 */

namespace ClassLibrary;

/**
 * Class ClSystem 系统级函数
 * @package ClassLibrary
 */
class ClSystem {

    /**
     * 是否是64位系统
     * @var null
     */
    private static $is_64bit = null;

    /**
     * 是否是windows
     * @var null
     */
    private static $is_win = null;

    /**
     * 是否是苹果mac
     * @var null
     */
    private static $is_apple_mac = null;

    /**
     * 是否是linux
     * @var null
     */
    private static $is_linux = null;

    /**
     * 本机ip
     * @var null
     */
    private static $local_ip = null;

    /**
     * Mac地址
     * @var null
     */
    private static $mac = null;

    /**
     * 判断是否是64位环境
     * @return bool|string
     */
    public static function is64bit() {
        if (self::$is_64bit !== null) {
            return self::$is_64bit;
        }
        $int = "9223372036854775807";
        $int = intval($int);
        if ($int == 9223372036854775807) {
            //64bit
            self::$is_64bit = true;
        } elseif ($int == 2147483647) {
            //32bit
            self::$is_64bit = false;
        } else {
            //error
            return "error";
        }
        return self::$is_64bit;
    }

    /**
     * 获取本机地址
     * @return string
     */
    public static function getLocalIp() {
        if (self::$local_ip !== null) {
            return self::$local_ip;
        }
        $preg = '/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/';
        if (self::isWin()) {
            //获取操作系统为win2000/xp、win7的本机IP真实地址
            exec("ipconfig", $out, $stats);
            if (!empty($out)) {
                foreach ($out AS $row) {
                    if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                        $tmpIp = explode(":", $row);
                        if (preg_match($preg, trim($tmpIp[1]))) {
                            self::$local_ip = trim($tmpIp[1]);
                        }
                    }
                }
            }
        } else {
            //获取操作系统为linux类型的本机IP真实地址
            exec("ifconfig", $out, $stats);
            if (!empty($out)) {
                if (isset($out[1]) && strstr($out[1], 'addr:')) {
                    $tmpArray = explode(":", $out[1]);
                    $tmpIp    = explode(" ", $tmpArray[1]);
                    if (preg_match($preg, trim($tmpIp[0]))) {
                        self::$local_ip = trim($tmpIp[0]);
                    }
                }
            }
        }
        if (self::$local_ip === null) {
            self::$local_ip = '127.0.0.1';
        }
        return self::$local_ip;
    }

    /**
     * 获取外网ip地址
     * @return mixed|string
     */
    public static function getIp() {
        $result = ClHttp::request('http://ip.360.cn/IPShare/info', [], ClHttp::REQUEST_RESULT_TYPE_JSON);
        if (is_array($result) && isset($result['ip'])) {
            return $result['ip'];
        } else {
            return '';
        }
    }

    /**
     * 判断系统是否是windows
     * @return bool
     */
    public static function isWin() {
        if (self::$is_win !== null) {
            return self::$is_win;
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            self::$is_win = true;
        } else {
            self::$is_win = false;
        }
        return self::$is_win;
    }

    /**
     * 是否是苹果mac
     * @return bool|null
     */
    public static function isAppleMac() {
        if (self::$is_apple_mac !== null) {
            return self::$is_apple_mac;
        }
        if (strtoupper(PHP_OS) === 'DARWIN') {
            self::$is_apple_mac = true;
        } else {
            self::$is_apple_mac = false;
        }
        return self::$is_apple_mac;
    }

    /**
     * 是否是linux
     * @return bool|null
     */
    public static function isLinux() {
        if (self::$is_linux !== null) {
            return self::$is_linux;
        }
        if (strtoupper(PHP_OS) === 'LINUX') {
            self::$is_linux = true;
        } else {
            self::$is_linux = false;
        }
        return self::$is_linux;
    }

    /**
     * 获取mac地址
     * @return null
     */
    public static function getMac() {
        if (self::$mac != null) {
            return self::$mac;
        }
        $return_array = array();
        if (self::isWin()) {
            @exec("ipconfig /all", $return_array);
            if (empty($return_array)) {
                $ipconfig = $_SERVER["WINDIR"] . '\system32\ipconfig.exe';
                if (is_file($ipconfig)) {
                    @exec($ipconfig . " /all", $return_array);
                } else {
                    @exec($_SERVER["WINDIR"] . '\system\ipconfig.exe /all', $return_array);
                }
            }
        } else {
            @exec("ifconfig -a", $return_array);
        }
        foreach ($return_array as $value) {
            if (preg_match("/[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f]/i", $value, $temp_array)) {
                self::$mac = $temp_array[0];
                break;
            }
        }
        unset($return_array, $temp_array);
        return self::$mac;
    }

}
