<?php
/**
 * Created by PhpStorm.
 * User: SongKeJing
 * Date: 2015/9/30
 * Time: 13:17
 */

namespace ClassLibrary\WX;


use ClassLibrary\ClString;
use Think\Exception;

class PrpCrypt
{

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public static function encrypt($key, $text, $corp_id)
    {
        $key = base64_decode($key . "=");
        try {
            //获得16位随机字符串，填充到明文之前
            $random = ClString::getRandomStr(16);
            $text = $random . pack("N", strlen($text)) . $text . $corp_id;
            // 网络字节序
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($key, 0, 16);
            //使用自定义的填充方式对明文进行补位填充
            $text = PKCS7Encoder::encode($text);
            mcrypt_generic_init($module, $key, $iv);
            //加密
            $encrypted = mcrypt_generic($module, $text);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
            //print(base64_encode($encrypted));
            //使用BASE64对加密后的字符串进行编码
            return array(ErrorCode::$OK, base64_encode($encrypted));
        } catch (Exception $e) {
            print $e;
            return array(ErrorCode::$EncryptAESError, null);
        }
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public static function decrypt($key, $encrypted, $corp_id)
    {
        $key = base64_decode($key . "=");
        try {
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($key, 0, 16);
            mcrypt_generic_init($module, $key, $iv);
            //解密
            $decrypted = mdecrypt_generic($module, $ciphertext_dec);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(ErrorCode::$DecryptAESError, null);
        }
        try {
            //去除补位字符
            $result = PKCS7Encoder::decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_corpid = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        if ($from_corpid != $corp_id)
            return array(ErrorCode::$ValidateCorpidError, null);
        return array(0, $xml_content);
    }
}
