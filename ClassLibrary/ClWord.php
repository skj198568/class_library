<?php
/**
 * Created by PhpStorm.
 * User: SongKeJing
 * Email: 597481334@qq.com
 * Date: 2015/8/28
 * Time: 13:12
 */

namespace ClassLibrary;

/**
 * word类库
 * Class ClWord
 * @package ClassLibrary
 */
class ClWord {

    /**
     * 导出word文件
     * @param $html_content
     * @param string $file_name
     */
    public static function export($html_content, $file_name = '') {
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        //防止导出乱码
        $file_name = empty($file_name) ? time() : iconv("utf-8", "GBK", $file_name);
        header("Content-Type: application/doc");
        header("Content-Disposition: attachment; filename=" . $file_name . ".doc");
        exit($html_content);
    }

}
