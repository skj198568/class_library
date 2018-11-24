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
     * 检查时删除的key
     * @var array
     */
    private static $check_key_is_valid_del_keys = [];

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
            } else if (is_string($each)) {
                if (strlen($each) < 10) {
                    $key .= self::$seg_str . $each;
                } else {
                    $key .= self::$seg_str . ClString::toCrc32($each);
                }
            } else if (is_array($each) || is_object($each)) {
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
        foreach ([$function, $function . self::$seg_str . 'mkl0'] as $key_each) {
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
    private static function addToKeysList($key) {
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
            $current_is_valid = self::checkKeyIsValid($key_father_temp, $father_key[$i + 1]);
            if (!$is_valid || !$current_is_valid) {
                $is_valid = false;
            }
        }
        if (!$is_valid) {
            cache($key, null);
        }
        return true;
    }

    /**
     * 校验key是否有效
     * @param string $key_father_temp
     * @param string $value
     * @param int $i 递减计数
     * @return bool
     */
    private static function checkKeyIsValid($key_father_temp, $value, $i = 2, $son_map_key_level = '') {
        if (empty($son_map_key_level)) {
            $son_map_key_level = $key_father_temp . self::$seg_str . $value;
        }
        $is_valid = true;
        if ($i == 0) {
            $map_key_level = $key_father_temp . self::$seg_str . 'mkl' . $i;
        } else {
            $map_key_level = $key_father_temp . self::$seg_str . self::getMapKey($son_map_key_level, 'mkl' . $i . '_');
        }
        $map_values_level = cache($map_key_level);
        if ($map_values_level === false) {
            $is_valid = false;
            cache($map_key_level, [$son_map_key_level]);
        } else {
            if (!in_array($son_map_key_level, $map_values_level)) {
                $is_valid           = false;
                $map_values_level[] = $son_map_key_level;
                cache($map_key_level, $map_values_level);
            }
        }
        if (!$is_valid) {
            $delete_key = $key_father_temp . self::$seg_str . $value . self::$seg_str . 'mkl0';
            if (!in_array($delete_key, self::$check_key_is_valid_del_keys)) {
                self::$check_key_is_valid_del_keys[] = $delete_key;
                //删除
                cache($delete_key, null);
            }
        }
        if ($i > 0) {
            $i--;
            $son_is_valid = self::checkKeyIsValid($key_father_temp, $value, $i, $map_key_level);
            $is_valid     = (!$is_valid || !$son_is_valid) ? false : true;
        }
        if ($i == 0) {
            //清空
            self::$check_key_is_valid_del_keys = [];
        }
        return $is_valid;
    }

    /**
     * 获取map key
     * @param $value
     * @param string $pre_suffix 前缀
     * @param int $array_count 每个数组存储的数量
     * @return string
     */
    private static function getMapKey($value, $pre_suffix = 'cache_mk_', $array_count = 500) {
        //将非数字类型转换成数字
        if (!is_numeric($value)) {
            $value = [$value];
            $value = json_encode($value);
            $value = ClString::toCrc32($value);
        }
        //根据每个数组里面储存数量进行计算
        $value           = ceil($value / $array_count);
        $key             = [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        ];
        $map_key         = $pre_suffix;
        $cut_value_array = [62 * 62 * 62 * 62, 62 * 62 * 62, 62 * 62, 62, 0];
        foreach ($cut_value_array as $cut_value) {
            if ($value >= $cut_value) {
                $index   = empty($cut_value) ? 0 : floor($value / $cut_value);
                $map_key .= $key[$index];
                $value   -= $index * $cut_value;
            }
        }
        return $map_key;
    }

}
