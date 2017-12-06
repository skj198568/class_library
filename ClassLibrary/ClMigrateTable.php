<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/11/30
 * Time: 9:21
 */

namespace ClassLibrary;

/**
 * 表定义
 * Class ClMigrateTable
 * @package ClassLibrary
 */
class ClMigrateTable
{
    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    /**
     * 表配置
     * @var array
     */
    private $table_config = [];

    /**
     * 表引擎
     * @var string
     */
    protected $table_engine = 'InnoDB';

    /**
     * 实例对象
     * @return ClMigrateTable|null
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取表名定义
     * @param $table_name
     * @return array
     */
    public function fetch($table_name)
    {
        $result = [
            'comment' => json_encode(
                array_merge([
                    'name' => $table_name
                ], $this->table_config),
                JSON_UNESCAPED_UNICODE),
            'engine' => $this->table_engine
        ];
        $this->table_config = [];
        return $result;
    }

    /**
     * 是否启用缓存
     * @param int $duration
     * @return $this
     */
    public function usingCache($duration = 3600)
    {
        $this->table_config['is_cache'] = $duration;
        return $this;
    }

    /**
     * 设置表引擎，不设置，默认InnoDB
     * @param string $engine
     * @return $this
     */
    public function engine($engine = 'MyISAM')
    {
        $this->table_engine = $engine;
        return $this;
    }

    /**
     * 是否创建视图层
     * @param int $show_type 1/modal弹出框, 2/page单个页面
     * @return $this
     */
    public function createView($show_type = 1)
    {
        $this->table_config['show_type'] = $show_type;
        return $this;
    }

    /**
     * 不创建api，默认创建
     * @return $this
     */
    public function notCreateApi(){
        $this->table_config['create_api'] = false;
        return $this;
    }

}