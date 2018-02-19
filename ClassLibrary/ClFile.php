<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:32
 */

namespace ClassLibrary;

/**
 * Class ClFile(文件类库)
 * @package Common\Common
 */
class ClFile
{

    /**
     * 无限新建文件夹，支持linux和windows目录
     * @param string $absolute_file_name 待创建的文件，例如：C:\workspace\PhpStorm\CC\WebSite\Application\Runtime\Logs/Home//DDD/14_08_07.log
     * @param bool $is_file 传入的是否是文件，如果是文件则不进行文件、文件夹的自动判断
     * @return string
     */
    public static function dirCreate($absolute_file_name, $is_file = false)
    {
        $file_name = trim(str_replace('\\', '/', $absolute_file_name), '/');
        $dir_array = explode('/', $file_name);
        if (ClSystem::isWin()) {
            $dir_str_pre = '';
        } else {
            $dir_str_pre = '/';
        }
        if (empty($dir_array[0])) {
            //去除为空的数据
            array_shift($dir_array);
        }
        //判断最后一个是文件还是文件夹
        if ($is_file) {
            $min_limit = 1;
        } else {
            $min_limit = empty(self::getSuffix($file_name)) ? 0 : 1;
        }
        if ($min_limit > 0) {
            array_pop($dir_array);
        }
        //赋值
        $temp_dir_array = $dir_array;
        while (is_array($temp_dir_array) && count($temp_dir_array) > $min_limit) {
            $dir_str = $dir_str_pre . implode('/', $temp_dir_array);
            if (is_dir($dir_str)) {
                break;
            }
            array_pop($temp_dir_array);
        }
        //去除相同目录
        $dir_array = $dir_str_pre . implode('/', $dir_array);
        $dir_array = str_replace($dir_str, '', $dir_array);
        $dir_array = explode('/', trim($dir_array, '/'));
        //第一个目录不判断
        while (is_array($dir_array) && !empty($dir_array)) {
            $dir_str .= '/' . $dir_array[0];
            if (!is_dir($dir_str)) {
                mkdir($dir_str, 0777);
                if (self::checkChmod($dir_str, 0777)) {
                    //修改权限，root用户创建可能是0755
                    chmod($dir_str, 0777);
                }
            }
            array_shift($dir_array);
        }
        return $dir_str;
    }

    /**
     * 判断文件、文件夹权限
     * @param $file_path
     * @param int $target_chmod
     * @return bool
     */
    public static function checkChmod($file_path, $target_chmod = 0777)
    {
        $mod = substr(base_convert(@fileperms($file_path), 10, 8), -4);
        return ($mod == $target_chmod) || ($mod == strval($target_chmod));
    }

    /**
     * 获取当前文件夹下面所有的文件夹
     * @param $dir
     * @return array
     */
    public static function dirGet($dir)
    {
        $data = array();
        if (is_dir($dir)) {
            $dp = dir($dir);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($dir . '/' . $file)) {
                        $data[] = $dir . '/' . $file;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 获取父类文件夹
     * @param string $dir
     * @return string
     */
    public static function dirGetFather($dir)
    {
        return dirname($dir);
    }

    /**
     * 获取文件夹下面的所有文件
     * @param string $dir 文件夹目录绝对地址
     * @param array $file_types :文件类型array('.pdf', '.doc')
     * @param array $ignore_dir_or_file : 忽略的文件或文件夹
     * @return array
     */
    public static function dirGetFiles($dir, $file_types = array(), $ignore_dir_or_file = [])
    {
        foreach (['.', '..'] as $each) {
            if (!in_array($each, $ignore_dir_or_file)) {
                $ignore_dir_or_file[] = $each;
            }
        }
        $data = array();
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if (in_array($file, $ignore_dir_or_file)) {
                    continue;
                }
                if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                    $data = array_merge($data, self::dirGetFiles($dir . DIRECTORY_SEPARATOR . $file, $file_types, $ignore_dir_or_file));
                } else {
                    if (empty($file_types)) {
                        $data[] = $dir . DIRECTORY_SEPARATOR . $file;
                    } else {
                        //判断类型
                        if (in_array(self::getSuffix($file), $file_types)) {
                            $data[] = $dir . DIRECTORY_SEPARATOR . $file;
                        }
                    }
                }
            }
        } else if (is_file($dir)) {
            if (empty($file_types)) {
                if (!in_array($dir, $ignore_dir_or_file)) {
                    $data[] = $dir;
                }
            } else {
                //判断类型
                if (in_array(self::getSuffix($dir), $file_types) && !in_array($dir, $ignore_dir_or_file)) {
                    $data[] = $dir;
                }
            }
        }
        return $data;
    }

    /**
     * 获取文件名
     * @param $file
     * @param bool|false $has_suffix 是否带有后缀
     * @return array|string
     */
    public static function getName($file, $has_suffix = false)
    {
        $file = trim($file);
        $file = basename($file);
        if ($has_suffix) {
            return $file;
        } else {
            if (strpos($file, '.') === false) {
                return $file;
            } else {
                return str_replace(self::getSuffix($file), '', $file);
            }
        }
    }

    /**
     * 获取文件后缀名
     * @param $file
     * @param bool $with_point 是否包含.
     * @return string
     */
    public static function getSuffix($file, $with_point = true)
    {
        $suffix = isset(pathinfo($file)['extension']) ? strtolower(pathinfo($file)['extension']) : '';
        if($with_point && !empty($suffix)){
            $suffix = '.'.$suffix;
        }
        return $suffix;
    }

    /**
     * 下载文件
     * @param string $file 被下载文件的绝对路径
     * @param string $name 用户看到的文件名
     * @return void
     */
    public static function downloadForClient($file, $name = '')
    {
        $fileName = $name ? $name : self::getName($file, true);
        $filePath = realpath($file);
        $fp = fopen($filePath, 'rb');
        if (!$filePath || !$fp) {
            header('HTTP/1.1 404 Not Found');
            echo "Error: 404 Not Found.(server file path error)<!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding -->";
            exit;
        }
        //ie 版本 gb2312转码
        $encoded_filename = ClString::encoding($fileName, ClString::V_ENCODE_GB2312);
        header('HTTP/1.1 200 OK');
        header("Pragma: public");
        header("Expires: 0");
        header("Content-type: application/octet-stream");
        header("Content-Length: " . filesize($filePath));
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($filePath));
        $ua = ClHttp::getBrowser();
        if (in_array($ua[0], ['Internet Explorer', 'Edge'])) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if ($ua[0] == 'Mozilla Firefox') {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
        }
        // ob_end_clean(); <--有些情况可能需要调用此函数
        // 输出文件内容
        fpassthru($fp);
        exit;
    }

    /**
     * 依据远程地址生成本地地址
     * @param $url
     * @return string
     */
    public static function getLocalAbsoluteUrlByRemoteUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return $url;
        }
        if (ClString::hasChinese($url)) {
            return DOCUMENT_ROOT_PATH . '/upload' . str_replace(basename($url), ClString::toCrc32(basename($url)), parse_url($url)['path']) . '.' . (pathinfo($url)['extension']);
        } else {
            return DOCUMENT_ROOT_PATH . '/upload' . parse_url($url)['path'];
        }
    }

    /**
     * 获取本地相对地址
     * @param $url
     * @return mixed
     */
    public static function getLocalUrlByRemoteUrl($url)
    {
        return str_replace(DOCUMENT_ROOT_PATH, '', self::getLocalAbsoluteUrlByRemoteUrl($url));
    }

    /**
     * 中文路径转换为英文
     * @param $path
     * @return array|mixed|string
     */
    public static function pathChineseToEnglish($path)
    {
        //先转码
        $path = ClString::encoding($path, 'UTF-8');
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        if (strpos($path, '/') === false) {
            $suffix = self::getSuffix($path);
            if(ClString::hasChinese($path)){
                if(!empty($suffix)){
                    return ClString::toCrc32($path).$suffix;
                }else{
                    return ClString::toCrc32($path);
                }
            }else{
                return $path;
            }
        } else {
            $path = explode('/', $path);
            foreach ($path as $k => $v) {
                $path[$k] = self::pathChineseToEnglish($v);
            }
            return implode('/', $path);
        }
    }

    /**
     * 获取文件html类型
     * @param $filename
     * @return string
     */
    public static function getMimeType($filename)
    {
        $path_info = pathinfo($filename);
        switch ($path_info['extension']) {
            case 'htm':
                $mime_type = 'text/html';
                break;
            case 'html':
                $mime_type = 'text/html';
                break;
            case 'txt':
                $mime_type = 'text/plain';
                break;
            case 'cgi':
                $mime_type = 'text/plain';
                break;
            case 'php':
                $mime_type = 'text/plain';
                break;
            case 'css':
                $mime_type = 'text/css';
                break;
            case 'jpg':
                $mime_type = 'image/jpeg';
                break;
            case 'jpeg':
                $mime_type = 'image/jpeg';
                break;
            case 'jpe':
                $mime_type = 'image/jpeg';
                break;
            case 'gif':
                $mime_type = 'image/gif';
                break;
            case 'png':
                $mime_type = 'image/png';
                break;
            default:
                $mime_type = 'application/octet-stream';
                break;
        }
        return $mime_type;
    }

    /**
     * 处理客户端上传
     * @param string $file_absolute_path 文件保存绝对路径
     * @return array
     */
    public static function uploadDealClient($file_absolute_path = '')
    {
        $file_size = input('post.file_size', '', 'trim');
        $file_name = input('post.name', '', 'trim,strval');
        $chunk = input('post.chunk', 'no', 'trim');
        $root_path = sprintf(DOCUMENT_ROOT_PATH . '/upload/%s/', date('Y/m/d'));
        if (!is_dir($root_path)) {
            ClFile::dirCreate($root_path);
        }
        if ($chunk == 'no') {
            $save_file = ($file_absolute_path == '' ? $root_path : $file_absolute_path) . date('His') . '_' . (ClString::toCrc32($file_size . $file_name)) . self::getSuffix($file_name);
            if (!is_dir($save_file)) {
                ClFile::dirCreate($save_file);
            }
            move_uploaded_file($_FILES['file']['tmp_name'], $save_file);
            if (!empty($_FILES['file']['error'])) {
                $return = array(
                    'result' => false,
                    'msg' => $_FILES['file']['error']
                );
            } else {
                $return = array(
                    'result' => true,
                    'msg' => '上传成功',
                    'file' => str_replace(DOCUMENT_ROOT_PATH, '', $save_file)
                );
            }
            return $return;
        }
        //分片上传
        $chunks = input('post.chunks/d', null, 'trim');
        //目标文件
        $destination_file = ($file_absolute_path == '' ? $root_path : $file_absolute_path) . (ClString::toCrc32($file_size . $file_name . $chunks) . '_temp' . self::getSuffix($file_name));
        $chunks = input('post.chunks', null, 'trim,intval');
        if ($_FILES['file']['error'] == 0) {
            $f = null;
            if ($chunk == 0) {
                //创建文件夹
                ClFile::dirCreate($destination_file);
                //开始上传
                $f = fopen($destination_file, 'w+');
            } else {
                $f = fopen($destination_file, 'a');
            }
            //合并缓存文件
            fwrite($f, file_get_contents($_FILES['file']['tmp_name']));
            //关闭
            fclose($f);
            if ($chunk + 1 < $chunks) {
                return array(
                    'result' => true,
                    'msg' => '上传成功'
                );
            } else {
                //判断文件是否已经存在
                $temp_name = str_replace('_temp', '', $destination_file);
                if (is_file($temp_name)) {
                    if (md5_file($destination_file) == md5_file($temp_name)) {
                        //两个文件一致
                        $destination_file = $temp_name;
                        unset($destination_file);
                    } else {
                        //重名命名文件
                        $temp_name = ClFile::dirGetFather($temp_name) . "/" . md5_file($destination_file) . self::getSuffix($temp_name);
                        rename($destination_file, $temp_name);
                    }
                } else {
                    //重命名
                    rename($destination_file, $temp_name);
                }
                $destination_file = $temp_name;
                return [
                    'result' => true,
                    'msg' => '上传成功',
                    'file' => str_replace(DOCUMENT_ROOT_PATH, '', $destination_file)
                ];
            }
        } else {
            //记录日志
            log_info($_FILES);
            return [
                'result' => false,
                'msg' => $_FILES['file']['error']
            ];
        }
    }

    /**
     * 获取远程文件大小
     * @param $remote_file_url
     * @return int
     */
    public static function getRemoteFileSize($remote_file_url)
    {
        $header = get_headers($remote_file_url, true);
        if (empty($header)) {
            return 0;
        } else {
            return $header['Content-Length'];
        }
    }

    /**
     * 抓取远程文件（支持大文件）
     * @param string $remote_file_url 远程文件地址
     * @param string $local_absolute_file 本地文件绝对地址
     * @return bool|string 下载的文件绝对地址
     */
    public static function catchRemote($remote_file_url, $local_absolute_file = '', $is_file = true)
    {
        if (empty($local_absolute_file)) {
            //本地存储地址
            $local_absolute_file = ClFile::getLocalAbsoluteUrlByRemoteUrl($remote_file_url);
        }
        if (is_file($local_absolute_file)) {
            if ($is_file && filesize($local_absolute_file) == self::getRemoteFileSize($remote_file_url)) {
                return $local_absolute_file;
            } else {
                unlink($local_absolute_file);
            }
        } else {
            //创建文件夹
            self::dirCreate($local_absolute_file, true);
        }
        //设置超时时间
        @ini_set('default_socket_timeout', 2);
        //下载文件
        $f_remote = fopen($remote_file_url, 'rb');
        if (empty($f_remote)) {
            log_info('fopen error:', $remote_file_url);
            fclose($f_remote);
            //设置id
            return false;
        }
        $file_size = 0;
        $local_temp = dirname($local_absolute_file) . '/temp';
        $f_local = fopen($local_temp, 'w+');
        // 输出文件内容
        while (!feof($f_remote)) {
            $content = fread($f_remote, 8192);
            if (empty($content)) {
                log_info('file fetch:size 0', $remote_file_url);
                break;
            }
            fputs($f_local, $content);
            $file_size += 8192;
            if ($file_size > 0 && $file_size % (1024 * 1024) == 0) {
                log_info('file_size:', $file_size / (1024 * 1024) . 'M');
            }
        }
        fclose($f_local);
        fclose($f_remote);
        $file_size = filesize($local_temp);
        if ($is_file && $file_size != self::getRemoteFileSize($remote_file_url)) {
            //文件大小不一致
            unlink($local_temp);
            //设置id
        } else {
            //重命名
            rename($local_temp, $local_absolute_file);
        }
        return $local_absolute_file;
    }

}
