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
class ClMigrateTable {

    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    const V_CREATE_API_GET = 'get';
    const V_CREATE_API_CREATE = 'create';
    const V_CREATE_API_DELETE = 'delete';
    const V_CREATE_API_UPDATE = 'update';

    /**
     * 表配置
     * @var array
     */
    private $table_config = [
        'create_api' => [
            self::V_CREATE_API_GET,
            self::V_CREATE_API_CREATE,
            self::V_CREATE_API_DELETE,
            self::V_CREATE_API_UPDATE
        ]
    ];

    /**
     * 实例对象
     * @return ClMigrateTable|null
     */
    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取表名定义
     * @param $comment_name
     * @return array
     */
    public function fetch($comment_name) {
        $result             = json_encode(
            array_merge([
                'name' => $comment_name
            ], $this->table_config),
            JSON_UNESCAPED_UNICODE);
        $this->table_config = [
            'create_api' => [
                self::V_CREATE_API_GET,
                self::V_CREATE_API_CREATE,
                self::V_CREATE_API_DELETE,
                self::V_CREATE_API_UPDATE
            ]
        ];
        return $result;
    }

    /**
     * 是否启用缓存
     * @param int|null $duration 缓存时间：0/永久缓存，null/不缓存
     * @return $this
     */
    public function usingCache($duration = 3600) {
        $this->table_config['is_cache'] = $duration;
        return $this;
    }

    /**
     * 是否创建视图层
     * @param int $show_type 1/modal弹出框, 2/page单个页面
     * @return $this
     */
    public function createView($show_type = 1) {
        $this->table_config['show_type'] = $show_type;
        return $this;
    }

    /**
     * 不创建api的函数，默认全部创建
     * @param array $functions
     * @return $this
     */
    public function createApi($functions = [
        ClMigrateTable::V_CREATE_API_GET,
        ClMigrateTable::V_CREATE_API_CREATE,
        ClMigrateTable::V_CREATE_API_DELETE,
        ClMigrateTable::V_CREATE_API_UPDATE
    ]) {
        $this->table_config['create_api'] = $functions;
        return $this;
    }

    /**
     * 获取更新Comment SQL
     * @param $table
     * @param $table_desc
     * @param string $engine
     * @return string
     */
    public function getUpdateCommentSql($table, $table_desc, $engine = 'InnoDB') {
        $sql                = sprintf("ALTER TABLE `%s` ENGINE=%s COMMENT='%s'", config('database.prefix') . $table, $engine, json_encode(
                array_merge([
                    'name' => $table_desc
                ], $this->table_config),
                JSON_UNESCAPED_UNICODE)
        );
        $this->table_config = [
            'create_api' => [
                self::V_CREATE_API_GET,
                self::V_CREATE_API_CREATE,
                self::V_CREATE_API_DELETE,
                self::V_CREATE_API_UPDATE
            ]
        ];
        return $sql;
    }

    /**
     * 分表规则
     * @param array $partition
     * @return $this
     */
    public function partition($partition) {
        if (!empty($partition)) {
            $this->table_config['partition'] = $partition;
        }
        return $this;
    }

}