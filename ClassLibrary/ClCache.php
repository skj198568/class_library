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
        //处理无效缓存数据
        self::dealInvalidDataByKey($function);
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
        foreach ([$function, $function . self::$seg_str . 'k'] as $key_each) {
            //如果缓存存在
            if (cache($key_each) !== false) {
                cache($key_each, null);
            }
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
        for ($i = 0; $i < $father_key_count; $i++) {
            if ($key_father_temp == '') {
                $key_father_temp = $father_key[$i];
            } else {
                $key_father_temp = $key_father_temp . self::$seg_str . $father_key[$i];
            }
            $key_temp = $key_father_temp . self::$seg_str . $father_key[$i + 1];
            $map      = cache($key_father_temp . self::$seg_str . 'k');
            if (empty($map)) {
                cache($key_father_temp . self::$seg_str . 'k', [$key_temp]);
            } else {
                if (!in_array($key_temp, $map)) {
                    $map[] = $key_temp;
                    cache($key_father_temp . self::$seg_str . 'k', $map);
                }
            }
        }
        return true;
    }

    /**
     * 处理无效数据
     * @param $key
     * @return bool
     */
    public static function dealInvalidDataByKey($key) {
        //没有子存储或者缓存不存在
        if (strpos($key, self::$seg_str) === false || cache($key) === false) {
            return false;
        }
        $father_key       = explode(self::$seg_str, $key);
        $key_father_temp  = '';
        $father_key_count = count($father_key) - 2;
        $is_valid         = true;
        $del_keys         = [];
        for ($i = 0; $i < $father_key_count; $i++) {
            if ($key_father_temp == '') {
                $key_father_temp = $father_key[$i];
            } else {
                $key_father_temp = $key_father_temp . self::$seg_str . $father_key[$i];
            }
            $key_temp = $key_father_temp . self::$seg_str . $father_key[$i + 1];
            $map      = cache($key_father_temp . self::$seg_str . 'k');
            //map不存在，或者是不在map里，均认为该key对应的value，不是最新的值
            if (!(is_array($map) && in_array($key_temp, $map))) {
                $is_valid = false;
            }
            if (!$is_valid) {
                $del_keys[] = $key_temp . self::$seg_str . 'k';
            }
        }
        if (!$is_valid) {
            $del_keys[] = $key;
            //批量删除
            foreach ($del_keys as $each_del_key) {
                cache($each_del_key, null);
            }
        }
        return true;
    }

}
