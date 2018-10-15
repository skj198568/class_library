<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:35
 */

namespace ClassLibrary;


use Think\Verify;

/**
 * Class ClVerify 校验类库
 * @package Common\Common
 */
class ClVerify {

    /**
     * model实例
     * @var null
     */
    private static $model_instance = null;

    /**
     * 是否是邮件
     * @param $str
     * @return bool
     */
    public static function isEmail($str) {
        return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $str) === 1;
    }

    /**
     * 是否是url
     * @param $str
     * @return bool
     */
    public static function isUrl($str) {
        return preg_match('/^(http(s?))?(:)?\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/', $str) === 1;
    }

    /**
     * 是否是金钱
     * @param $str
     * @return bool
     */
    public static function isCurrency($str) {
        return preg_match('/^\d+(\.\d+)?$/', $str) === 1;
    }

    /**
     * 是否是邮编
     * @param $str
     * @return bool
     */
    public static function isPostcode($str) {
        return preg_match('/^\d{6}$/', $str) === 1;
    }

    /**
     * 是否是手机
     * @param $mobile
     * @return bool
     */
    public static function isMobile($mobile) {
        return preg_match('/^1(3|4|5|6|7|8|9)\d{9}$/', $mobile) === 1;
    }

    /**
     * 是否是固话
     * @param $str
     * @return bool
     */
    public static function isTel($str) {
        return preg_match('/\d{3}(-?)\d{8}|\d{4}(-?)\d{7}$/', $str) === 1;
    }

    /**
     * 是否是身份证号
     * @param $id_card
     * @return bool
     */
    public static function isIdCard($id_card) {
        $id_card = self::isIdCardTo18Card($id_card);
        if (strlen($id_card) != 18) {
            return false;
        }
        $id_cardBase = substr($id_card, 0, 17);
        return (self::isIdCardGetVerifyNum($id_cardBase) == strtoupper(substr($id_card, 17, 1)));
    }

    /**
     * 格式化15位身份证号码为18位
     * @param $id_card
     * @return bool|string
     */
    private static function isIdCardTo18Card($id_card) {
        $id_card = trim($id_card);
        if (strlen($id_card) == 18) {
            return $id_card;
        }
        if (strlen($id_card) != 15) {
            return false;
        }
        // 如果身份证顺序码是996 997 998 999,这些是为百岁以上老人的特殊编码
        if (array_search(substr($id_card, 12, 3), array('996', '997', '998', '999')) !== false) {
            $id_card = substr($id_card, 0, 6) . '18' . substr($id_card, 6, 9);
        } else {
            $id_card = substr($id_card, 0, 6) . '19' . substr($id_card, 6, 9);
        }
        $id_card = $id_card . self::isIdCardGetVerifyNum($id_card);
        return $id_card;
    }

    /**
     * 计算身份证校验码,根据国家标准gb 11643-1999
     * @param $id_cardBase
     * @return bool|mixed
     */
    private static function isIdCardGetVerifyNum($id_cardBase) {
        if (strlen($id_cardBase) != 17) {
            return false;
        }
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        // 校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum           = 0;
        for ($i = 0; $i < strlen($id_cardBase); $i++) {
            $checksum += substr($id_cardBase, $i, 1) * $factor[$i];
        }
        $mod           = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

    /**
     * 是否是正确的密码格式：以字母开头，长度在6~18之间，只能包含字符、数字和下划线
     * @param $str
     * @param int $length_min 最小长度
     * @param int $length_max 最大长度
     * @return bool
     */
    public static function isPassword($str, $length_min = 6, $length_max = 18) {
        return preg_match('/^[A-Za-z0-9\-\_]{' . $length_min . ',' . $length_max . '}$/', $str) === 1;
    }

    /**
     * 判断字符串是否是json数据
     * @param $str
     * @return bool
     */
    public static function isJson($str) {
        $str = json_decode($str, true);
        return is_array($str);
    }

    /**
     * 是否是qq
     * @param $str
     * @return int
     */
    public static function isQQ($str) {
        return preg_match('/^[1-9]\d{4,13}$/', $str);
    }

    /**
     * 是否是ip地址
     * @param string $str
     * @return bool|int
     */
    public static function isIp($str) {
        $v = trim($str);
        if (empty($v)) {
            return false;
        }
        if (self::isLocalIp($str)) {
            return true;
        }
        return preg_match('/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/', $v);
    }

    /**
     * 是否是局域网ip
     * @param string $ip
     * @return bool
     */
    public static function isLocalIp($ip = '') {
        if (empty($ip)) {
            $ip = request()->ip();
        }
        if (in_array(strtok($ip, '.'), ['0', '10', '127', '168', '172', '192'])) {
            return true;
        }
        return false;
    }

    /**
     * 固定长度中文
     * @param $str
     * @param $length1
     * @param $length2
     * @return int
     */
    public static function fixedLengthChinese($str, $length1, $length2) {
        return preg_match("/^([\x81-\xfe][\x40-\xfe]){" . $length1 . "," . $length2 . "}$/", $str);
    }

    /**
     * 固定长度数字
     * @param $str
     * @param $length1
     * @param $length2
     * @return int
     */
    public static function fixedLengthInt($str, $length1, $length2) {
        return preg_match("/^[0-9]{" . $length1 . "," . $length2 . "}$/i", $str);
    }

    const V_FILE_TYPE_JPEG = 'FFD8FFE0';
    const V_FILE_TYPE_PNG = '89504E47';
    const V_FILE_TYPE_GIF = '47494638';
    const V_FILE_TYPE_TIF = '49492A00';
    const V_FILE_TYPE_BMP = '424DC001';
    const V_FILE_TYPE_DWG = '41433130';
    const V_FILE_TYPE_PSD = '38425053';
    const V_FILE_TYPE_RTF = '7B5C727466';
    const V_FILE_TYPE_XML = '3C3F786D6C';
    const V_FILE_TYPE_HTML = '68746D6C3E';
    const V_FILE_TYPE_EML = '44656C69766572792D646174653A';
    const V_FILE_TYPE_DBX = 'CFAD12FEC5FD746F';
    const V_FILE_TYPE_PST = '2142444E';
    const V_FILE_TYPE_DOC = 'D0CF11E0';
    const V_FILE_TYPE_XLS = 'D0CF11E0';
    const V_FILE_TYPE_MDB = '5374616E64617264204A';
    const V_FILE_TYPE_WPD = 'FF575043';
    const V_FILE_TYPE_PDF = '255044462D312E';
    const V_FILE_TYPE_QDF = 'AC9EBD8F';
    const V_FILE_TYPE_PWL = 'E3828596';
    const V_FILE_TYPE_ZIP = '504B0304';
    const V_FILE_TYPE_RAR = '52617221';
    const V_FILE_TYPE_WAV = '57415645';
    const V_FILE_TYPE_AVI = '41564920';
    const V_FILE_TYPE_RAM = '2E7261FD';
    const V_FILE_TYPE_RM = '2E524D46';
    const V_FILE_TYPE_MPG = '000001BA';
    const V_FILE_TYPE_MOV = '6D6F6F76';
    const V_FILE_TYPE_ASF = '3026B2758E66CF11';
    const V_FILE_TYPE_MID = '4D546864';
    const V_FILE_TYPE_GZ = '4D546864';
    const V_FILE_TYPE_BZ = '425A';

    /**
     * 判断文件类型
     * @param $file_absolute_url
     * @param $v_file_type
     * @return bool
     */
    public static function isFileType($file_absolute_url, $v_file_type) {
        $length = strlen($v_file_type);
        $file   = fopen($file_absolute_url, "rb");
        $bin    = fread($file, $length);
        fclose($file);
        $fileHead = @unpack("H{$length}", $bin);
        // 判断文件头
        return strtolower($v_file_type) == $fileHead[1] ? true : false;
    }

    /**
     * 大写字母
     * @param $str
     * @return bool
     */
    public static function isAlphaCapital($str) {
        return preg_match('/^[A-Z]+$/', $str) === 1;
    }

    /**
     * 小写字母
     * @param $str
     * @return bool
     */
    public static function isAlphaLowercase($str) {
        return preg_match('/^[a-z]+$/', $str) === 1;
    }

    /**
     * 是否是字母
     * @param $str
     * @return bool
     */
    public static function isAlpha($str) {
        return preg_match('/^[A-Za-z]+$/', $str) === 1;
    }

    /**
     * 是否是字母，数字
     * @param $str
     * @return bool
     */
    public static function isAlphaNum($str) {
        return preg_match('/^[A-Za-z0-9]+$/', $str) === 1;
    }

    /**
     * 字母，数字，_，-
     * @param $str
     * @return bool
     */
    public static function isAlphaNumDash($str) {
        return preg_match('/^[A-Za-z0-9\-\_]+$/', $str) === 1;
    }

    /**
     * 中文
     * @param $str
     * @return bool
     */
    public static function isChinese($str) {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str) === 1;
    }

    /**
     * 中文，英文
     * @param $str
     * @return bool
     */
    public static function isChineseAlpha($str) {
        return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u', $str) === 1;
    }

    /**
     * 中文，英文，数字
     * @param $str
     * @return bool
     */
    public static function isChineseAlphaNum($str) {
        return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', $str) === 1;
    }

    /**
     * 中文，英文，数字，_，-
     * @param $str
     * @return bool
     */
    public static function isChineseAlphaNumDash($str) {
        return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u', $str) === 1;
    }

    /**
     * 是否是boolean
     * @param $str
     * @return bool
     */
    public static function isBoolean($str) {
        return in_array($str, [true, false, 0, 1, '0', '1'], true);
    }

    /**
     * 是否是时间
     * @param $str
     * @return bool
     */
    public static function isDate($str) {
        return false !== strtotime($str);
    }

    /**
     * 是否是域名
     * @param $str
     * @return bool
     */
    public static function isDomain($str) {
        return preg_match('/(http(s?):\/\/)?([a-zA-Z0-9]([a-zA-Z0-9\\-]{0,61}[a-zA-Z0-9])?\\.)+[a-zA-Z]{2,6}/u', $str) === 1;
    }

    /**
     * 是否包含中文
     * @param $str
     * @return bool
     */
    public static function hasChinese($str) {
        return preg_match('/[\x7f-\xff]/', $str) === 1;
    }

    /**
     * 是否包含html标签
     * @param $str
     * @return bool
     */
    public static function hasHtmlTag($str) {
        return ClString::stripTags($str) !== $str;
    }

}
