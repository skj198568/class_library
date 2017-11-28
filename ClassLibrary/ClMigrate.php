<?php
/**
 * Created by PhpStorm.
 * User =>SongKejing
 * QQ =>597481334
 * Date =>2017/8/12
 * Time =>10:26
 */

namespace ClassLibrary;

use app\index\model\BaseModel;

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
    protected $field_config = [];

    /**
     * 表引擎
     * @var string
     */
    protected $table_engine = 'InnoDB';

    /**
     * 实例对象
     * @return ClMigrate|null
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
    public function fetchTable($table_name)
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
    public function tableAddElseShowFields($else_field_name, $relation_table_field, $this_field = '')
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
    public function tableUsingCache($duration = 3600)
    {
        $this->table_config['is_cache'] = $duration;
        return $this;
    }

    /**
     * 设置表引擎，不设置，默认InnoDB
     * @param string $engine
     * @return $this
     */
    public function tableEngine($engine = 'MyISAM')
    {
        $this->table_engine = $engine;
        return $this;
    }

    /**
     * 是否创建视图层
     * @param int $show_type 1/modal弹出框, 2/page单个页面
     * @return $this
     */
    public function tableCreateView($show_type = 1)
    {
        $this->table_config['show_type'] = $show_type;
        return $this;
    }

    /**
     * 页面是否显示
     * @return $this
     */
    public function viewIsForPage()
    {
        $this->field_config['view']['is_show_page'] = 1;
        return $this;
    }

    /**
     * 页面表格是否显示
     * @return $this
     */
    public function viewIsForTable()
    {
        $this->field_config['view']['is_show_table'] = 1;
        return $this;
    }

    /**
     * 表单是否显示
     * @return $this
     */
    public function viewIsShowForm()
    {
        $this->field_config['view']['is_show_form'] = 1;
        return $this;
    }

    /**
     * 添加的时候可编辑
     * @return $this
     */
    public function viewEditableAdd()
    {
        $this->field_config['view']['editable'][] = 'add';
        return $this;
    }

    /**
     * 修改的时候可编辑
     * @return $this
     */
    public function viewEditableUpdate()
    {
        $this->field_config['view']['editable'][] = 'update';
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function viewTypeText()
    {
        $this->field_config['view']['type'] = 'text';
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function viewTypePassword()
    {
        $this->field_config['view']['type'] = 'password';
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function viewTypeTextArea()
    {
        $this->field_config['view']['type'] = 'textarea';
        return $this;
    }

    /**
     * 类型
     * @param array $values 类似['name' => '', 'value' => '', 'checked' => 0]
     * @return $this
     */
    public function viewTypeCheckbox($values = [])
    {
        $this->field_config['view']['type'] = ['checkbox', ClArray::itemFilters($values)];
        return $this;
    }

    /**
     * 类型
     * @param array $values 类似['name' => '', 'value' => '', 'checked' => 0]
     * @return $this
     */
    public function viewTypeRadio($values = [])
    {
        $this->field_config['view']['type'] = ['radio', ClArray::itemFilters($values)];
        return $this;
    }

    /**
     * 类型
     * @param array $values 类似['name' => '', 'value' => '', 'checked' => 0]
     * @return $this
     */
    public function viewTypeSelect($values = [])
    {
        $this->field_config['view']['type'] = ['select', ClArray::itemFilters($values)];
        return $this;
    }

    /**
     * 日期
     * @param string $format
     * @return $this
     */
    public function viewTypeDate($format = 'Ymd')
    {
        $this->field_config['view']['type'] = [
            'date',
            $format
        ];
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function viewTypeDatetime()
    {
        $this->field_config['view']['type'] = 'datetime';
        return $this;
    }

    /**
     * 类型
     * @param int $file_max_size 文件大小，单位为M
     * @param array $valid_types 空则不限制，否则进行文件类型限制，例如: ['pdf', 'doc']
     * @return $this
     */
    public function viewTypeFile($file_max_size = 1, $valid_types = [])
    {
        $this->field_config['view']['type'] = ['file', $file_max_size, ClArray::itemFilters($valid_types)];
        return $this;
    }

    /**
     * 多文件上传
     * @param int $file_max_size 文件大小，单位为M
     * @param array $valid_types 空则不限制，否则进行文件类型限制，例如: ['pdf', 'doc']
     * @return $this
     */
    public function viewTypeFiles($file_max_size = 1, $valid_types = [])
    {
        $this->field_config['view']['type'] = ['files', $file_max_size, ClArray::itemFilters($valid_types)];
        return $this;
    }

    /**
     * 类型
     * @return $this
     */
    public function viewTypeAvatar()
    {
        $this->field_config['view']['type'] = 'avatar';
        return $this;
    }

    /**
     * 类型
     * @param int $width
     * @param int $height
     * @param array $valid_types
     * @return $this
     */
    public function viewTypeImage($width = 600, $height = 400, $valid_types = ['jpg', 'png'])
    {
        $this->field_config['view']['type'] = ['image', $width, $height, ClArray::itemFilters($valid_types)];
        return $this;
    }

    /**
     * 内容提醒
     * @param string $content
     * @return $this
     */
    public function viewPlaceholder($content = '')
    {
        $this->field_config['view']['placeholder'] = $content;
        return $this;
    }

    /**
     * 帮助文本
     * @param string $content
     * @return $this
     */
    public function viewHelpContent($content = '')
    {
        $this->field_config['view']['help_content'] = $content;
        return $this;
    }

    /**
     * 过滤器
     * @param array $filters 例如，['trim', 'intval']
     * @return $this
     */
    public function filters($filters = ['trim'])
    {
        $this->field_config['filters'] = ClArray::itemFilters($filters);
        return $this;
    }

    /**
     * 必须填写
     * @return $this
     */
    public function verifyIsRequire()
    {
        if (!isset($this->field_config['verifies'])) {
            $this->field_config['verifies'][] = 'is_required';
        } else {
            if (!in_array('is_required', $this->field_config['verifies'])) {
                $this->field_config['verifies'][] = 'is_required';
            }
        }
        return $this;
    }

    /**
     * 是否是密码
     * @param int $min
     * @param int $max
     * @return $this
     */
    public function verifyIsPassword($min = 6, $max = 18)
    {
        $this->field_config['verifies'][] = ['password', intval($min), intval($max)];
        return $this;
    }

    /**
     * 是否在数组内
     * @param array $valid_values
     * @return $this
     */
    public function verifyInArray($valid_values = [])
    {
        $this->field_config['verifies'][] = ['in_array', ClArray::itemFilters($valid_values)];
        return $this;
    }

    /**
     * 是否在范围内
     * @param $min
     * @param $max
     * @return $this
     */
    public function verifyIntInScope($min, $max)
    {
        $this->field_config['verifies'][] = ['in_scope', intval($min), intval($max)];
        return $this;
    }

    /**
     * 最大
     * @param $max
     * @return $this
     */
    public function verifyIntMax($max)
    {
        $this->field_config['verifies'][] = ['max', intval($max)];
        return $this;
    }

    /**
     * 最小
     * @param $min
     * @return $this
     */
    public function verifyIntMin($min)
    {
        $this->field_config['verifies'][] = ['min', intval($min)];
        return $this;
    }

    /**
     * 字符串最长
     * @param $length
     * @return $this
     */
    public function verifyStringLengthMax($length)
    {
        $this->field_config['verifies'][] = ['length_max', intval($length)];
        return $this;
    }

    /**
     * 字符串最短
     * @param $length
     * @return $this
     */
    public function verifyStringLengthMin($length)
    {
        $this->field_config['verifies'][] = ['length_min', intval($length)];
        return $this;
    }

    /**
     * 邮件
     * @return $this
     */
    public function verifyEmail()
    {
        $this->field_config['verifies'][] = 'email';
        return $this;
    }

    /**
     * 手机
     * @return $this
     */
    public function verifyMobile()
    {
        $this->field_config['verifies'][] = 'mobile';
        return $this;
    }

    /**
     * ip地址
     * @return $this
     */
    public function verifyIp()
    {
        $this->field_config['verifies'][] = 'ip';
        return $this;
    }

    /**
     * 邮政编码校验
     * @return $this
     */
    public function verifyPostcode()
    {
        $this->field_config['verifies'][] = 'postcode';
        return $this;
    }

    /**
     * 身份证
     * @return $this
     */
    public function verifyIdCard()
    {
        $this->field_config['verifies'][] = 'id_card';
        return $this;
    }

    /**
     * 汉字
     * @return $this
     */
    public function verifyChinese()
    {
        $this->field_config['verifies'][] = 'chinese';
        return $this;
    }

    /**
     * 汉字、字母
     * @return $this
     */
    public function verifyChineseAlpha()
    {
        $this->field_config['verifies'][] = 'chinese_alpha';
        return $this;
    }

    /**
     * 汉字、字母、数字
     * @return $this
     */
    public function verifyChineseAlphaNum()
    {
        $this->field_config['verifies'][] = 'chinese_alpha_num';
        return $this;
    }

    /**
     * 汉字、字母、数字、下划线_、破折号-
     * @return $this
     */
    public function verifyChineseAlphaNumDash()
    {
        $this->field_config['verifies'][] = 'chinese_alpha_num_dash';
        return $this;
    }

    /**
     * 字母
     * @return $this
     */
    public function verifyAlpha()
    {
        $this->field_config['verifies'][] = 'alpha';
        return $this;
    }

    /**
     * 字母和数字
     * @return $this
     */
    public function verifyAlphaNum()
    {
        $this->field_config['verifies'][] = 'alpha_num';
        return $this;
    }

    /**
     * 字母、数字，下划线_、破折号-
     * @return $this
     */
    public function verifyAlphaNumDash()
    {
        $this->field_config['verifies'][] = 'alpha_num_dash';
        return $this;
    }

    /**
     * 网址
     * @return $this
     */
    public function verifyUrl()
    {
        $this->field_config['verifies'][] = 'url';
        return $this;
    }

    /**
     * 数字
     * @return $this
     */
    public function verifyNumber()
    {
        $this->field_config['verifies'][] = 'number';
        return $this;
    }

    /**
     * 数组
     * @return $this
     */
    public function verifyArray()
    {
        $this->field_config['verifies'][] = 'array';
        return $this;
    }

    /**
     * 固话
     * @return $this
     */
    public function verifyTel()
    {
        $this->field_config['verifies'][] = 'tel';
        return $this;
    }

    /**
     * 唯一值
     * @return $this
     */
    public function verifyUnique()
    {
        $this->field_config['verifies'][] = 'unique';
        return $this;
    }

    /**
     * 可排序
     * @return $this
     */
    public function isSortable()
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
     * 可检索
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
    public function fetchField($name)
    {
        $this->field_config['name'] = $name;
        $result = json_encode($this->field_config, JSON_UNESCAPED_UNICODE);
        $this->field_config = [];
        return $result;
    }

    /**
     * 校验字段值
     * @param $fields
     * @param array $fields_verifies
     * @param string $type
     * @param BaseModel $instance
     */
    public static function verifyFields($fields, $fields_verifies = [], $type = 'insert', $instance = null)
    {
        $error_msg = '';
        foreach ($fields_verifies as $k_field => $each_verify) {
            foreach ($each_verify as $v_verify) {
                if (is_array($v_verify)) {
                    switch ($v_verify[0]) {
                        case 'password':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isPassword($each_value, $v_verify[1], $v_verify[2])) {
                                            $error_msg = sprintf('%s:%s 密码长度%s~%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1], $v_verify[2]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isPassword($fields[$k_field], $v_verify[1], $v_verify[2])) {
                                        $error_msg = sprintf('%s:%s 密码长度%s~%s', $k_field, $fields[$k_field], $v_verify[1], $v_verify[2]);
                                    }
                                }
                            }
                            break;
                        case 'in_array':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!in_array($each_value, $v_verify[1])) {
                                            $error_msg = sprintf('%s:%s 不在%s范围内', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), json_encode($v_verify[1], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!in_array($fields[$k_field], $v_verify[1])) {
                                        $error_msg = sprintf('%s:%s 不在%s范围内', $k_field, $fields[$k_field], json_encode($v_verify[1], JSON_UNESCAPED_UNICODE));
                                    }
                                }
                            }
                            break;
                        case 'in_scope':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if ($each_value < $v_verify[1] || $each_value > $v_verify[2]) {
                                            $error_msg = sprintf('%s:%s 不在[%s, %s]区间内', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1], $v_verify[2]);
                                            break;
                                        }
                                    }
                                } else {
                                    if ($fields[$k_field] < $v_verify[1] || $fields[$k_field] > $v_verify[2]) {
                                        $error_msg = sprintf('%s:%s 不在[%s, %s]区间内', $k_field, $fields[$k_field], $v_verify[1], $v_verify[2]);
                                    }
                                }
                            }
                            break;
                        case 'max':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if ($each_value > $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最大值%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if ($fields[$k_field] > $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最大值%s', $k_field, $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'min':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if ($each_value < $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最小值%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if ($fields[$k_field] < $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最小值%s', $k_field, $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'length_max':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClString::getLength($each_value) > $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最大长度%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClString::getLength($fields[$k_field]) > $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最大长度%s', $k_field, $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'length_min':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClString::getLength($each_value) < $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最小长度%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClString::getLength($fields[$k_field]) < $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最小长度%s', $k_field, $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    switch ($v_verify) {
                        case 'is_required':
                            //更新不必判断是否必须，必须字段是在新增的时候判断
                            if ($type != 'update') {
                                if (!isset($fields[$k_field]) || (isset($fields[$k_field]) && !is_numeric($fields[$k_field]) && empty($fields[$k_field]))) {
                                    $error_msg = sprintf('%s为必填项', $k_field);
                                }
                            } else {
                                if (isset($fields[$k_field]) && !is_numeric($fields[$k_field]) && empty($fields[$k_field])) {
                                    $error_msg = sprintf('%s不可为空', $k_field);
                                }
                            }
                            break;
                        case 'email':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isEmail($each_value)) {
                                            $error_msg = sprintf('%s:%s 邮箱格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isEmail($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 邮箱格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'mobile':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isMobile($each_value)) {
                                            $error_msg = sprintf('%s:%s 手机号码格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isMobile($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 手机号码格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'ip':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isIp($each_value)) {
                                            $error_msg = sprintf('%s:%s ip格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isIp($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s ip格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'postcode':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isPostcode($each_value)) {
                                            $error_msg = sprintf('%s:%s 邮编格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isPostcode($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 邮编格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'id_card':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isIdCard($each_value)) {
                                            $error_msg = sprintf('%s:%s 身份证格式错误', $k_field, $fields[$k_field]);
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isIdCard($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 身份证格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChinese($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChinese($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese_alpha':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChineseAlpha($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文、英文格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChineseAlpha($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文、英文格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese_alpha_num':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChineseAlphaNum($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文、英文、数字格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChineseAlphaNum($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文、英文、数字格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese_alpha_num_dash':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChineseAlphaNumDash($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文、英文、数字、-、_格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChineseAlphaNumDash($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文、英文、数字、-、_格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'alpha':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isAlpha($each_value)) {
                                            $error_msg = sprintf('%s:%s 英文格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isAlpha($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 英文格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'alpha_num':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isAlphaNum($each_value)) {
                                            $error_msg = sprintf('%s:%s 英文、数字格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isAlphaNum($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 英文、数字格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'alpha_num_dash':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isAlphaNumDash($each_value)) {
                                            $error_msg = sprintf('%s:%s 英文、数字、-、_格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isAlphaNumDash($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 英文、数字、-、_格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'url':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isUrl($each_value)) {
                                            $error_msg = sprintf('%s:%s url格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isUrl($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s url格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'number':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!is_numeric($each_value)) {
                                            $error_msg = sprintf('%s:%s 数字格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!is_numeric($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 数字格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'tel':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isTel($each_value)) {
                                            $error_msg = sprintf('%s:%s 固话格式错误', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isTel($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 固话格式错误', $k_field, $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'array':
                            if (isset($fields[$k_field])) {
                                if (!is_array($fields[$k_field])) {
                                    $error_msg = sprintf('%s:%s 数组格式错误', $k_field, $fields[$k_field]);
                                }
                            }
                            break;
                        case 'unique':
                            if (isset($fields[$k_field]) && !is_null($instance)) {
                                if ($instance->where([
                                        $k_field => $fields[$k_field]
                                    ])->count() > 0) {
                                    $error_msg = sprintf('%s:%s 该值为unique，不可重复', $k_field, $fields[$k_field]);
                                }
                            }
                            break;
                    }
                }
            }
            if (!empty($error_msg)) {
                $msg = json_encode([
                    'status' => -1,
                    'message' => $error_msg,
                    'data' => $fields
                ], JSON_UNESCAPED_UNICODE);
                $value = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
                if (strpos($value, 'xmlhttprequest') !== false) {
                    //输出结果并退出
                    header('Content-Type:application/json; charset=utf-8');
                }
                echo($msg);
                exit;
            }
        }
    }

}