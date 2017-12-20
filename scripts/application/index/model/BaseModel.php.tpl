<?php
/**
 * Created by PhpStorm.
 * User: skj19
 * Date: 2016/8/29
 * Time: 18:31
 */

namespace app\index\model;

use ClassLibrary\ClCache;
use ClassLibrary\ClFieldVerify;
use ClassLibrary\ClString;
use think\db\Query;

/**
 * 基础Model
 * Class BaseModel
 */
class BaseModel extends Query
{

    /**
     * @var int 有效数字标识 1
     */
    const V_VALID = 1;

    /**
     * @var int 无效数字标识 0
     */
    const V_INVALID = 0;

    /**
     * @var string pk int(11)
     */
    const F_ID = 'id';

    /**
     * 逆序
     * @var string
     */
    const V_ORDER_DESC = 'DESC';

    /**
     * 正序
     * @var string
     */
    const V_ORDER_ASC = 'ASC';

    /**
     * 字段校验，用于字段内容判断
     * @var array
     */
    public static $fields_verifies = [];

    /**
     * 只读的字段，仅仅是创建的时候添加，其他地方均不可修改
     * @var array
     */
    protected static $fields_read_only = [];

    /**
     * 字段映射
     */
    protected static $fields_show_map_fields = [];

    /**
     * 字段格式化
     */
    protected static $fields_show_format = [];

    /**
     * 获取所有的字段
     * @param array $exclude_fields 不包含的字段
     * @return array
     */
    public static function getAllFields($exclude_fields = [self::F_ID])
    {
        return [];
    }

    /**
     * 重写execute方法，用于清除缓存
     * @param string $sql
     * @param array $bind
     * @return int
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function execute($sql, $bind = [])
    {
        if (strpos($sql, 'UPDATE') === 0 || strpos($sql, 'DELETE') === 0) {
            if (strpos($sql, 'UPDATE') === 0) {
                //先更新，后查询
                $result = parent::execute($sql, $bind);
                $last_sql = $this->getLastSql();
                $table_name = substr($last_sql, strpos($last_sql, '`') + 1);
                $table_name = substr($table_name, 0, strpos($table_name, '`'));
                $trigger_sql = sprintf('SELECT * FROM `%s` %s', $table_name, substr($last_sql, strpos($last_sql, 'WHERE')));
                $items = $this->query($trigger_sql);
            } else {
                //先查询，后删除
                $last_sql = $this->connection->getRealSql($sql, $bind);
                $table_name = substr($last_sql, strpos($last_sql, '`') + 1);
                $table_name = substr($table_name, 0, strpos($table_name, '`'));
                $trigger_sql = sprintf('SELECT * FROM `%s` %s', $table_name, substr($last_sql, strpos($last_sql, 'WHERE')));
                $items = $this->query($trigger_sql);
                $result = parent::execute($sql, $bind);
            }
            if (!empty($items)) {
                if (count($items) !== count($items, 1)) {
                    //多维数组
                    foreach ($items as $each) {
                        $this->cacheRemoveTrigger($each);
                    }
                } else {
                    $this->cacheRemoveTrigger($items);
                }
                //清除缓存后执行
                ClCache::removeAfter();
            }
        } else {
            //查询
            $result = parent::execute($sql, $bind);
        }
        return $result;
    }

    /**
     * 重写
     * @param array $data
     * @param bool $replace
     * @param bool $getLastInsID
     * @param null $sequence
     * @return int|string
     */
    public function insert(array $data = [], $replace = false, $getLastInsID = false, $sequence = null)
    {
        //校验参数
        ClFieldVerify::verifyFields($data, static::$fields_verifies, 'insert', static::instance());
        //自动完成字段
        if (in_array('create_time', static::getAllFields())) {
            if (!isset($data['create_time']) || empty($data['create_time'])) {
                $data['create_time'] = time();
            }
        }
        $result = parent::insert($data, $replace, $getLastInsID, $sequence);
        //执行
        if (count($data) !== count($data, 1)) {
            //多维数组
            foreach ($data as $each) {
                $this->cacheRemoveTrigger($each);
            }
        } else {
            $this->cacheRemoveTrigger($data);
        }
        //清除缓存后执行
        ClCache::removeAfter();
        return $result;
    }

    /**
     * 批量插入记录
     * @access public
     * @param mixed     $dataSet 数据集
     * @param boolean   $replace  是否replace
     * @param integer   $limit   每次写入数据限制
     * @return integer|string
     */
    public function insertAll(array $dataSet, $replace = false, $limit = null)
    {
        //校验参数
        foreach ($dataSet as $data) {
            ClFieldVerify::verifyFields($data, static::$fields_verifies, 'insert', static::instance());
        }
        //自动完成字段
        foreach ($dataSet as $k_data => $data) {
            if (in_array('create_time', static::getAllFields())) {
                if (!isset($data['create_time']) || empty($data['create_time'])) {
                    $dataSet[$k_data]['create_time'] = time();
                }
            }
        }
        $result = parent::insertAll($dataSet, $replace);
        //执行
        if (count($dataSet) !== count($dataSet, 1)) {
            //多维数组
            foreach ($dataSet as $each) {
                $this->cacheRemoveTrigger($each);
            }
        } else {
            $this->cacheRemoveTrigger($dataSet);
        }
        //清除缓存后执行
        ClCache::removeAfter();
        return $result;
    }

    /**
     * 重写update方法
     * @param array $data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function update(array $data = [])
    {
        //校验参数
        ClFieldVerify::verifyFields($data, static::$fields_verifies, 'update', static::instance());
        //自动完成字段
        if (in_array('update_time', static::getAllFields())) {
            if (!isset($data['update_time']) || empty($data['update_time'])) {
                $data['update_time'] = time();
            }
        }
        //去除只读字段
        foreach (static::$fields_read_only as $each_field) {
            if (isset($data[$each_field])) {
                unset($data[$each_field]);
            }
        }
        return parent::update($data);
    }

    /**
     * 拼接额外展现字段
     * @param $items
     * @return array|mixed
     */
    public static function showMapFields($items){
        if(empty($items)){
            return $items;
        }
        if(empty(static::$fields_show_map_fields)) {
            return $items;
        }
        //一维数组，处理成多维数组
        if (count($items) === count($items, 1)) {
            $items = [$items];
        }
        //查询结果值
        $values = [];
        //额外字段拼接
        foreach(static::$fields_show_map_fields as $field => $map_fields){
            foreach ($items as $k => $each) {
                if(isset($each[$field])){
                    foreach($map_fields as $each_map_field){
                        $table_and_field = $each_map_field[0];
                        $alias = $each_map_field[1];
                        $fetch_field = ClString::getBetween($table_and_field, '.', '', false);
                        if(strpos($table_and_field, '_') !== false){
                            $table_and_field = explode('_', $table_and_field);
                            foreach($table_and_field as $k => $each_table_and_field){
                                $table_and_field[$k] = ucfirst($each_table_and_field);
                            }
                            $model = implode('', $table_and_field);
                        }else{
                            $model = ucfirst(ClString::getBetween($table_and_field, '', '.', false));
                        }
                        //拼接Model
                        $model .= 'Model';
                        //考虑性能，对查询结果进行缓存
                        $key = sprintf('app\index\model\%s::getValueById(%s, %s)', $model, $each[$field], $fetch_field);
                        if(!isset($values[$key])){
                            $each[$alias] = forward_static_call_array([sprintf('app\index\model\%s', $model), 'getValueById'], [$each[$field], $fetch_field]);
                        }
                        $values[$key] = $each[$alias];
                    }
                }
                $items[$k] = $each;
            }
        }
        if(count($items) == 1){
            return $items[0];
        }else{
            return $items;
        }
    }

    /**
     * 字段格式化
     * @param $items
     * @return array|mixed
     */
    public static function showFormat($items){
        if(empty($items)){
            return $items;
        }
        if(empty(static::$fields_show_format)){
            return $items;
        }
        //一维数组，处理成多维数组
        if (count($items) === count($items, 1)) {
            $items = [$items];
        }
        foreach($items as $k => $item){
            foreach(static::$fields_show_format as $k_format_key => $each_format){
                if(!isset($item[$k_format_key])){
                    continue;
                }
                foreach ($each_format as $each_format_item){
                    if(is_string($each_format_item[0]) && strpos($each_format_item[0], '%s') !== false){
                        //函数型格式化
                        $format_string = sprintf('%s;', sprintf($each_format_item[0], $item[$k_format_key]));
                        $function = ClString::getBetween($format_string, '', '(', false);
                        $params = ClString::getBetween($format_string, '(', ')',false);
                        if(strpos($params, ',') !== false){
                            $params = explode(',', $params);
                        }else{
                            $params = [$params];
                        }
                        $item[$k_format_key.$each_format_item[1]] = trim(call_user_func_array($function, $params), "''");
                    }else{
                        //数组式格式化
                        foreach($each_format_item[0] as $each_format_item_each){
                            echo_info($each_format_item_each, $k_format_key);
                            if($each_format_item_each[0] == $item[$k_format_key]){
                                $item[$k_format_key.$each_format_item[1]] = $each_format_item_each[1];
                            }
                        }
                    }
                }
            }
            $items[$k] = $item;
        }
        if(count($items) == 1){
            return $items[0];
        }else{
            return $items;
        }
    }

    /**
     * 实例对象
     * @return null|static
     */
    public static function instance()
    {
        return null;
    }

    /**
     * 缓存清除触发器
     * @param $item
     */
    protected function cacheRemoveTrigger($item)
    {

    }

    /**
     * 重写cache方法，用于控制缓存的key
     * @param bool|mixed|array $key
     * @param null $expire
     * @param null $tag
     * @return $this
     */
    public function cache($key = true, $expire = null, $tag = null)
    {
        if(is_null($expire)){
            $key = false;
        }else{
            $key = call_user_func_array(['\ClassLibrary\ClCache', 'getKey'], $key);
        }
        parent::cache($key, $expire, $tag);
        return $this;
    }

}