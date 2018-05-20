<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:27
 */

namespace ClassLibrary;


/**
 * Class ClDate(class library date日期类)
 * @package Common\Common
 */
class ClDate {

    /**
     * 返回两个时间的相距时间，*年*月*日*时*分*秒
     * @param int $one_time 时间一
     * @param int $two_time 时间二
     * @param array $format_array 格式化字符，例，array('年', '个月', '天', '小时', '分', '秒')
     * @return String or false
     */
    public static function getRemainderTime($one_time, $two_time, $format_array = ['年', '个月', '天', '小时', '分', '秒']) {
        if (!(is_numeric($one_time) && is_numeric($two_time))) {
            return false;
        }
        $remainder_seconds = abs($one_time - $two_time);
        //年
        $years = 0;
        if ($remainder_seconds - 31536000 > 0) {
            $years = floor($remainder_seconds / (31536000));
        }
        //月
        $months = 0;
        if ($remainder_seconds - $years * 31536000 - 2592000 > 0) {
            $months = floor(($remainder_seconds - $years * 31536000) / (2592000));
        }
        //日
        $days = 0;
        if ($remainder_seconds - $years * 31536000 - $months * 2592000 - 86400 > 0) {
            $days = floor(($remainder_seconds - $years * 31536000 - $months * 2592000) / (86400));
        }
        //时
        $hours = 0;
        if ($remainder_seconds - $years * 31536000 - $months * 2592000 - $days * 86400 - 3600 > 0) {
            $hours = floor(($remainder_seconds - $years * 31536000 - $months * 2592000 - $days * 86400) / 3600);
        }
        //分
        $minutes = 0;
        if ($remainder_seconds - $years * 31536000 - $months * 2592000 - $days * 86400 - $hours * 3600 - 60 > 0) {
            $minutes = floor(($remainder_seconds - $years * 31536000 - $months * 2592000 - $days * 86400 - $hours * 3600) / 60);
        }
        //秒
        $seconds = $remainder_seconds - $years * 31536000 - $months * 2592000 - $days * 86400 - $hours * 3600 - $minutes * 60;
        return ($years > 0 ? $years . $format_array[0] : '') . ($months > 0 ? $months . $format_array[1] : '') . ($days > 0 ? $days . $format_array[2] : '') . ($hours > 0 ? $hours . $format_array[3] : '') . ($minutes > 0 ? $minutes . $format_array[4] : '') . $seconds . $format_array[5];
    }

    /**
     * 获取毫秒
     * @return float
     */
    public static function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 获取微秒
     * @return mixed
     */
    public static function getMicrosecond() {
        return microtime(true);
    }

    /**
     * 获取一个月的开始时间和结束时间戳
     * @param $month 201509
     * @return array
     */
    public static function getMonthBetweenTimestamp($month) {
        $month         = ClString::getInt($month);
        $return        = array();
        $return[0]     = strtotime($month . '01 00:00:00');
        $current_year  = $month{0} . $month{1} . $month{2} . $month{3};
        $current_month = $month{4} . $month{5};
        if ($current_month == '12') {
            $return[1] = (intval($current_year) + 1) . '0101 00:00:00';
        } else {
            $current_month = intval($current_month) + 1;
            $return[1]     = $current_year . (strlen($current_month) == 1 ? '0' . $current_month : $current_month) . '01 00:00:00';
        }
        $return[1] = strtotime($return[1]) - 1;
        return $return;
    }

    /**
     * 获取两个时间戳之间的月份
     * @param $one_timestamp
     * @param $two_timestamp
     * @return array
     */
    public static function getMonthsBetweenTimestamp($one_timestamp, $two_timestamp) {
        //对换数据，保证$one_timestamp较小
        if ($one_timestamp > $two_timestamp) {
            $temp_timestamp = $two_timestamp;
            $two_timestamp  = $one_timestamp;
            $one_timestamp  = $temp_timestamp;
            unset($temp_timestamp);
        }
        $month_array = [];
        $one_year    = intval(date('Y', $one_timestamp));
        $one_month   = intval(date('m', $one_timestamp));
        $two_year    = intval(date('Y', $two_timestamp));
        $two_month   = intval(date('m', $two_timestamp));
        for ($i = $one_year; $i <= $two_year; $i++) {
            foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] as $month) {
                if ($i == $one_year || $i == $two_year) {
                    if ($i == $one_year && $i == $two_year) {
                        if ($month >= $one_month && $month <= $two_month) {
                            $month_array[] = $i . self::addZero($month);
                        }
                    } else {
                        if ($i == $one_year && $month >= $one_month) {
                            $month_array[] = $i . self::addZero($month);
                        } elseif ($i == $two_year && $month <= $two_month) {
                            $month_array[] = $i . self::addZero($month);
                        }
                    }
                } else {
                    $month_array[] = $i . self::addZero($month);
                }
            }
        }
        return $month_array;
    }

    /**
     * 获取两个时间戳之间的天数
     * @param $one_timestamp
     * @param $two_timestamp
     * @return array
     */
    public static function getDaysBetweenTimestamp($one_timestamp, $two_timestamp) {
        //对换数据，保证$one_timestamp较小
        if ($one_timestamp > $two_timestamp) {
            $temp_timestamp = $two_timestamp;
            $two_timestamp  = $one_timestamp;
            $one_timestamp  = $temp_timestamp;
            unset($temp_timestamp);
        }
        $one_year  = intval(date('Y', $one_timestamp));
        $one_month = intval(date('m', $one_timestamp));
        $two_year  = intval(date('Y', $two_timestamp));
        $two_month = intval(date('m', $two_timestamp));
        $day_array = [];
        foreach (self::getMonthsBetweenTimestamp($one_timestamp, $two_timestamp) as $month) {
            $timestamp_between_month = self::getMonthBetweenTimestamp($month);
            for ($day = intval(date('Ymd', $timestamp_between_month[0])); $day <= intval(date('Ymd', $timestamp_between_month[1])); $day++) {
                if (date('Ym', $timestamp_between_month[0]) == $one_year . self::addZero($one_month) || date('Ym', $timestamp_between_month[1]) == $two_year . self::addZero($two_month)) {
                    if (date('Ym', $timestamp_between_month[0]) == $one_year . self::addZero($one_month) && date('Ym', $timestamp_between_month[1]) == $two_year . self::addZero($two_month)) {
                        if ($day >= intval(date('Ymd', $one_timestamp)) && $day <= intval(date('Ymd', $two_timestamp))) {
                            $day_array[] = $day;
                        }
                    } else {
                        if (date('Ym', $timestamp_between_month[0]) == $one_year . self::addZero($one_month) && $day >= intval(date('Ymd', $one_timestamp))) {
                            $day_array[] = $day;
                        } else if (date('Ym', $timestamp_between_month[1]) == $two_year . self::addZero($two_month) && $day <= intval(date('Ymd', $two_timestamp))) {
                            $day_array[] = $day;
                        }
                    }
                } else {
                    $day_array[] = $day;
                }
            }
        }
        return $day_array;
    }

    /**
     * 时间加上0
     * @param $value
     * @return string
     */
    public static function addZero($value) {
        $value = ClString::getInt($value);
        if (strlen($value) == 1) {
            return '0' . $value;
        }
        return $value;
    }

    /**
     * 获取日期
     * @param $str 比如，2016年1月1日 2时5分6秒
     * @param string $format
     * @return string
     */
    public static function getDateFromString($str, $format = '') {
        //去除空格
        $str = ClString::spaceTrim($str);
        //获取分隔符
        $segmentation = ClString::toArray(preg_replace('/[\d]+/', '', $str));
        foreach ($segmentation as $key => $item) {
            $str                = array(
                ClString::split($str, $item, true, false),
                ClString::split($str, $item, false, false)
            );
            $segmentation[$key] = self::addZero($str[0]);
            if (count($str) == 2) {
                $str = $str[1];
            }
            if (count($segmentation) == $key + 1 && !empty($str)) {
                $segmentation[] = self::addZero($str);
            }
        }
        $str = implode('', $segmentation);
        if (!empty($format)) {
            $str = date($format, strtotime($str));
        }
        return $str;
    }

    /**
     * 格式化日期显示
     * @param $time
     * @return string
     */
    public static function formatShow($time) {
        $time = floor((time() - $time));
        $str  = '';
        if ($time < 0) {
            $str = '未知';
        } else if ($time < 60) {
            $str = '刚刚';
        } else if ($time < 60 * 60) {
            $time = floor($time / 60);
            $str  = $time . '分钟前';
        } else if ($time < 60 * 60 * 24) {
            $time = floor($time / (60 * 60));
            $str  = $time . '小时前';
        } else if ($time < 60 * 60 * 24 * 30) {
            $time = floor($time / (60 * 60 * 24));
            $str  = $time . '天前';
        } else if ($time < 60 * 60 * 24 * 30 * 12) {
            $time = floor($time / (60 * 60 * 24 * 30));
            $str  = $time . '个月前';
        } else {
            $time = floor($time / (60 * 60 * 24 * 30 * 12));
            $str  = $time . '年前';
        }
        return $str;
    }

}
