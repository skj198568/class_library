<?php
/**
 * Created by PhpStorm.
 * User: Skj
 * Date: 2015/11/10
 * Time: 14:28
 */

namespace ClassLibrary;

/**
 * ftp操作
 * Class ClFtp
 * @package ClassLibrary
 */
class ClFtp
{

    /**
     * @var string
     */
    private static $con = '';

    /**
     * @param $host 远程host
     * @param $port 远程port
     * @param string $username 账号
     * @param string $password 密码
     * @param int $timeout 超时时间
     */
    public static function connect($host, $port, $username = '', $password = '', $timeout = 3600)
    {
        self::$con = ftp_connect($host, $port, $timeout) or die("Could not connect");
        if (!empty($username)) {
            ftp_login(self::$con, $username, $password);
        }
    }

    /**
     * 从ftp服务器下载文件
     * @param $file_local_absolute_url 本地存储文件地址
     * @param $file_remote_absolute_url 远程存储文件地址
     * @param int $mode 规定传输模式。可能的值有：FTP_ASCII、FTP_BINARY
     * @param int $resume 规定在远程文件中的何处开始拷贝。默认是 0。
     * @return bool
     */
    public static function down($file_local_absolute_url, $file_remote_absolute_url, $mode = FTP_BINARY, $resume = 0)
    {
        return ftp_get(self::$con, $file_local_absolute_url, $file_remote_absolute_url, $mode, $resume);
    }

    /**
     * 获取远程文件夹下面的所有文件夹
     * @param $dir_remote_absolute_url 远程绝对目录
     * @return array
     */
    public static function getDirs($dir_remote_absolute_url)
    {
        $data = ftp_nlist(self::$con, $dir_remote_absolute_url);
        return $data;
    }

    /**
     * 获取文件夹下面的所有文件
     * @param $dir_remote_absolute_url 远程文件夹目录绝对地址
     * @param array $file_types :文件类型array('pdf', 'doc')
     * @return array
     */
    public static function getFiles($dir_remote_absolute_url, $file_types = array())
    {
        $data = ftp_nlist(self::$con, $dir_remote_absolute_url);
        $return_array = array();
        if (!empty($data)) {
            if (!empty($file_types)) {
                foreach ($data as $item) {
                    if (in_array(ClFile::getSuffix($item), $file_types)) {
                        $return_array[] = $item;
                    }
                }
            } else {
                $return_array = $data;
            }
        }
        unset($data);
        return $return_array;
    }

    /**
     * 关闭链接
     */
    public static function close()
    {
        if (!empty(self::$con)) {
            ftp_close(self::$con);
        }
    }

}
