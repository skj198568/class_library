<?php
/**
 * Created by PhpStorm.
 * User =>SongKejing
 * QQ =>597481334
 * Date =>2017/8/12
 * Time =>10:26
 */

namespace ClassLibrary;

/**
 * database migrate
 * Class ClMigrate
 * @package ClassLibrary
 */
class ClMigrate
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
     * 字段配置
     * @var array
     */
    private $field_config = [];

    /**
     * 实例对象
     * @return ClMigrate|null
     */
    public static function instance()
    {
        if(self::$instance == null){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取表名定义
     * @param $table_name
     * @param integer $show_mode 1/modal, 2/page
     * @return array
     */
    public function fetchTable($table_name, $show_mode = 1){
        return [
            'comment' => json_encode(array_merge([
                'name' => $table_name,
                'show_mode' => $show_mode == 1 ? 'modal' : 'page',
            ], $this->table_config), JSON_UNESCAPED_UNICODE)
        ];
    }

    /**
     * 新增显示的字段，用于页面显示需求
     * @param string $else_field_name 新增的字段名，默认为当前field+'_show'，例如:create_uid_show
     * @param string $relation_table_field 关联的表和字段，例如：'user.name'，会自动获取：select name form user where id == create_uid
     * @param integer $is_searchable 是否可以检索
     * @param string $this_field 默认为空，不为空，则改变匹配的本表的字段，例如：c_uid，select name form user where id == c_uid，建议留空，程序自动处理
     * @return $this
     */
    public function tableAddElseShowFields($else_field_name, $relation_table_field, $is_searchable = 0, $this_field = ''){
        $this->table_config['else_show_fields'][] = [
            $else_field_name,
            $relation_table_field,
            $is_searchable,
            $this_field
        ];
        return $this;
    }

    /**
     * 设置列表默认排序方式
     * @return $this
     */
    public function tableListSortTypeDesc(){
        $this->table_config['list_sort_type'] = 'DESC';
        return $this;
    }

    /**
     * field名字定义
     * @param $name
     * @return $this
     */
    public function name($name){
        $this->field_config['name'] = $name;
        return $this;
    }

    /**
     * 页面是否显示
     * @return $this
     */
    public function isShowPage(){
        $this->field_config['is_show_page'] = 1;
        return $this;
    }

    /**
     * 页面表格是否显示
     * @return $this
     */
    public function isShowTable(){
        $this->field_config['is_show_table'] = 1;
        return $this;
    }

    /**
     * 表单是否显示
     * @return $this
     */
    public function isShowForm(){
        $this->field_config['is_show_form'] = 1;
        return $this;
    }

    /**
     * 添加的时候可编辑
     * @return $this
     */
    public function editableAdd(){
        $this->field_config['editable'][] = 'add';
        return $this;
    }

    /**
     * 修改的时候可编辑
     * @return $this
     */
    public function editableUpdate(){
        $this->field_config['editable'][] = 'update';
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function typeText(){
        $this->field_config['type'] = 'text';
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function typePassword(){
        $this->field_config['type'] = 'password';
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function typeTextArea(){
        $this->field_config['type'] = 'textarea';
        return $this;
    }

    /**
     * 类型
     * @param array $values 类似['name' => '', 'value' => '', 'checked' => 0]
     * @return $this
     */
    public function typeCheckbox($values = []){
        $this->field_config['type'] = ['checkbox', ClArray::itemFilters($values)];
        return $this;
    }

    /**
     * 类型
     * @param array $values 类似['name' => '', 'value' => '', 'checked' => 0]
     * @return $this
     */
    public function typeRadio($values = []){
        $this->field_config['type'] = ['radio', ClArray::itemFilters($values)];
        return $this;
    }

    /**
     * 类型
     * @param array $values 类似['name' => '', 'value' => '', 'checked' => 0]
     * @return $this
     */
    public function typeSelect($values = []){
        $this->field_config['type'] = ['select', ClArray::itemFilters($values)];
        return $this;
    }

    /**
     * 日期
     * @param string $format
     * @return $this
     */
    public function typeDate($format = 'Ymd'){
        $this->field_config['type'] = [
            'date',
            $format
        ];
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function typeDatetime(){
        $this->field_config['type'] = 'datetime';
        return $this;
    }

    /**
     * 类型
     * @param int $file_max_size 文件大小，单位为M
     * @param array $valid_types 空则不限制，否则进行文件类型限制，例如: ['pdf', 'doc']
     * @return $this
     */
    public function typeFile($file_max_size = 1, $valid_types = []){
        $this->field_config['type'] = ['file', $file_max_size, ClArray::itemFilters($valid_types)];
        return $this;
    }

    /**
     * 多文件上传
     * @param int $file_max_size 文件大小，单位为M
     * @param array $valid_types 空则不限制，否则进行文件类型限制，例如: ['pdf', 'doc']
     * @return $this
     */
    public function typeFiles($file_max_size = 1, $valid_types = []){
        $this->field_config['type'] = ['files', $file_max_size, ClArray::itemFilters($valid_types)];
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function typeAvatar(){
        $this->field_config['type'] = 'avatar';
        return $this;
    }

    /**
     * 类型
     * @param int $width
     * @param int $height
     * @param array $valid_types
     * @return $this
     */
    public function typeImage($width = 600, $height = 400, $valid_types = ['jpg', 'png']){
        $this->field_config['type'] = ['image', $width, $height, ClArray::itemFilters($valid_types)];
        return $this;
    }

    /**
     * 内容提醒
     * @param string $content
     * @return $this
     */
    public function placeholder($content = ''){
        $this->field_config['placeholder'] = $content;
        return $this;
    }

    /**
     * 帮助文本
     * @param string $content
     * @return $this
     */
    public function helpContent($content = ''){
        $this->field_config['help_content'] = $content;
        return $this;
    }

    /**
     * 必须填写
     * @return $this
     */
    public function isRequire(){
        $this->field_config['is_require'] = 1;
        return $this;
    }

    /**
     * 过滤器
     * @param array $filters 例如，['trim', 'intval']
     * @return $this
     */
    public function filters($filters = ['trim']){
        $this->field_config['filters'] = ClArray::itemFilters($filters);
        return $this;
    }

    /**
     * 是否在数组内
     * @param array $valid_values
     * @return $this
     */
    public function verifyInArray($valid_values = []){
        $this->field_config['verifies'][] = ['in_array', ClArray::itemFilters($valid_values)];
        return $this;
    }

    /**
     * 是否在范围内
     * @param $min
     * @param $max
     * @return $this
     */
    public function verifyIntInScope($min, $max){
        $this->field_config['verifies'][] = ['in_scope', $min, $max];
        return $this;
    }

    /**
     * 最大
     * @param $max
     * @return $this
     */
    public function verifyIntMax($max){
        $this->field_config['verifies'][] = ['max', $max];
        return $this;
    }

    /**
     * 最大
     * @param $min
     * @return $this
     */
    public function verifyIntMin($min){
        $this->field_config['verifies'][] = ['min', $min];
        return $this;
    }

    /**
     * 字符串最长
     * @param $length
     * @return $this
     */
    public function verifyStringLengthMax($length){
        $this->field_config['verifies'][] = ['length_max', $length];
        return $this;
    }

    /**
     * 字符串最长
     * @param $length
     * @return $this
     */
    public function verifyStringLengthMin($length){
        $this->field_config['verifies'][] = ['length_min', $length];
        return $this;
    }

    /**
     * 邮件
     * @return $this
     */
    public function verifyEmail(){
        $this->field_config['verifies'][] = 'email';
        return $this;
    }

    /**
     * 手机
     * @return $this
     */
    public function verifyMobile(){
        $this->field_config['verifies'][] = 'mobile';
        return $this;
    }

    /**
     * ip地址
     * @return $this
     */
    public function verifyIp(){
        $this->field_config['verifies'][] = 'ip';
        return $this;
    }

    /**
     * mac地址
     * @return $this
     */
    public function verifyMac(){
        $this->field_config['verifies'][] = 'mac';
        return $this;
    }

    /**
     * 邮政编码校验
     * @return $this
     */
    public function verifyPostcode(){
        $this->field_config['verifies'][] = 'postcode';
        return $this;
    }

    /**
     * 身份证
     * @return $this
     */
    public function verifyIdCard(){
        $this->field_config['verifies'][] = 'id_card';
        return $this;
    }

    /**
     * 汉字
     * @return $this
     */
    public function verifyChs(){
        $this->field_config['verifies'][] = 'chs';
        return $this;
    }

    /**
     * 汉字、字母
     * @return $this
     */
    public function verifyChsAlpha(){
        $this->field_config['verifies'][] = 'chs_alpha';
        return $this;
    }

    /**
     * 汉字、字母、数字
     * @return $this
     */
    public function verifyChsAlphaNum(){
        $this->field_config['verifies'][] = 'chs_alpha_num';
        return $this;
    }

    /**
     * 汉字、字母、数字、下划线_、破折号-
     * @return $this
     */
    public function verifyChsDash(){
        $this->field_config['verifies'][] = 'chs_dash';
        return $this;
    }

    /**
     * 字母
     * @return $this
     */
    public function verifyAlpha(){
        $this->field_config['verifies'][] = 'alpha';
        return $this;
    }

    /**
     * 字母和数字
     * @return $this
     */
    public function verifyAlphaNum(){
        $this->field_config['verifies'][] = 'alpha_num';
        return $this;
    }

    /**
     * 字母、数字，下划线_、破折号-
     * @return $this
     */
    public function verifyAlphaDash(){
        $this->field_config['verifies'][] = 'alpha_dash';
        return $this;
    }

    /**
     * 网址
     * @return $this
     */
    public function verifyUrl(){
        $this->field_config['verifies'][] = 'url';
        return $this;
    }

    /**
     * 浮点型
     * @return $this
     */
    public function verifyFloat(){
        $this->field_config['verifies'][] = 'float';
        return $this;
    }

    /**
     * 数字
     * @return $this
     */
    public function verifyNumber(){
        $this->field_config['verifies'][] = 'number';
        return $this;
    }

    /**
     * 固话
     * @return $this
     */
    public function verifyTel(){
        $this->field_config['verifies'][] = 'tel';
        return $this;
    }

    /**
     * 可排序
     * @return $this
     */
    public function isSortable(){
        $this->field_config['is_sortable'] = 1;
        return $this;
    }

    /**
     * 可检索
     * @return $this
     */
    public function isSearchable(){
        $this->field_config['is_searchable'] = 1;
        return $this;
    }

    /**
     * 预定义的静态变量的值
     * @param array $values
     * @return $this
     */
    public function constValues($values = []){
        $this->field_config['const_values'] = ClArray::itemFilters($values);
        return $this;
    }

    /**
     * 存储类型，默认是普通类型
     * @return $this
     */
    public function storeTypeJson(){
        $this->field_config['store_type'] = 'json';
        return $this;
    }

    /**
     * 获取字段名定义
     * @return string
     */
    public function fetchField(){
        return json_encode($this->field_config, JSON_UNESCAPED_UNICODE);
    }

}