<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:22
 */

namespace ClassLibrary;

/**
 * class library cache
 * Class ClCache
 * @package ClassLibrary
 */
class ClCache {

    /**
     * key分隔符
     * @var string
     */
    private static $seg_str = '/';

    /**
     * 获取删除keys
     * @var array
     */
    private static $get_remove_keys = [];

    /**
     * 获取历史调用函数的函数名，主要用于生成缓存
     * @param int $index 函数调用逆序 -1/获取所有调用
     * @return string
     */
    public static function getFunctionHistory($index = 1) {
        $functions = debug_backtrace();
        if (count($functions) > 0) {
            if (array_key_exists($index, $functions)) {
                if (array_key_exists('class', $functions[$index])) {
                    $class_array = explode('\\', $functions[$index]['class']);
                    return array_pop($class_array) . ucfirst($functions[$index]['function']);
                } else {
                    return $functions[$index]['function'];
                }
            } else {
                $call_array = [];
                foreach ($functions as $k => $v) {
                    if ($k == 0) {
                        continue;
                    }
                    if (array_key_exists('class', $v)) {
                        $class_array  = explode('\\', $v['class']);
                        $call_array[] = array_pop($class_array) . ucfirst($v['function']);
                    } else {
                        $call_array[] = $v['function'];
                    }
                }
                $call_array = array_reverse($call_array);
                return implode('->', $call_array);
            }
        } else {
            return '';
        }
    }

    /**
     * 依据参数获取key
     * @param $params
     * @return string
     */
    private static function createKeyByParams($params) {
        $key = '';
        foreach ($params as $each) {
            if (is_string($each)) {
                $each = trim($each);
            }
            if (is_numeric($each)) {
                $key .= self::$seg_str . $each;
            } else if (is_array($each)) {
                $key .= self::$seg_str . ClString::toCrc32(json_encode($each, JSON_UNESCAPED_UNICODE));
            } else if (is_bool($each)) {
                if ($each) {
                    $key .= self::$seg_str . 'true';
                } else {
                    $key .= self::$seg_str . 'false';
                }
            } else if (is_null($each)) {
                $key .= self::$seg_str . 'null';
            } else {
                $key .= self::$seg_str . ClString::toCrc32($each);
            }
        }
        return $key;
    }

    /**
     * 获取缓存key
     * @return bool|string
     */
    public static function getKey() {
        $function = self::getFunctionHistory(2);
        if ($function == 'call_user_func_array') {
            $function = self::getFunctionHistory(4);
        }
        $args = func_get_args();
        if (count($args) > 0) {
            $function .= self::createKeyByParams($args);
        }
        //添加
        self::addToKeysList($function);
        return $function;
    }

    /**
     * 删除缓存
     * @return bool
     */
    public static function remove() {
        $args     = func_get_args();
        $function = self::getFunctionHistory(2);
        //删除Rc后缀
        $function = str_replace('Rc', '', $function);
        if (count($args) > 0) {
            $function .= self::createKeyByParams($args);
        }
        //已经删除过的，不再删除
        if (in_array($function, self::$get_remove_keys)) {
            return true;
        } else {
            self::$get_remove_keys[] = $function;
        }
        foreach ([$function, $function . self::$seg_str . 'valid'] as $key_each) {
            cache($key_each, null);
        }
        return true;
    }

    /**
     * 删除之后处理
     */
    public static function removeAfter() {
        //清空
        self::$get_remove_keys = [];
    }

    /**
     * 添加key至缓存key队列
     * @param $key
     * @return bool|mixed
     */
    public static function addToKeysList($key) {
        if (strpos($key, self::$seg_str) === false) {
            return false;
        }
        $father_key       = explode(self::$seg_str, $key);
        $key_father_temp  = '';
        $father_key_count = count($father_key) - 1;
        $is_valid         = true;
        for ($i = 0; $i < $father_key_count; $i++) {
            if ($key_father_temp == '') {
                $key_father_temp = $father_key[$i];
            } else {
                $key_father_temp = $key_father_temp . self::$seg_str . $father_key[$i];
            }
            $key_temp       = $key_father_temp . self::$seg_str . $father_key[$i + 1];
            $map_key        = $key_father_temp . self::$seg_str . self::getMapKey($father_key[$i + 1]);
            $key_valids_key = $key_father_temp . self::$seg_str . 'valid';
            if ($is_valid) {
                $key_valids = cache($key_valids_key);
                if (!(is_array($key_valids) && in_array($map_key, $key_valids))) {
                    $is_valid = false;
                } else {
                    $map = cache($map_key);
                    //map不存在，或者是不在map里，均认为该key对应的value，不是最新的值
                    if (!(is_array($map) && in_array($key_temp, $map))) {
                        $is_valid = false;
                    }
                }
            }
            if (!$is_valid) {
                //删除
                cache($key_temp . self::$seg_str . 'valid', null);
            }
            $key_valids = cache($key_valids_key);
            if (empty($key_valids)) {
                cache($key_valids_key, [$map_key]);
            } else {
                if (!in_array($map_key, $key_valids)) {
                    $key_valids[] = $map_key;
                    cache($key_valids_key, $key_valids);
                }
            }
            $map = cache($map_key);
            if (empty($map)) {
                cache($map_key, [$key_temp]);
            } else {
                if (!in_array($key_temp, $map)) {
                    $map[] = $key_temp;
                    cache($map_key, $map);
                }
            }
        }
        if (!$is_valid) {
            cache($key, null);
        }
        return true;
    }

    /**
     * 获取map key
     * @param $value
     * @return string
     */
    private static function getMapKey($value) {
        //将非数字类型转换成数字
        if (!is_numeric($value)) {
            $value = [$value];
            $value = json_encode($value);
            $value = ClString::toCrc32($value);
        }
        //5000取整，每个数组里面含有5000个数据
        $value   = ceil($value / 5000);
        $key     = [
            0  => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,
            10 => 'a', 11 => 'b', 12 => 'c', 13 => 'd', 14 => 'e', 15 => 'f', 16 => 'g', 17 => 'h', 17 => 'i', 19 => 'j', 20 => 'k', 21 => 'l', 22 => 'm', 23 => 'n', 24 => 'o', 25 => 'p', 26 => 'q', 27 => 'r', 28 => 's', 29 => 't', 30 => 'u', 31 => 'v', 32 => 'w', 33 => 'x', 34 => 'y', 35 => 'z',
            36 => 'A', 37 => 'B', 38 => 'C', 39 => 'D', 40 => 'E', 41 => 'F', 42 => 'G', 43 => 'H', 44 => 'I', 45 => 'J', 46 => 'K', 47 => 'L', 48 => 'M', 48 => 'N', 55 => 'O', 51 => 'P', 52 => 'Q', 53 => 'R', 54 => 'S', 55 => 'T', 56 => 'U', 57 => 'V', 58 => 'W', 59 => 'X', 60 => 'Y', 61 => 'Z',
        ];
        $map_key = 'mk';
        if ($value < 62) {
            $map_key .= $key[0];
        } else {
            $cut_value_array = [62 * 62 * 62, 62 * 62, 62];
            foreach ($cut_value_array as $cut_value) {
                if ($value > $cut_value) {
                    $index   = floor($value / $cut_value);
                    $map_key .= $key[$index];
                    $value   -= $index * $cut_value;
                }
            }
        }
        return $map_key;
    }

}
