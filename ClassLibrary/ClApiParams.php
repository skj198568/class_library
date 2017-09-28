<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/9/27
 * Time: 15:47
 */

namespace ClassLibrary;

/**
 * api参数
 * Class ClApiParams
 * @package ClassLibrary
 */
class ClApiParams extends ClMigrate
{

    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    /**
     * 实例对象
     * @return ClApiParams|null
     */
    public static function instance()
    {
        if(self::$instance == null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取校验
     * @return mixed
     */
    public function fetchVerifies(){
        $verifies = $this->field_config['verifies'];
        $this->field_config = [];
        return $verifies;
    }

}