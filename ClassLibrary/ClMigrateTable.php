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
     * 新增显示的字段，用于页面显示需求
     * @param string $else_field_name 新增的字段名，建议为当前field+'_show'，例如:create_uid_show
     * @param string $relation_table_field 关联的表和字段，例如：'user.name'，会自动获取：select name form user where id == create_uid
     * @param string $this_field 默认为空，不为空，则改变匹配的本表的字段，例如：c_uid，select name form user where id == c_uid，建议留空，程序自动处理
     * @return $this
     */
    public function addElseShowFields($else_field_name, $relation_table_field, $this_field = '')
    {
        $this->table_config['else_show_fields'][] = [
            $else_field_name,
            $relation_table_field,
            $this_field
        ];
        return $this;
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

}