<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:37
 */

namespace ClassLibrary;

use think\Exception;
use think\exception\ErrorException;

/**
 * Class ClString 字符串类库
 * @package ClassLibrary
 */
class ClString {

    /**
     * 计算中文字符串长度，支持中文
     * @param string $string
     * @return int
     */
    public static function getLength($string) {
        if (is_string($string) || is_numeric($string)) {
            // 将字符串分解为单元
            preg_match_all('/./us', $string, $match);
            // 返回单元个数
            return count($match[0]);
        } else {
            return 0;
        }
    }

    const V_ENCODE_GB2312 = 'GB2312';
    const V_ENCODE_UTF8 = 'UTF-8';
    const V_ENCODE_ASCII = 'ASCII';
    const V_ENCODE_GBK = 'GBK';
    const V_ENCODE_BIG5 = 'BIG5';
    const V_ENCODE_JIS = 'JIS';
    const V_ENCODE_eucjp_win = 'eucjp-win';
    const V_ENCODE_sjis_win = 'sjis-win';
    const V_ENCODE_EUC_JP = 'EUC-JP';

    /**
     * 自动检测内容并且进行转换编码
     * @param string $string
     * @param string $to
     * @return string
     */
    public static function encoding($string, $to = 'UTF-8') {
        $encode_arr = ['GB2312', 'UTF-8', 'ASCII', 'GBK', 'BIG5', 'JIS', 'eucjp-win', 'sjis-win', 'EUC-JP'];
        $encoded    = mb_detect_encoding($string, $encode_arr);
        $string     = mb_convert_encoding($string, $to, $encoded);
        return $string;
    }

    /**
     * 获取crc32字符串结果的正整数
     * @param string $str
     * @return string 正整数
     */
    public static function toCrc32($str) {
        return self::toFloat(sprintf('%u', crc32($str)));
    }

    /**
     * 转换为浮点型
     * @param string $str
     * @return float
     */
    public static function toFloat($str) {
        return floatval($str);
    }

    /**
     * boolean格式转换为数字0,1
     * @param string $boolean
     * @return int
     */
    public static function boolean2int($boolean) {
        if (ClVerify::isBoolean($boolean)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 格式化金钱为万分单位分割
     * @param string $money
     * @return string
     */
    public static function moneyFormat($money) {
        $money      = strval($money);
        $money      = strrev($money);
        $return_str = '';
        //是否存在小数
        if (strpos($money, '.')) {
            $money_array = explode('.', $money);
            for ($i = 0; $i < strlen($money_array[1]); $i++) {
                $return_str .= $money_array[1]{$i};
                if (($i + 1) % 4 == 0 && ($i + 1) < strlen($money_array[1])) {
                    $return_str .= ',';
                }
            }
            $return_str = $money_array[0] . '.' . $return_str;
        } else {
            for ($i = 0; $i < strlen($money); $i++) {
                $return_str .= $money{$i};
                if (($i + 1) % 4 == 0 && ($i + 1) < strlen($money)) {
                    $return_str .= ',';
                }
            }
        }
        return strrev($return_str);
    }

    /**
     * 将字符串打散为数组
     * @param string $str
     * @return mixed
     */
    public static function toArray($str) {
        preg_match_all('/./u', $str, $arr);
        unset($str);
        return $arr[0];
    }

    /**
     * 去除空格，包括中英文
     * @param string $string
     * @return string
     */
    public static function spaceTrim($string) {
        if (strlen($string) > 0) {
            $str_array = ClString::toArray($string);
            foreach ($str_array as $k => $each) {
                if (ord($each) == 194) {
                    unset($str_array[$k]);
                }
            }
            $string = implode('', $str_array);
        }
        return trim(preg_replace('/[\s]+/', '', $string));
    }

    /**
     * 多个空格转换为一个空格
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function spaceManyToOne($string, $separator = ' ') {
        if (strlen($string) > 0) {
            $str_array = ClString::toArray($string);
            foreach ($str_array as $k => $each) {
                if (ord($each) == 194) {
                    unset($str_array[$k]);
                }
            }
            $string = implode('', $str_array);
        }
        return trim(preg_replace('/[\s]+/', $separator, $string));
    }

    /**
     * 获取数字字符串
     * @param string $s
     * @return string
     */
    public static function getInt($s) {
        preg_match_all('/\d+/', $s, $arr);
        $s = '';
        if (!empty($arr[0])) {
            $s = implode('', $arr[0]);
        }
        return $s;
    }

    /**
     * 获取所有的中文
     * @param string $s
     * @return string
     */
    public static function getChinese($s) {
        $array = self::toArray($s);
        $str   = '';
        foreach ($array as $each) {
            if (ClVerify::isChinese($each)) {
                $str .= $each;
            }
        }
        return $str;
    }

    /**
     * 把格式化的字符串写入变量中，支持数组参数
     * @param string $str
     * @param array $value_array
     * @return mixed
     */
    public static function sprintf($str, $value_array) {
        array_unshift($value_array, $str);
        return call_user_func_array('sprintf', $value_array);
    }

    /**
     * 仅替换一次
     * @param string $search
     * @param string $replace
     * @param string $string
     * @return mixed
     */
    public static function replaceOnce($search, $replace, $string) {
        if (empty($search)) {
            return $string;
        }
        $pos = strpos($string, $search);
        if ($pos === false) {
            return $string;
        }
        return substr_replace($string, $replace, $pos, strlen($search));
    }

    /**
     * 分割字符串，会默认转为小写
     * @param string $string 待分割的字符
     * @param string $separator_tag 分割标签
     * @param bool $get_before 分割标签之前
     * @param bool $is_include_tag 是否包含分割标签
     * @return string 返回分割后的结果
     */
    public static function split($string, $separator_tag, $get_before = true, $is_include_tag = true) {
        if ($separator_tag === '') {
            return $string;
        }
        $lc_str     = strtolower($string);
        $marker     = strtolower($separator_tag);
        $split_here = strpos($lc_str, $marker);
        if ($split_here === false) {
            return '';
        }
        if ($get_before) {
            if ($is_include_tag != false) {
                $split_here = strpos($lc_str, $marker) + strlen($marker);
            }
            $parsed_string = substr($string, 0, $split_here);
        } else {
            if ($is_include_tag == false) {
                $split_here = $split_here + strlen($marker);
            }
            $parsed_string = substr($string, $split_here, strlen($string));
        }
        return $parsed_string;
    }

    /**
     * 获取字符串标签中间的值
     * @param string $string
     * @param string $begin_tag :开始标签
     * @param string $end_tag :结束标签，如果为空，则直接获取到最后
     * @param bool|true $is_include_tag :是否包含标签
     * @return string 结果
     */
    public static function getBetween($string, $begin_tag, $end_tag = '', $is_include_tag = true) {
        $string = self::split($string, $begin_tag, false, true);
        if ($end_tag !== '') {
            //end_tag不为空时
            if (!empty($begin_tag)) {
                //先去除begin_tag
                if (strpos($string, $begin_tag) !== false) {
                    $string = substr($string, strlen($begin_tag));
                }
            }
            $string = self::split($string, $end_tag, true, true);
            //拼接上begin_tag
            $string = $begin_tag . $string;
            //在end_tag仍存在的情况下，判断begin_tag是否存在多个
            if (!empty($string) && !empty($begin_tag) && (strpos($string, $begin_tag) != strrpos($string, $begin_tag))) {
                $string = substr($string, strlen($begin_tag));
                //当end_tag存在，且位置大于begin_tag
                if (strpos($string, $end_tag) !== false && strpos($string, $end_tag) > strpos($string, $begin_tag)) {
                    $string = self::getBetween($string, $begin_tag, $end_tag, true);
                }
            }
        } else {
            //只需判断begin_tag是否仍旧存在
            if (!empty($string) && !empty($begin_tag) && (strpos($string, $begin_tag) != strrpos($string, $begin_tag))) {
                $string = substr($string, strlen($begin_tag));
                $string = self::getBetween($string, $begin_tag, $end_tag, true);
            }
        }
        //最后处理是否包含标签
        if (!$is_include_tag) {
            if ($begin_tag !== '') {
                if (strpos($string, $begin_tag) !== false) {
                    $string = substr($string, strlen($begin_tag));
                }
            }
            if ($end_tag !== '') {
                if (strpos($string, $end_tag) !== false) {
                    $string = substr($string, 0, strlen($string) - strlen($end_tag));
                }
            }
        }
        //去除两端空格及换行符、tab等
        $string = trim(trim(trim($string), "\n"), "\t");
        return $string;
    }

    /**
     * 解析为数组
     * @param string $string
     * @param string $begin_tag
     * @param string $end_tag
     * @param bool|true $is_include_tag 是否包含标签
     * @return mixed
     */
    public static function parseToArray($string, $begin_tag, $end_tag, $is_include_tag = true) {
        $preg_quote_begin_tag = preg_quote($begin_tag);
        $preg_quote_end_tag   = preg_quote($end_tag);
        preg_match_all("($preg_quote_begin_tag(.*)$preg_quote_end_tag)siU", $string, $matching_data);
        //循环处理，获取最小结果值
        foreach ($matching_data[0] as $k => $each) {
            while (substr_count($each, $begin_tag) > 1 || substr_count($each, $end_tag) > 1) {
                if (substr_count($each, $begin_tag) > 1) {
                    $each = trim($each, $begin_tag);
                }
                if (substr_count($each, $end_tag) > 1) {
                    $each = trim($each, $end_tag);
                }
                $each = ClString::getBetween($each, $begin_tag, $end_tag, $is_include_tag);
            }
            if ($is_include_tag == false) {
                $each = str_replace($begin_tag, '', $each);
                $each = str_replace($end_tag, '', $each);
            }
            $matching_data[0][$k] = $each;
        }
        return $matching_data[0];
    }

    /**
     * 获取Xml结构类型的标签属性
     * @param string $string
     * @param string $attribute :属性
     * @return string
     */
    public static function getAttribute($string, $attribute) {
        # Use Tidy library to 'clean' input
        $cleaned_html = self::tidyHtml($string);
        # Remove all line feeds from the string
        $cleaned_html = str_replace("\r", '', $cleaned_html);
        $cleaned_html = str_replace("\n", '', $cleaned_html);
        # Use return_between() to find the properly quoted value for the attribute
        return self::getBetween($cleaned_html, strtolower($attribute) . '="', '"', false);
    }

    /**
     * 删除标签之间的数据
     * @param string $string
     * @param string $begin_tag :开始标签
     * @param string $end_tag :结束标签
     * @param bool|false $is_remove_tag :是否删除标签
     * @return mixed
     */
    public static function remove($string, $begin_tag, $end_tag, $is_remove_tag = false) {
        $remove_array = self::parseToArray($string, $begin_tag, $end_tag, $is_remove_tag);
        //循环替换
        for ($xx = 0; $xx < count($remove_array); $xx++) {
            $string = str_replace($remove_array, '', $string);
        }
        return $string;
    }

    /**
     * 格式化html，注意开启php的tidy扩展
     * @param string $input_string
     * @return string
     */
    public static function tidyHtml($input_string) {
        if (function_exists('tidy_get_release')) {
            $config = array(
                'uppercase-attributes' => true,
                'wrap'                 => 800
            );
            $tidy   = new \tidy();
            $tidy->parseString($input_string, $config, 'utf8');
            $tidy->cleanRepair();
            $input_string = tidy_get_output($tidy);
        }
        return $input_string;
    }

    /**
     * 随机生成字符串
     * @param int $length
     * @param string $str_pol
     * @return string 生成的字符串
     */
    public static function getRandomStr($length = 16, $str_pol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz') {
        $str = '';
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

    /**
     * 去除html标签
     * @param string $str
     * @return mixed
     */
    public static function stripTags($str) {
        //去除html字符
        $str = strip_tags(self::htmlFormat($str));
        //js中html字符无法转换
        $str = str_replace(
            ['&#160;', '&#60;', '&#62;', '&#38;', '&#34;', '&#39;', '&#162;', '&#163;', '&#165;', '&#8364;', '&#167;', '&#169;', '&#174;', '&#8482;', '&#215;', '&#247;'],
            [' ', '<', '>', '&', '"', "'", '￠', '£', '¥', '€', '§', '©', '®', '™', '×', '÷'],
            $str
        );
        //去除 &nbsp;
        return str_replace('&nbsp;', ' ', $str);
    }

    /**
     * 字符串截取，支持中文和其他编码
     * @param string $str 待截取的字符串
     * @param int $start 开始位置
     * @param int $length 截取长度
     * @param bool $suffix 截断显示字符
     * @param string $charset 编码格式
     * @return string
     */
    public static function subStringForView($str, $start = 0, $length = 10, $suffix = false, $charset = "utf-8") {
        $str = self::stripTags($str);
        //获取字符串长度
        $str_len = self::getLength($str);
        if ($str_len <= $length) {
            return $str;
        }
        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);
            if (false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8']  = '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/';
            $re['gb2312'] = '/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/';
            $re['gbk']    = '/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/';
            $re['big5']   = '/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/';
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice . '…' : $slice;
    }

    /**
     * 获取唯一id
     * @return string
     */
    public static function getUniqueId() {
        $uuid                  = array(
            'time_low'      => 0,
            'time_mid'      => 0,
            'time_hi'       => 0,
            'clock_seq_hi'  => 0,
            'clock_seq_low' => 0,
            'node'          => array()
        );
        $uuid['time_low']      = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
        $uuid['time_mid']      = mt_rand(0, 0xffff);
        $uuid['time_hi']       = (4 << 12) | (mt_rand(0, 0x1000));
        $uuid['clock_seq_hi']  = (1 << 7) | (mt_rand(0, 128));
        $uuid['clock_seq_low'] = mt_rand(0, 255);
        for ($i = 0; $i < 6; $i++) {
            $uuid['node'][$i] = mt_rand(0, 255);
        }
        $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
            $uuid['time_low'],
            $uuid['time_mid'],
            $uuid['time_hi'],
            $uuid['clock_seq_hi'],
            $uuid['clock_seq_low'],
            $uuid['node'][0],
            $uuid['node'][1],
            $uuid['node'][2],
            $uuid['node'][3],
            $uuid['node'][4],
            $uuid['node'][5]
        );
        return md5($uuid);
    }

    /**
     * 订单编码，YmdHis.rand(10000000, 99999999).CC
     * @return string
     */
    public static function getOrderNumber() {
        $order_id_main = date('YmdHis') . rand(10000000, 99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1));
        }
        //添加校验码
        return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 格式化url字符串
     * @param string $str
     * @return mixed|string
     */
    public static function urlEncode($str) {
        if ($str === true) {
            $str = 'true';
        } else if ($str === false) {
            $str = 'false';
        }
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /**
     * 去除html多次转码问题
     * @param string $html
     * @return mixed
     */
    public static function stripAmp($html) {
        //无限循环去除&amp;多次格式化问题
        while (strpos($html, '&amp;') !== false && strpos($html, '&lt;') === false) {
            $html = str_replace('&amp;', '&', $html);
        }
        //特殊字符处理
        $html = str_replace(
            ['&#160;', '&#60;', '&#62;', '&#38;', '&#34;', '&#39;', '&#162;', '&#163;', '&#165;', '&#8364;', '&#167;', '&#169;', '&#174;', '&#8482;', '&#215;', '&#247;'],
            [' ', '<', '>', '&', '"', "'", '￠', '£', '¥', '€', '§', '©', '®', '™', '×', '÷'],
            $html
        );
        return $html;
    }

    /**
     * html格式化
     * @param string $html
     * @return mixed
     */
    public static function htmlFormat($html) {
        $html = self::stripAmp($html);
        $html = htmlspecialchars_decode($html);
        //去掉回车和换行
        return str_replace(array("\r", "\n"), '<br/>', $html);
    }

    /**
     * 任何格式转为string
     * @return mixed|string
     */
    public static function toString() {
        $args = func_get_args();
        $str  = '';
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
                if ($str === '') {
                    $str = print_r($each, true);
                } else {
                    $str .= ', ' . print_r($each, true);
                }
            } else if (is_bool($each)) {
                if ($str === '') {
                    $str = $each ? 'TRUE' : 'FALSE';
                } else {
                    $str .= ', ' . ($each ? 'TRUE' : 'FALSE');
                }
            } else if (is_null($each)) {
                if ($str === '') {
                    $str = 'null';
                } else {
                    $str .= ', null';
                }
            } else {
                if ($str === '') {
                    $str = $each;
                } else {
                    $str .= ', ' . $each;
                }
            }
        }
        return $str;
    }

    /**
     * 格式化json
     * @param $json
     * @param bool $html
     * @return string
     */
    public static function jsonFormat($json, $html = false) {
        $str = json_decode($json, true);
        if (empty($str)) {
            return '';
        }
        $str = json_encode($str, JSON_PRETTY_PRINT);
        //美化
        if ($html) {
            $str_array = explode("\n", $str);
            $search    = [];
            $replace   = [];
            foreach ($str_array as $each) {
                $item = self::getBetween($each, '"', ':', false);
                if (!empty($item)) {
                    $item = '"' . $item;
                    if (!in_array($item, $search)) {
                        $search[]  = $item;
                        $replace[] = '<span style="color: blue;">' . $item . '</span>';
                    }
                }
            }
            $str = str_replace(' ', '&nbsp;', $str);
            $str = nl2br($str);
            $str = str_replace('{', '<span style="color: #FF3300;">{</span>', $str);
            $str = str_replace('}', '<span style="color: #FF3300;">}</span>', $str);
            $str = str_replace('[', '<span style="color: #3ab54a;">[</span>', $str);
            $str = str_replace(']', '<span style="color: #3ab54a;">]</span>', $str);
            $str = str_replace(',', '<span style="color: blue;">,</span>', $str);
            $str = str_replace($search, $replace, $str);
            $str = str_replace(['\/'], ['<span style="color: #3ab54a;">/</span>'], $str);
        } else {
            $str = str_replace(['\/'], ['/'], $str);
        }
        //中文处理
        $str = preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            function ($matches) {
                return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
            },
            $str);
        return $str;
    }

    /**
     * 拼接字符串
     * @param string $source_str 原字符串
     * @param int $append_char 拼接字符
     * @param int $total_length 拼接至X长度
     * @param bool $at_before 拼接位置
     * @return string
     */
    public static function append($source_str, $append_char = 0, $total_length = 2, $at_before = true) {
        if (self::getLength($append_char) > 1) {
            return $source_str;
        }
        $source_str_length = self::getLength($source_str);
        if ($source_str_length >= $total_length) {
            return $source_str;
        }
        $join_str = '';
        for ($i = 0; $i < $total_length - $source_str_length; $i++) {
            $join_str .= $append_char;
        }
        if ($at_before) {
            $source_str = $join_str . $source_str;
        } else {
            $source_str .= $join_str;
        }
        return $source_str;
    }

    /**
     * 数字金额转换成中文大写金额
     * @param integer $num 金额
     * @param bool $is_simple 是否是中文简写
     * @param bool $mode 模式
     * @return mixed|string
     */
    public static function monenyFormatRmb($num, $is_simple = false, $mode = true) {
        if (!is_numeric($num)) {
            return '含有非数字非小数点字符！';
        }
        $char   = $is_simple ? ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九']
            : ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        $unit   = $is_simple ? ['', '十', '百', '千', '', '万', '亿', '兆']
            : ['', '拾', '佰', '仟', '', '萬', '億', '兆'];
        $retval = $mode ? '元' : '点';
        //小数部分
        if (strpos($num, '.')) {
            list($num, $dec) = explode('.', $num);
            $dec = strval(round($dec, 2));
            if ($mode) {
                if (strlen($dec) == 1) {
                    $retval .= sprintf('%s角', $char[$dec{0}]);
                } else {
                    $retval .= sprintf('%s角%s分', $char[$dec{0}], $char[$dec{1}]);
                }
            } else {
                for ($i = 0, $c = strlen($dec); $i < $c; $i++) {
                    $retval .= $char[$dec[$i]];
                }
            }
        }
        //整数部分
        $str = $mode ? strrev(intval($num)) : strrev($num);
        for ($i = 0, $c = strlen($str); $i < $c; $i++) {
            $out[$i] = $char[$str[$i]];
            if ($mode) {
                $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
                if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                    $out[$i] = '';
                }
                if ($i % 4 == 0) {
                    $out[$i] .= $unit[4 + floor($i / 4)];
                }
            }
        }
        $retval = join('', array_reverse($out)) . $retval;
        return $retval;
    }

}
