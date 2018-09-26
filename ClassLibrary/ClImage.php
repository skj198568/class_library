<?php
/**
 * Created by PhpStorm.
 * User: SongKeJing
 * Date: 2015/9/17
 * Time: 16:25
 */

namespace ClassLibrary;

use think\Image;

/**
 * 图片处理类
 * Class ClImg
 * @package ClassLibrary
 */
class ClImage {

    /**
     * 居中剪裁图片
     * @param string $img_url 图片绝对地址
     * @param string $cut_width 裁剪宽度
     * @param integer $cut_height 裁剪高度，默认自动计算
     * @param string $save_img_url 保存的图片绝对地址，如果为'',则覆盖掉原图片
     * @param bool $is_delete 当save_img_url不为空时，是否删掉原始img
     */
    public static function centerCut($img_url, $cut_width, $cut_height = 0, $save_img_url = '', $is_delete = false) {
        if (!is_file($img_url)) {
            if (is_file(DOCUMENT_ROOT_PATH . $img_url)) {
                $img_url = DOCUMENT_ROOT_PATH . $img_url;
            }
        }
        if (!is_file($img_url)) {
            log_info('文件不存在：' . $img_url);
            return;
        }
        $cut_width = intval($cut_width);
        if ($cut_height == 0) {
            $cut_height = self::getHeightByProportion($img_url, $cut_width);
        }
        $cut_height = intval($cut_height);
        if ($cut_width > ClImage::getWidth($img_url) || $cut_height > ClImage::getHeight($img_url)) {
            if (!empty($save_img_url)) {
                //不够裁剪的情况下，进行复制
                copy($img_url, $save_img_url);
                //删除原图
                if ($is_delete) {
                    unlink($img_url);
                }
            }
            return;
        }
        $image        = Image::open($img_url);
        $image_width  = $image->width();
        $image_height = $image->height();
        //居中裁剪图片
        if ($image_width / $image_height > $cut_width / $cut_height) {
            $image->crop(round($image_height / $cut_height * $cut_width), $image_height, round(($image_width - $image_height / $cut_height * $cut_width) / 2), 0);
        } else if ($image_width / $image_height < $cut_width / $cut_height) {
            $image->crop($image_width, round($image_width / $cut_width * $cut_height), 0, round(($image_height - $image_width / $cut_width * $cut_height) / 2));
        } else {
            //长宽比相同
            if ($image_width == $cut_width) {
                //如果长宽相等，则直接copy一份
                if (!empty($save_img_url)) {
                    copy($img_url, $save_img_url);
                    //删掉原图
                    if ($is_delete) {
                        unlink($img_url);
                    }
                }
            } else {
                if (empty($save_img_url)) {
                    $image->thumb($cut_width, $cut_height)->save($img_url);
                } else {
                    $image->thumb($cut_width, $cut_height)->save($save_img_url);
                    //删掉原图
                    if ($is_delete) {
                        unlink($img_url);
                    }
                }
            }
            return;
        }
        if (empty($save_img_url)) {
            $image->thumb($cut_width, $cut_height)->save($img_url);
        } else {
            $image->thumb($cut_width, $cut_height)->save($save_img_url);
            //删掉原图
            if ($is_delete) {
                unlink($img_url);
            }
        }
    }

    /**
     * 获取图片宽度
     * @param $img_absolute_url
     * @return mixed
     */
    public static function getWidth($img_absolute_url) {
        $info = getimagesize($img_absolute_url);
        return $info[0];
    }

    /**
     * 按比例获取图片高度
     * @param $img_absolute_url
     * @param $height
     * @return float
     */
    public static function getWidthByProportion($img_absolute_url, $height) {
        return intval(self::getWidth($img_absolute_url) / self::getHeight($img_absolute_url) * $height);
    }

    /**
     * 获取图片高度
     * @param $img_absolute_url
     * @return mixed
     */
    public static function getHeight($img_absolute_url) {
        $info = getimagesize($img_absolute_url);
        return $info[1];
    }

    /**
     * 按比例获取图片高度
     * @param $img_absolute_url
     * @param $width
     * @return float
     */
    public static function getHeightByProportion($img_absolute_url, $width) {
        return intval(self::getHeight($img_absolute_url) / self::getWidth($img_absolute_url) * $width);
    }

    /**
     * 生成二维码图片
     * @param $str 二维码内容
     * @param string $logo_absolute_file 二维码logo绝对路径
     * @param int $width 图片宽度，
     * @param int $margin 二维码margin
     * @param bool $cover 是否覆盖原图片
     * @return string 返回二维码图片地址
     */
    public static function qrCode($str, $logo_absolute_file = '', $width = 200, $margin = 1, $cover = false) {
        if (!empty($logo_absolute_file)) {
            if (!in_array(strtolower(ClFile::getSuffix($logo_absolute_file)), ['.png', '.jpg'])) {
                echo_info('logo_absolute_file support png or jpg');
                exit;
            }
        }
        include_once "phpqrcode/phpqrcode.php";
        $file_dir  = '/qr_code';
        $file_name = md5($str . $logo_absolute_file . $width . $margin) . '.png';
        //三级目录存储
        $file = $file_dir . '/' . date('Y/m/d') . '/' . $file_name;
        if ($cover == false && is_file(DOCUMENT_ROOT_PATH . $file)) {
            return $file;
        }
        //创建文件夹
        ClFile::dirCreate(DOCUMENT_ROOT_PATH . $file);
        \QRcode::png($str, DOCUMENT_ROOT_PATH . $file, QR_ECLEVEL_M, $width, $margin);
        if (!empty($logo_absolute_file)) {
            self::qrCodeAddLogo(DOCUMENT_ROOT_PATH . $file, $logo_absolute_file);
        }
        return $file;
    }

    /**
     * 给二维码图片添加logo
     * @param $qr_absolute_file
     * @param $logo_absolute_file
     * @return mixed
     */
    public static function qrCodeAddLogo($qr_absolute_file, $logo_absolute_file) {
        $qr        = imagecreatefromstring(file_get_contents($qr_absolute_file));
        $qr_width  = imagesx($qr);//二维码图片宽度
        $qr_height = imagesy($qr);//二维码图片高度
        //重新裁剪图片
        $temp_logo = DOCUMENT_ROOT_PATH . '/temp' . ClFile::getSuffix($logo_absolute_file);
        self::centerCut($logo_absolute_file, ceil($qr_width / 5), ceil($qr_width / 5), $temp_logo);
        //拼接图片
        $x = $y = ceil(($qr_width - $qr_width / 5) / 2);
        //转换为圆角图
        $temp_logo = self::radius($temp_logo);
        //合并图片
        return self::mergeImages($qr_absolute_file, $temp_logo, $x, $y);
    }

    /**
     * 保存base64图片
     * @param $base64_image_content
     * @param string $save_absolute_url
     * @return bool|string
     */
    public static function base64Decode($base64_image_content, $save_absolute_url = '') {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            if (empty($save_absolute_url)) {
                $save_absolute_url = DOCUMENT_ROOT_PATH . '/images/base64_images/' . time() . '.' . $type;
            }
            //创建文件夹
            ClFile::dirCreate($save_absolute_url);
            //写入文件
            file_put_contents($save_absolute_url, base64_decode(str_replace($result[1], '', $base64_image_content)));
            //返回文件地址
            return $save_absolute_url;
        } else {
            return false;
        }
    }

    /**
     * base64编码
     * @param $image_absolute_url
     * @return string 图片进行处理
     */
    public static function base64Encode($image_absolute_url) {
        //不存在的图片，不进行处理
        if (!is_file($image_absolute_url)) {
            return $image_absolute_url;
        }
        return 'data:' . ClFile::getMimeType($image_absolute_url) . ';base64,' . base64_encode(file_get_contents($image_absolute_url));
    }

    /**
     * 生成带图片背景的二维码
     * @param $content 二维码内容
     * @param $img_absolute_url 背景图片的绝对地址
     * @param int $img_width 生成的二维码宽度
     * @param string $save_img_absolute_url 保存的图片绝对地址
     * @param bool $is_cut 是否自动裁剪
     * @param bool $force 是否重新生成二维码
     * @return mixed|string 返回图片存储的地址
     */
    public static function qrCodeWithBackgroundImage($content, $img_absolute_url, $img_width = 93, $save_img_absolute_url = '', $is_cut = false, $force = false) {
        if (!is_file($img_absolute_url)) {
            log_info(sprintf('背景图片"%s"不存在', $img_absolute_url));
            return false;
        }
        if (!ClVerify::isFileType($img_absolute_url, ClVerify::V_FILE_TYPE_PNG) && !ClVerify::isFileType($img_absolute_url, ClVerify::V_FILE_TYPE_JPEG)) {
            log_info('仅支持png或者是jpg类型格式的图片');
            return false;
        }
        if (self::getWidth($img_absolute_url) < $img_width + 35) {
            log_info(sprintf('图片宽度不够，至少需要%s像素', $img_width + 35));
            return false;
        }
        if (self::getHeight($img_absolute_url) < $img_width + 35) {
            log_info(sprintf('图片高度不够，至少需要%s像素', $img_width + 35));
            return false;
        }
        //获取二维码可能生成的最小值
        $img_width = min(self::getHeight($img_absolute_url), self::getWidth($img_absolute_url), $img_width);
        //先生成二维码
        $qr_url          = self::qrCode($content, $img_width, '', $is_cut, $force);
        $qr_absolute_url = DOCUMENT_ROOT_PATH . $qr_url;
        $qr_height       = self::getHeight($qr_absolute_url);
        $qr_width        = self::getWidth($qr_absolute_url);
        $im              = imagecreatefrompng($qr_absolute_url);
        $new_im          = imagecreatetruecolor($qr_width, $qr_height);
        //设置背景颜色
        $bg_color = imagecolorallocate($new_im, 255, 255, 255);
        imagefill($new_im, 0, 0, $bg_color); //填充
        //裁剪原图
        $img_name = ClFile::getName($img_absolute_url);
        $temp_img = $img_name . '_temp.png';
        //替换文件名
        $temp_img = str_replace(ClFile::getName($img_absolute_url, true), $temp_img, $img_absolute_url);
        self::centerCut($img_absolute_url, $qr_width, $qr_height, $temp_img);
        $photo_im = '';
        if (ClVerify::isFileType($temp_img, ClVerify::V_FILE_TYPE_PNG)) {
            $photo_im = imagecreatefrompng($temp_img);
        } else if (ClVerify::isFileType($temp_img, ClVerify::V_FILE_TYPE_JPEG)) {
            $photo_im = imagecreatefromjpeg($temp_img);
        } else {
            exit('只支持png或者是jpg类型格式的图片');
        }
        $rgb = '';
        //颜色分割值
        $invite_color_black = 50;
        $invite_color_white = 100;
        $rgb_list           = [];
        $r                  = 0;
        $g                  = 0;
        $b                  = 0;
        for ($x_index = 0; $x_index < $qr_width; $x_index++) {
            for ($y_index = 0; $y_index < $qr_height; $y_index++) {
                $rgb = imagecolorat($im, $x_index, $y_index);
                if ($rgb != 0) {
                    $rgb = imagecolorat($photo_im, $x_index, $y_index);
                    $r   = ($rgb >> 16) & 0xFF;
                    $g   = ($rgb >> 8) & 0xFF;
                    $b   = $rgb & 0xFF;
                    if ($r > $invite_color_black) {
                        $r = $invite_color_black;
                    }
                    if ($g > $invite_color_black) {
                        $g = $invite_color_black;
                    }
                    if ($b > $invite_color_black) {
                        $b = $invite_color_black;
                    }
                    //获取像素点颜色
                    $rgb = imagecolorallocate($new_im, $r, $g, $b);
                    //设置像素点
                    imagesetpixel($new_im, $x_index, $y_index, $rgb);
                } else {
                    //外边框区域，获取对角线像素点
                    if ($x_index % 2 == 1 || $y_index % 2 == 0) {
                        $rgb = imagecolorat($photo_im, $x_index, $y_index);
                        $r   = ($rgb >> 16) & 0xFF;
                        $g   = ($rgb >> 8) & 0xFF;
                        $b   = $rgb & 0xFF;
                        if ($r < $invite_color_white) {
                            $r = $invite_color_white;
                        }
                        if ($g < $invite_color_white) {
                            $g = $invite_color_white;
                        }
                        if ($b < $invite_color_white) {
                            $b = $invite_color_white;
                        }
                        //获取像素点颜色
                        $rgb = imagecolorallocate($new_im, $r, $g, $b);
                        //设置像素点
                        imagesetpixel($new_im, $x_index, $y_index, $rgb);
                    }
                }
            }
        }
        //保存为图片
        if (empty($save_img_absolute_url)) {
            $img_name      = ClFile::getName($img_absolute_url);
            $save_img_name = $img_name . '_qr_' . $img_width . '.png';
            //替换文件名
            $save_img_absolute_url = str_replace(ClFile::getName($img_absolute_url, true), $save_img_name, $img_absolute_url);
        }
        imagepng($new_im, $save_img_absolute_url);
        //销毁对象
        imagedestroy($im);
        imagedestroy($new_im);
        imagedestroy($photo_im);
        //是否剪裁
        if ($is_cut && self::getWidth($save_img_absolute_url) != $img_width) {
            self::centerCut($save_img_absolute_url, $img_width, $img_width);
        }
        return $save_img_absolute_url;
    }

    /**
     * 转换图片的dpi
     * @param $image_absolute_file
     * @param string $saved_image_absolute_file
     * @param int $dpi
     * @return int
     */
    public static function convertDpi($image_absolute_file, $saved_image_absolute_file = '', $dpi = 300) {
        // ob_start();
        // $im = imagecreatefrompng($filename);
        // imagepng($im);
        // $file = ob_get_contents();
        // ob_end_clean();
        $img_content = file_get_contents($image_absolute_file);
        //数据块长度为9
        $len = pack("N", 9);
        //数据块类型标志为pHYs
        $sign = pack("A*", "pHYs");
        //X方向和Y方向的分辨率均为300DPI（1像素/英寸=39.37像素/米），单位为米（0为未知，1为米）
        $data = pack("NNC", $dpi * 39.37, $dpi * 39.37, 0x01);
        //CRC检验码由数据块符号和数据域计算得到
        $checksum = pack("N", crc32($sign . $data));
        $phys     = $len . $sign . $data . $checksum;
        $pos      = strpos($img_content, "pHYs");
        log_info('$pos:', $pos);
        if ($pos > 0) {
            //修改pHYs数据块
            $img_content = substr_replace($img_content, $phys, $pos - 4, 21);
        } else {
            //IHDR结束位置（PNG头固定长度为8，IHDR固定长度为25）
            $pos = 33;
            //将pHYs数据块插入到IHDR之后
            $img_content = substr_replace($img_content, $phys, $pos, 0);
        }
        //重新写入文件
        if (!empty($saved_image_absolute_file)) {
            //创建文件夹
            ClFile::dirCreate($saved_image_absolute_file);
        }
        file_put_contents(empty($saved_image_absolute_file) ? $image_absolute_file : $saved_image_absolute_file, $img_content);
    }

    public static function convertToPrint($filename) {
// ob_start();
// $im = imagecreatefrompng($filename);
// imagepng($im);
// $file = ob_get_contents();
// ob_end_clean();
        $file = file_get_contents($filename);

//数据块长度为9
        $len = pack("N", 9);
//数据块类型标志为pHYs
        $sign = pack("A*", "pHYs");
//X方向和Y方向的分辨率均为300DPI（1像素/英寸=39.37像素/米），单位为米（0为未知，1为米）
        $data = pack("NNC", 300 * 39.37, 300 * 39.37, 0x01);
//CRC检验码由数据块符号和数据域计算得到
        $checksum = pack("N", crc32($sign . $data));
        $phys     = $len . $sign . $data . $checksum;

        $pos = strpos($file, "pHYs");
        if ($pos > 0) {
            //修改pHYs数据块
            $file = substr_replace($file, $phys, $pos - 4, 21);
        } else {
            //IHDR结束位置（PNG头固定长度为8，IHDR固定长度为25）
            $pos = 33;
            //将pHYs数据块插入到IHDR之后
            $file = substr_replace($file, $phys, $pos, 0);
        }

        header("Content-type: image/png");
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        echo $file;

    }

    public static function read_png_dpi($source) {
        $fh = fopen($source, 'rb');
        if (!$fh)
            return false;

        $dpi = false;

        $buf = array();

        $x     = 0;
        $y     = 0;
        $units = 0;

        while (!feof($fh)) {
            array_push($buf, ord(fread($fh, 1)));
            if (count($buf) > 13)
                array_shift($buf);
            if (count($buf) < 13)
                continue;
            if ($buf[0] == ord('p') &&
                $buf[1] == ord('H') &&
                $buf[2] == ord('Y') &&
                $buf[3] == ord('s')
            ) {
                $x     = ($buf[4] << 24) + ($buf[5] << 16) + ($buf[6] << 8) + $buf[7];
                $y     = ($buf[8] << 24) + ($buf[9] << 16) + ($buf[10] << 8) + $buf[11];
                $units = $buf[12];
                break;
            }
        }

        fclose($fh);

        if ($x == $y)
            $dpi = $x;

        if ($dpi != false && $units == 1) //meters
            $dpi = round($dpi * 0.0254);

        return $dpi;
    }

    const V_BARCODE_TYPE_CODE39 = 'code39';
    const V_BARCODE_TYPE_CODE128 = 'code128';
    const V_BARCODE_TYPE_CODE128A = 'code128a';
    const V_BARCODE_TYPE_CODE25 = 'code25';
    const V_BARCODE_ORIENTATION_HORIZONTAL = 'horizontal';
    const V_BARCODE_ORIENTATION_VERTICAL = 'vertical';

    /**
     * 条形码
     * @param string $text 条形码内容
     * @param int $bar_height_or_width 条形码宽度或高度
     * @param string $code_type
     * @param int $size_factor
     * @return string
     */
    public static function barcode($text = "0", $bar_height_or_width = 60, $code_type = "code128", $size_factor = 1) {
        $file_path = '/static/images/barcode/' . $code_type . '/' . $bar_height_or_width . '/' . $size_factor . '/' . $text . '.png';
        if (is_file(DOCUMENT_ROOT_PATH . $file_path)) {
            return $file_path;
        }
        $code_string = "";
        // Translate the $text into barcode the correct $code_type
        if (in_array(strtolower($code_type), array("code128", "code128b"))) {
            $chk_sum = 104;
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array  = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "\`" => "111422", "a" => "121124", "b" => "121421", "c" => "141122", "d" => "141221", "e" => "112214", "f" => "112412", "g" => "122114", "h" => "122411", "i" => "142112", "j" => "142211", "k" => "241211", "l" => "221114", "m" => "413111", "n" => "241112", "o" => "134111", "p" => "111242", "q" => "121142", "r" => "121241", "s" => "114212", "t" => "124112", "u" => "124211", "v" => "411212", "w" => "421112", "x" => "421211", "y" => "212141", "z" => "214121", "{" => "412121", "|" => "111143", "}" => "111341", "~" => "131141", "DEL" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "FNC 4" => "114131", "CODE A" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys   = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey   = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chk_sum     = ($chk_sum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chk_sum - (intval($chk_sum / 103) * 103))]];

            $code_string = "211214" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code128a") {
            $chk_sum = 103;
            $text    = strtoupper($text); // Code 128A doesn't support lower case
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array  = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys   = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey   = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chk_sum     = ($chk_sum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chk_sum - (intval($chk_sum / 103) * 103))]];

            $code_string = "211412" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code39") {
            $code_array = array("0" => "111221211", "1" => "211211112", "2" => "112211112", "3" => "212211111", "4" => "111221112", "5" => "211221111", "6" => "112221111", "7" => "111211212", "8" => "211211211", "9" => "112211211", "A" => "211112112", "B" => "112112112", "C" => "212112111", "D" => "111122112", "E" => "211122111", "F" => "112122111", "G" => "111112212", "H" => "211112211", "I" => "112112211", "J" => "111122211", "K" => "211111122", "L" => "112111122", "M" => "212111121", "N" => "111121122", "O" => "211121121", "P" => "112121121", "Q" => "111111222", "R" => "211111221", "S" => "112111221", "T" => "111121221", "U" => "221111112", "V" => "122111112", "W" => "222111111", "X" => "121121112", "Y" => "221121111", "Z" => "122121111", "-" => "121111212", "." => "221111211", " " => "122111211", "$" => "121212111", "/" => "121211121", "+" => "121112121", "%" => "111212121", "*" => "121121211");

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ($X = 1; $X <= strlen($upper_text); $X++) {
                $code_string .= $code_array[substr($upper_text, ($X - 1), 1)] . "1";
            }

            $code_string = "1211212111" . $code_string . "121121211";
        } elseif (strtolower($code_type) == "code25") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
            $code_array2 = array("3-1-1-1-3", "1-3-1-1-3", "3-3-1-1-1", "1-1-3-1-3", "3-1-3-1-1", "1-3-3-1-1", "1-1-1-3-3", "3-1-1-3-1", "1-3-1-3-1", "1-1-3-3-1");

            for ($X = 1; $X <= strlen($text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($text, ($X - 1), 1) == $code_array1[$Y])
                        $temp[$X] = $code_array2[$Y];
                }
            }

            for ($X = 1; $X <= strlen($text); $X += 2) {
                if (isset($temp[$X]) && isset($temp[($X + 1)])) {
                    $temp1 = explode("-", $temp[$X]);
                    $temp2 = explode("-", $temp[($X + 1)]);
                    for ($Y = 0; $Y < count($temp1); $Y++)
                        $code_string .= $temp1[$Y] . $temp2[$Y];
                }
            }

            $code_string = "1111" . $code_string . "311";
        } elseif (strtolower($code_type) == "codabar") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "$", ":", "/", ".", "+", "A", "B", "C", "D");
            $code_array2 = array("1111221", "1112112", "2211111", "1121121", "2111121", "1211112", "1211211", "1221111", "2112111", "1111122", "1112211", "1122111", "2111212", "2121112", "2121211", "1121212", "1122121", "1212112", "1112122", "1112221");

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ($X = 1; $X <= strlen($upper_text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($upper_text, ($X - 1), 1) == $code_array1[$Y])
                        $code_string .= $code_array2[$Y] . "1";
                }
            }
            $code_string = "11221211" . $code_string . "1122121";
        }

        // Pad the edges of the barcode
        $code_length = 20;
        for ($i = 1; $i <= strlen($code_string); $i++) {
            $code_length = $code_length + (integer)(substr($code_string, ($i - 1), 1));
        }
        $text_height = 30;
        $img_width   = $code_length * $size_factor;
        $img_height  = $bar_height_or_width - $text_height;
        $image       = imagecreate($img_width, $bar_height_or_width);
        $black       = imagecolorallocate($image, 0, 0, 0);
        $white       = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        $font = 5;
        imagestring($image, $font, ceil(($img_width - imagefontwidth($font) * strlen($text)) / 2), $img_height + 5, $text, $black);

        $location = 10;
        for ($position = 1; $position <= strlen($code_string); $position++) {
            $cur_size = $location + (substr($code_string, ($position - 1), 1));
            imagefilledrectangle($image, $location * $size_factor, 0, $cur_size * $size_factor, $img_height, ($position % 2 == 0 ? $white : $black));
            $location = $cur_size;
        }
        ClFile::dirCreate(DOCUMENT_ROOT_PATH . $file_path);
        imagepng($image, DOCUMENT_ROOT_PATH . $file_path);
        imagedestroy($image);
        return $file_path;
    }

    /**
     * 合并图片
     * @param string $source_absolute_img 源图片绝对地址
     * @param string $with_absolute_img 拼接的图片地址
     * @param int $x 拼接图片相对于源文件偏移量
     * @param int $y 拼接图片相对于源文件偏移量
     * @param string $save_absolute_file 另存为
     * @param int $padding_top 生成新图的padding
     * @param int $padding_right 生成新图的padding
     * @param int $padding_bottom 生成新图的padding
     * @param int $padding_left 生成新图的padding
     * @return string
     */
    public static function mergeImages($source_absolute_img, $with_absolute_img, $x = 0, $y = 0, $save_absolute_file = '', $padding_top = 0, $padding_right = 0, $padding_bottom = 0, $padding_left = 0) {
        if (empty($save_absolute_file)) {
            $save_absolute_file = $source_absolute_img;
        }
        $source = imagecreatefromstring(file_get_contents($source_absolute_img));
        imagesavealpha($source, true);
        $source_width    = imagesx($source);//图片宽度
        $source_height   = imagesy($source);//图片高度
        $with_img        = imagecreatefromstring(file_get_contents($with_absolute_img));
        $with_img_width  = imagesx($with_img);//图片宽度
        $with_img_height = imagesy($with_img);//图片高度
        $new_img_width   = $source_width + $padding_left + $padding_right;
        if ($x + $with_img_width > $source_width) {
            $new_img_width += $x + $with_img_width - $source_width;
        }
        $new_img_height = $source_height + $padding_top + $padding_bottom;
        if ($y + $with_img_height > $source_height) {
            $new_img_height += $y + $with_img_height - $source_height;
        }
        $new_img = imagecreatetruecolor($new_img_width, $new_img_height);
        //设置背景颜色
        $bg_color = imagecolorallocate($new_img, 255, 255, 255);
        imagefill($new_img, 0, 0, $bg_color); //填充
        //重新组合$source图片
        imagecopyresampled($new_img, $source, $padding_left, $padding_top, 0, 0, $source_width, $source_width, $source_width, $source_width);
        imagepng($new_img, $save_absolute_file);
        //重新组合with图片
        imagecopyresampled($new_img, $with_img, $x + $padding_left, $y + $padding_top, 0, 0, $with_img_width, $with_img_height, $with_img_width, $with_img_height);
        //保存图片
        imagepng($new_img, $save_absolute_file);
        return $save_absolute_file;
    }

    /**
     * 字符串创建图片
     * @param $string
     * @param string $save_absolute_url
     * @param int $width 0/按字符串长度自动生成
     * @param int $height 0/按字符串长度自动生成
     * @param int $x -1/自动居中
     * @param int $y -1/自动居中
     * @return mixed
     */
    public static function createWithString($string, $save_absolute_url = '', $width = 0, $height = 0, $x = -1, $y = -1) {
        $font = 10;
        if ($width == 0) {
            $width = imagefontwidth($font) * strlen($string);
        }
        if ($height == 0) {
            $height = imagefontheight($font);
        }
        if ($x == -1) {
            $x = ceil(($width - imagefontwidth($font) * strlen($string)) / 2);
        }
        if ($y == -1) {
            $y = ceil(($height - imagefontheight($font)) / 2);
        }
        if (empty($save_absolute_url)) {
            $save_absolute_url = DOCUMENT_ROOT_PATH . '/static/images/string/' . ClString::toCrc32($string) . $width . '_' . $height . '_' . $x . '_' . $y . '.png';
            if (is_file($save_absolute_url)) {
                return str_replace(DOCUMENT_ROOT_PATH, '', $save_absolute_url);
            }
        }
        ClFile::dirCreate($save_absolute_url);
        //创建背景图
        $im = ImageCreateTrueColor($width, $height);
        //分配颜色
        $white = ImageColorAllocate($im, 255, 255, 255);
        $black = ImageColorAllocate($im, 0, 0, 0);
        //绘制颜色至图像中
        ImageFill($im, 0, 0, $white);
        //绘制字符串
        ImageString($im, $font, $x, $y, $string, $black);
        imagepng($im, $save_absolute_url);
        return str_replace(DOCUMENT_ROOT_PATH, '', $save_absolute_url);
    }

    /**
     * png转jpg
     * @param string $file_absolute_url 图片文件绝对地址
     * @param bool $is_delete 是否删除
     * @param int $quality 图片质量
     * @return mixed
     */
    public static function png2jpg($file_absolute_url, $is_delete = true, $quality = 90) {
        $src_file                     = $file_absolute_url;
        $add_server_document_root_dir = false;
        if (!is_file($src_file)) {
            if (is_file(DOCUMENT_ROOT_PATH . $src_file)) {
                $add_server_document_root_dir = true;
                $src_file                     = DOCUMENT_ROOT_PATH . $src_file;
            } else {
                return $file_absolute_url;
            }
        }
        $src_file_ext = ClFile::getSuffix($src_file);
        if ($src_file_ext == '.png') {
            $dst_file   = str_replace('.png', '.jpg', $src_file);
            $photo_size = GetImageSize($src_file);
            $pw         = $photo_size[0];
            $ph         = $photo_size[1];
            $dst_image  = ImageCreateTrueColor($pw, $ph);
            imagecolorallocate($dst_image, 255, 255, 255);
            //读取图片  
            $src_image = ImageCreateFromPNG($src_file);
            //合拼图片  
            imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $pw, $ph, $pw, $ph);
            imagejpeg($dst_image, $dst_file, 90);
            if ($is_delete) {
                unlink($src_file);
            }
            imagedestroy($src_image);
            if ($add_server_document_root_dir) {
                $dst_file = str_replace(DOCUMENT_ROOT_PATH, '', $dst_file);
            }
            return $dst_file;
        } else {
            return $file_absolute_url;
        }
    }

    /**
     * 圆角图
     * @param $absolute_file_url
     * @param string $save_absulute_file_url
     * @param int $radius
     * @return mixed|string
     */
    public static function radius($absolute_file_url, $save_absulute_file_url = '', $radius = 30) {
        $suffix = strtolower(ClFile::getSuffix($absolute_file_url));
        if (!in_array($suffix, ['.png', '.jpg'])) {
            echo_info('ClImage::radius.$absolute_file_url support png or jpg');
            exit;
        }
        if ($suffix == '.png') {
            $src_img = imagecreatefrompng($absolute_file_url);
        } else {
            $src_img = imagecreatefromjpeg($absolute_file_url);
        }
        $wh = getimagesize($absolute_file_url);
        $w  = $wh[0];
        $h  = $wh[1];
        // $radius = $radius == 0 ? (min($w, $h) / 2) : $radius;
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $radius; //圆 角半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
                    //不在四角的范围内,直接画
                    imagesetpixel($img, $x, $y, $rgbColor);
                } else {
                    //在四角的范围内选择画
                    //上左
                    $y_x = $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //上右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下左
                    $y_x = $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                }
            }
        }
        if (empty($save_absulute_file_url)) {
            $save_absulute_file_url = $absolute_file_url;
        }
        $save_absulute_file_url = str_replace('.jpg', '.png', strtolower($save_absulute_file_url));
        //存储为png
        imagepng($img, $save_absulute_file_url);
        return $save_absulute_file_url;
    }

}

