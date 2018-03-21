<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * QQ: 597481334
 * Email: skj198568@163.com
 * Date: 2015/12/2
 * Time: 14:35
 */

namespace ClassLibrary;

/**
 * html静态处理方法
 * Class ClHtml
 * @package ClassLibrary
 */
class ClHtml {

    /**
     * 删除静态文件
     * @param $file_absolute_url
     * @return bool
     */
    public static function delete($file_absolute_url) {
        if (is_file($file_absolute_url)) {
            //删除
            return unlink($file_absolute_url);
        }
        return true;
    }

    /**
     * 删除所有html缓存
     */
    public static function deleteAll() {
        $files = ClFile::dirGetFiles(DOCUMENT_ROOT_PATH . '/Html');
        foreach ($files as $each) {
            if (is_file($each)) {
                unlink($each);
            }
        }
    }

}
