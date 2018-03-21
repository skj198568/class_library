<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:41
 */

namespace ClassLibrary;

/**
 * Class ClZip 压缩包类库
 * @package Common\Common
 */
class ClZip {

    /**
     * 解压缩文件
     * @param $zip_file 待解压的zip文件绝对路径
     * @param string $target_dir 解压的目标文件夹的绝对路径
     * @param bool $is_cover 是否覆盖，如果false，则不覆盖文件，否则覆盖掉已经解压出的文件
     * @return array|resource|string
     */
    public static function unzip($zip_file, $target_dir = '', $is_cover = false) {
        if (!is_file($zip_file)) {
            return false;
        }
        //目标文件夹
        if (empty($target_dir)) {
            $target_dir = explode('.', $zip_file);
            array_pop($target_dir);
            $target_dir = implode('.', $target_dir);
        }
        if ($is_cover == false && is_dir($target_dir)) {
            //如果不覆盖，则直接返回
            return $target_dir;
        }
        $zip_handle = new \ZipArchive();
        if ($zip_handle->open($zip_file) === true) {
            $zip_handle->extractTo($target_dir);
            $zip_handle->close();
        }
        return $target_dir;
    }

    /**
     * 压缩文件
     * @param $zip_file zip压缩文件绝对地址
     * @param array $files = [
     *      ['待压缩的文件绝对地址1', 'zip压缩包里面的文件存储目录1'],
     *      ['待压缩的文件绝对地址2', 'zip压缩包里面的文件存储目录2']
     * ] 待压缩的文件数组
     * @param bool $is_delete 是否删除
     * @param int $zip_flags 1/创建，8覆盖原zip文件
     * @return mixed
     */
    public static function zip($zip_file, $files, $is_delete = true, $zip_flags = 1) {
        $zip_handle = new \ZipArchive();
        //创建文件夹
        ClFile::dirCreate($zip_file);
        //打开
        $open_result = $zip_handle->open($zip_file, $zip_flags);
        if ($open_result === true) {
            foreach ($files as $file) {
                $zip_handle->addFile($file[0], $file[1]);
            }
            //关闭
            $zip_handle->close();
            //删除源文件
            if ($is_delete) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            return $zip_file;
        } else {
            log_info('open file is error');
        }
    }
}
