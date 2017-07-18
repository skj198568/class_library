<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/7/18
 * Time: 15:56
 */

/**
 * 写日志函数
 */
function log_info()
{
    $args = func_get_args();
    if (!empty($args)) {
        $log_str = '';
        foreach ($args as $each) {
            if (is_array($each) || is_object($each)) {
                //转换
                array_walk_recursive($each, function (&$val) {
                    if ($val === true) {
                        $val = 'TRUE';
                    } else if ($val === false) {
                        $val = 'FALSE';
                    } else if ($val === null) {
                        $val = 'NULL';
                    }
                });
                if ($log_str === '') {
                    $log_str = print_r($each, true);
                } else {
                    $log_str .= ', ' . print_r($each, true);
                }
            } else if (is_bool($each)) {
                if ($log_str === '') {
                    $log_str = $each ? 'TRUE' : 'FALSE';
                } else {
                    $log_str .= ', ' . ($each ? 'TRUE' : 'FALSE');
                }
            } else if (is_null($each)) {
                if ($log_str === '') {
                    $log_str = 'null';
                } else {
                    $log_str .= ', null';
                }
            } else {
                if ($log_str === '') {
                    $log_str = $each;
                } else {
                    $log_str .= ', ' . $each;
                }
            }
        }
        Log::record('[' . \ClassLibrary\ClCache::getFunctionHistory(2) . ']' . $log_str, Log::LOG);
    }
}