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
class ClCache
{

    /**
     * key分隔符
     * @var string
     */
    private static $seg_str = '_';

    /**
     * 获取历史调用函数的函数名，主要用于生成缓存
     * @param int $index 函数调用逆序
     * @return string
     */
    public static function getFunctionHistory($index = 1)
    {
        $functions = debug_backtrace();
        if (count($functions) > 0 && array_key_exists($index, $functions)) {
            if (array_key_exists('class', $functions[$index])) {
                $class_array = explode('\\', $functions[$index]['class']);
                return array_pop($class_array) . ucfirst($functions[$index]['function']);
            } else {
                return $functions[$index]['function'];
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
    private static function createKeyByParams($params)
    {
        $key = '';
        foreach ($params as $each) {
            if(is_string($each)){
                $each = trim($each);
            }
            if (ClVerify::isInt($each)) {
                $key .= self::$seg_str . $each;
            } else if (is_array($each)) {
                $key .= self::$seg_str . ClString::toCrc32(ClArray::toString($each));
            } else if (is_bool($each)) {
                if($each){
                    $key .= self::$seg_str . 'true';
                }else{
                    $key .= self::$seg_str . 'false';
                }
            }else if (is_null($each)) {
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
    public static function getKey()
    {
        $function = self::getFunctionHistory(2);
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
    public static function remove()
    {
        $args = func_get_args();
        $function = self::getFunctionHistory(2);
        //删除Rc后缀
        $function = str_replace('Rc', '', $function);
        if (count($args) > 0) {
            $function .= self::createKeyByParams($args);
        }
        $keys = self::getRemoveKeys($function);
        if (!empty($keys)) {
            foreach ($keys as $key_each) {
                cache($key_each, null);
            }
        }
        return true;
    }

    /**
     * 获取要删除的keys
     * @param $root_key
     * @return array
     */
    private static function getRemoveKeys($root_key)
    {
        $keys = [];
        $keys_list = cache($root_key . self::$seg_str . 'clCacheKeys');
        if (empty($keys_list)) {
            return [$root_key];
        } else {
            foreach ($keys_list as $key_each) {
                if (!empty(cache($key_each . self::$seg_str . 'clCacheKeys'))) {
                    $keys = array_merge($keys, self::getRemoveKeys($key_each));
                } else {
                    array_push($keys, $key_each);
                }
            }
        }
        return $keys;
    }

    /**
     * 添加key至缓存key队列
     * @param $key
     * @return bool|mixed
     */
    public static function addToKeysList($key)
    {
        if (strpos($key, self::$seg_str) === false) {
            return false;
        }
        $father_key = explode(self::$seg_str, $key);
        $key_father_temp = '';
        $key_temp = '';
        $map = [];
        $father_key_count = count($father_key) - 1;
        for ($i = 0; $i < $father_key_count; $i++) {
            if ($key_father_temp == '') {
                $key_father_temp = $father_key[$i];
            } else {
                $key_father_temp = $key_father_temp . self::$seg_str . $father_key[$i];
            }
            $key_temp = $key_father_temp . self::$seg_str . $father_key[$i + 1];
            $map = cache($key_father_temp . self::$seg_str . 'clCacheKeys');
            if (empty($map)) {
                cache($key_father_temp . self::$seg_str . 'clCacheKeys', [$key_temp]);
            } else {
                if (!in_array($key_temp, $map)) {
                    $map[] = $key_temp;
                    cache($key_father_temp . self::$seg_str . 'clCacheKeys', $map);
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
    public static function dealInvalidDataByKey($key)
    {
        if (strpos($key, self::$seg_str) === false) {
            return false;
        }
        $father_key = explode(self::$seg_str, $key);
        $key_father_temp = '';
        $key_temp = '';
        $map = [];
        $father_key_count = count($father_key) - 1;
        for ($i = 0; $i < $father_key_count; $i++) {
            if ($key_father_temp == '') {
                $key_father_temp = $father_key[$i];
            } else {
                $key_father_temp = $key_father_temp . self::$seg_str . $father_key[$i];
            }
            $key_temp = $key_father_temp . self::$seg_str . $father_key[$i + 1];
            $map = cache($key_father_temp . self::$seg_str . 'clCacheKeys');
            //map不存在，或者是不在map里，均认为该key对应的value，不是最新的值
            if (!(is_array($map) && in_array($key_temp, $map))) {
                //该key对应的value为无效数据，进行删除操作
                cache($key, null);
                break;
            }
        }
        return true;
    }
}
