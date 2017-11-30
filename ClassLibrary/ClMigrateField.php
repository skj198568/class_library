<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/11/30
 * Time: 9:22
 */

namespace ClassLibrary;

/**
 * 字段定义
 * Class ClMigrateField
 * @package ClassLibrary
 */
class ClMigrateField extends ClFieldVerify
{

    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    /**
     * 实例对象
     * @return ClMigrateField|null
     */
    public static function instance()
    {
        if(self::$instance == null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 可排序
     * @return $this
     */
    public final function isSortable()
    {
        $this->field_config['is_sortable'] = 1;
        return $this;
    }

    /**
     * 可检索
     * @return $this
     */
    public function isSearchable()
    {
        $this->field_config['is_searchable'] = 1;
        return $this;
    }

    /**
     * 只读字段
     * @return $this
     */
    public function isReadOnly()
    {
        $this->field_config['is_read_only'] = 1;
        $this->verifyIsRequire();
        return $this;
    }

    /**
     * 预定义的静态变量的值
     * @param array $values [['man', 1, '男']]会自动生成 const V_FIELD_MAN = 1;备注是第三个参数
     * @return $this
     */
    public function constValues($values = [])
    {
        $this->field_config['const_values'] = ClArray::itemFilters($values);
        return $this;
    }

    /**
     * 获取字段名定义
     * @param string $name 字段名称
     * @return string
     */
    public function fetch($name)
    {
        $this->field_config['name'] = $name;
        $result = json_encode($this->field_config, JSON_UNESCAPED_UNICODE);
        $this->field_config = [];
        return $result;
    }

}