<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/11/30
 * Time: 9:35
 */

namespace ClassLibrary;
use app\index\model\BaseModel;

/**
 * 字段校验
 * Class ClFieldVerify
 * @package ClassLibrary
 */
class ClFieldVerify
{

    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    /**
     * 字段配置
     * @var array
     */
    protected $field_config = [];

    /**
     * 实例对象
     * @return ClFieldVerify|null
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
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
    public function verifyStringLength($length)
    {
        $this->field_config['verifies'][] = ['length', intval($length)];
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
     * 获取描述名称
     * @param $filters
     * @return string
     */
    public static function getNamesStringByVerifies($filters){
        $filters_desc = [];
        foreach ($filters as $v_verify) {
            if (is_array($v_verify)) {
                switch ($v_verify[0]) {
                    case 'password':
                        $filters_desc[] = sprintf('密码长度%s~%s', $v_verify[1], $v_verify[2]);
                        break;
                    case 'in_array':
                        $filters_desc[] = sprintf('在%s范围内', json_encode($v_verify[1], JSON_UNESCAPED_UNICODE));
                        break;
                    case 'in_scope':
                        $filters_desc[] = sprintf('不在[%s, %s]区间内', $v_verify[1], $v_verify[2]);
                        break;
                    case 'max':
                        $filters_desc[] = sprintf('最大值%s', $v_verify[1]);
                        break;
                    case 'min':
                        $filters_desc[] = sprintf('最小值%s', $v_verify[1]);
                        break;
                    case 'length':
                        $filters_desc[] = sprintf('长度为%s', $v_verify[1]);
                        break;
                    case 'length_max':
                        $filters_desc[] = sprintf('最大长度%s', $v_verify[1]);
                        break;
                    case 'length_min':
                        $filters_desc[] = sprintf('最小长度%s', $v_verify[1]);
                        break;
                }
            } else {
                switch ($v_verify) {
                    case 'is_required':
                        $filters_desc[] = '必填';
                        break;
                    case 'email':
                        $filters_desc[] = '邮箱格式';
                        break;
                    case 'mobile':
                        $filters_desc[] = '手机号';
                        break;
                    case 'ip':
                        $filters_desc[] = 'ip地址';
                        break;
                    case 'postcode':
                        $filters_desc[] = '邮编';
                        break;
                    case 'id_card':
                        $filters_desc[] = '身份证';
                        break;
                    case 'chinese':
                        $filters_desc[] = '中文';
                        break;
                    case 'chinese_alpha':
                        $filters_desc[] = '中文或英文';
                        break;
                    case 'chinese_alpha_num':
                        $filters_desc[] = '中文、英文、数字';
                        break;
                    case 'chinese_alpha_num_dash':
                        $filters_desc[] = '中文、英文、数字、-、_';
                        break;
                    case 'alpha':
                        $filters_desc[] = '英文';
                        break;
                    case 'alpha_num':
                        $filters_desc[] = '英文或数字';
                        break;
                    case 'alpha_num_dash':
                        $filters_desc[] = '英文、数字、-、_格式';
                        break;
                    case 'url':
                        $filters_desc[] = 'url';
                        break;
                    case 'number':
                        $filters_desc[] = '数字';
                        break;
                    case 'tel':
                        $filters_desc[] = '固话';
                        break;
                    case 'array':
                        $filters_desc[] = '数组';
                        break;
                    case 'unique':
                        $filters_desc[] = '唯一值';
                        break;
                }
            }
        }
        return implode('; ', $filters_desc);
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

    /**
     * 校验字段值
     * @param $fields
     * @param array $fields_verifies
     * @param string $type
     * @param BaseModel $instance
     */
    public static function verifyFields($fields, $fields_verifies = [], $type = 'insert', $instance = null)
    {
        //去除无需校验的字段
        foreach($fields as $k => $each_field){
            if(is_array($each_field) && isset($each_field[0])){
                if(in_array($each_field[0], ['exp', 'inc', 'dec'])){
                    unset($fields[$k]);
                }
            }
        }
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
                        case 'length':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (ClString::getLength($each_value) != $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 长度为%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (ClString::getLength($fields[$k_field]) != $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 长度为%s', $k_field, $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'length_max':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (ClString::getLength($each_value) > $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最大长度%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (ClString::getLength($fields[$k_field]) > $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最大长度%s', $k_field, $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'length_min':
                            if (isset($fields[$k_field])) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (ClString::getLength($each_value) < $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最小长度%s', $k_field, json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (ClString::getLength($fields[$k_field]) < $v_verify[1]) {
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
                                $new_instance = $instance::instance(-2);
                                if($type == 'insert'){
                                    //插入，则只需要判断是否存在
                                    $count = $new_instance->where([
                                        $k_field => $fields[$k_field]
                                    ])->count();
                                    if ($count > 0) {
                                        $error_msg = sprintf('%s:%s 该值为unique，不可重复', $k_field, $fields[$k_field]);
                                    }
                                }else{
                                    //更新，则要判断where条件
                                    $field_id = $new_instance->where([$k_field => $fields[$k_field]])->value($new_instance->getPk());
                                    if(!empty($field_id)){
                                        //存在值的情况
                                        $where = $instance->getOptions('where');
                                        $where_id = $new_instance->where($where['AND'])->value($new_instance->getPk());
                                        if($field_id != $where_id){
                                            //两个结果记录不同，则不可更新
                                            $error_msg = sprintf('%s:%s 该值为unique，不可重复，已经存在记录id=%s', $k_field, $fields[$k_field], $field_id);
                                        }
                                    }
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
                $value = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) : '';
                if (strpos($value, 'xmlhttprequest') !== false) {
                    //输出结果并退出
                    header('Content-Type:application/json; charset=utf-8');
                    echo($msg);
                }else{
                    echo($msg.PHP_EOL);
                }
                exit;
            }
        }
    }

}