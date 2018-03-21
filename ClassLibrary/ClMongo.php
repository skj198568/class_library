<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/6
 * Time: 12:02
 */

namespace ClassLibrary;

/**
 * mongo处理类
 * Class ClMongo
 * @package ClassLibrary
 */
class ClMongo {

    /**
     * 针对mongo数据进行32位系统兼容处理
     * @param $data
     */
    public static function convertResult(&$data) {
        array_walk_recursive($data, function (&$val) {
            if (is_object($val)) {
                if (is_a($val, 'MongoId')) {
                    $val = $val->__toString();
                }
                if (is_a($val, 'MongoInt32') && !ClSystem::is64bit()) {
                    $val = intval($val->__toString());
                }
                if (is_a($val, 'MongoInt64') && !ClSystem::is64bit()) {
                    $val = floatval($val->__toString());
                }
            }
        });
    }

}
