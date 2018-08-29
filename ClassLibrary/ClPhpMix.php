<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * QQ: 597481334
 * Email: skj198568@163.com
 * Date: 2016/1/25
 * Time: 10:47
 */

namespace ClassLibrary;

/**
 * php代码混淆
 * Class ClPhpMix
 * @package ClassLibrary
 */
class ClPhpMix {

    /**
     * 返回随机字符串
     * @return string
     */
    private static function RandAbc() {
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        return str_shuffle($str);
    }

    /**
     * 混淆
     * @param $file_absolute_url
     * @param string $new_file_absolute_url 如果为空，则替换原文件
     */
    public static function encode($file_absolute_url, $new_file_absolute_url = '') {
        $T_k1 = self::RandAbc();//随机密匙1
        $T_k2 = self::RandAbc();//随机密匙2
        $q1   = "O00O0O";
        $q2   = "O0O000";
        $q3   = "O0OO00";
        $q4   = "OO0O00";
        $q5   = "OO0000";
        $q6   = "O00OO0";
        $vstr = file_get_contents($file_absolute_url);//要加密的文件
        if (strpos($vstr, '$' . $q6 . '=urldecode(') !== false) {
            //已加密，忽略
            return;
        }
        $v1 = base64_encode($vstr);
        $c  = strtr($v1, $T_k1, $T_k2);//根据密匙替换对应字符。
        $c  = $T_k1 . $T_k2 . $c;
        $s  = '$' . $q6 . '=urldecode("%6E1%7A%62%2F%6D%615%5C%76%740%6928%2D%70%78%75%71%79%2A6%6C%72%6B%64%679%5F%65%68%63%73%77%6F4%2B%6637%6A");$' . $q1 . '=$' . $q6 . '{3}.$' . $q6 . '{6}.$' . $q6 . '{33}.$' . $q6 . '{30};$' . $q3 . '=$' . $q6 . '{33}.$' . $q6 . '{10}.$' . $q6 . '{24}.$' . $q6 . '{10}.$' . $q6 . '{24};$' . $q4 . '=$' . $q3 . '{0}.$' . $q6 . '{18}.$' . $q6 . '{3}.$' . $q3 . '{0}.$' . $q3 . '{1}.$' . $q6 . '{24};$' . $q5 . '=$' . $q6 . '{7}.$' . $q6 . '{13};$' . $q1 . '.=$' . $q6 . '{22}.$' . $q6 . '{36}.$' . $q6 . '{29}.$' . $q6 . '{26}.$' . $q6 . '{30}.$' . $q6 . '{32}.$' . $q6 . '{35}.$' . $q6 . '{26}.$' . $q6 . '{30};eval($' . $q1 . '("' . base64_encode('$' . $q2 . '="' . $c . '";eval(\'?>\'.$' . $q1 . '($' . $q3 . '($' . $q4 . '($' . $q2 . ',$' . $q5 . '*2),$' . $q4 . '($' . $q2 . ',$' . $q5 . ',$' . $q5 . '),$' . $q4 . '($' . $q2 . ',0,$' . $q5 . '))));') . '"));';
        $s  = '<?php
 ' . $s;
        //生成 加密后的PHP文件
        if (!empty($new_file_absolute_url)) {
            //创建文件
            ClFile::dirCreate($new_file_absolute_url);
            $fpp1 = fopen($new_file_absolute_url, 'w+');
        } else {
            $fpp1 = fopen($file_absolute_url, 'w+');
        }
        fwrite($fpp1, $s) or die('写文件错误');
    }

}