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
class ClMigrateField extends ClFieldBase {

    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    /**
     * 实例对象
     * @return ClMigrateField|null
     */
    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 可排序
     * @return $this
     */
    public final function isSortable() {
        $this->field_config['is_sortable'] = 1;
        return $this;
    }

    /**
     * 可检索
     * @return $this
     */
    public function isSearchable() {
        $this->field_config['is_searchable'] = 1;
        return $this;
    }

    /**
     * 不可见
     * @return $this
     */
    public function invisible() {
        $this->field_config['visible'] = 0;
        return $this;
    }

    /**
     * 只读字段
     * @return $this
     */
    public function isReadOnly() {
        $this->field_config['is_read_only'] = 1;
        $this->verifyIsRequire();
        return $this;
    }

    /**
     * 预定义的静态变量的值
     * @param array $values [['man', 1, '男']]会自动生成 const V_FIELD_MAN = 1;备注是第三个参数
     * @return $this
     */
    public function constValues($values = []) {
        if (ClArray::isLinearArray($values)) {
            echo_info($values, '参数错误，应该是二维数组，请参考方法说明。');
            exit();
        }
        $this->field_config['const_values'] = ClArray::itemFilters($values);
        return $this;
    }

    /**
     * 展现时映射其他表字段，该字段必须为其他表的主键
     * @param array $map_fields 映射表字段数组：[['不带前缀的表名.表字段', '显示名称', '字段说明'], ['user.name', 'name_show', '用户名'], ['user.age', 'value_show', '年龄']]
     * @return $this
     */
    public function showMapFields($map_fields = []) {
        if (ClArray::isLinearArray($map_fields)) {
            echo_info($map_fields, '参数错误，应该是二维数组，请参考方法说明。');
            exit();
        }
        $this->field_config['show_map_fields'] = $map_fields;
        return $this;
    }

    /**
     * 展现格式化
     * @param string|array $format 两种方式，date('Y-m-d H:i:s', %s)这种会自动sprintf进行格式化，这种[["1","是"],["0","否"]]会自动按1和0进行格式化显示
     * @param string $alias_append 别名，如果不为空，则自动拼接生成新字段，如果为空，则覆盖当前字段
     * @return $this
     */
    public function showFormat($format, $alias_append = '_show') {
        $this->field_config['show_format'][] = [$format, $alias_append];
        return $this;
    }

    /**
     * 存储格式为json
     * @return $this
     */
    public function storageFormatJson() {
        $this->field_config['store_format'] = 'json';
        return $this;
    }

    /**
     * 存储格式为密码
     * @param string $salt
     * @return $this
     */
    public function storageFormatPassword($salt = '') {
        $this->field_config['store_format'] = ['password', $salt];
        return $this;
    }

    /**
     * 获取字段名定义
     * @param string $name 字段名称
     * @return string
     */
    public function fetch($name) {
        $this->field_config['name'] = $name;
        $result                     = json_encode($this->field_config, JSON_UNESCAPED_UNICODE);
        $this->field_config         = [];
        return $result;
    }

}