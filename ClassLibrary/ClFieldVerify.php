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
class ClFieldVerify extends ClFieldBase
{


    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

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
                    case 'is_date':
                        $filters_desc[] = '时间格式';
                        break;
                    case 'is_domain':
                        $filters_desc[] = '域名';
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
        $verifies = [];
        if(isset($this->field_config['verifies'])){
            $verifies = $this->field_config['verifies'];
        }
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
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isPassword($each_value, $v_verify[1], $v_verify[2])) {
                                            $error_msg = sprintf('%s:%s 密码长度%s~%s，且只能包含字符、数字和下划线', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1], $v_verify[2]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isPassword($fields[$k_field], $v_verify[1], $v_verify[2])) {
                                        $error_msg = sprintf('%s:%s 密码长度%s~%s，且只能包含字符、数字和下划线', self::getFieldDesc($k_field, $instance), $fields[$k_field], $v_verify[1], $v_verify[2]);
                                    }
                                }
                            }
                            break;
                        case 'in_array':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!in_array($each_value, $v_verify[1])) {
                                            $error_msg = sprintf('%s:%s 不在%s范围内', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), json_encode($v_verify[1], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!in_array($fields[$k_field], $v_verify[1])) {
                                        $error_msg = sprintf('%s:%s 不在%s范围内', self::getFieldDesc($k_field, $instance), $fields[$k_field], json_encode($v_verify[1], JSON_UNESCAPED_UNICODE));
                                    }
                                }
                            }
                            break;
                        case 'in_scope':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if ($each_value < $v_verify[1] || $each_value > $v_verify[2]) {
                                            $error_msg = sprintf('%s:%s 不在[%s, %s]区间内', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1], $v_verify[2]);
                                            break;
                                        }
                                    }
                                } else {
                                    if ($fields[$k_field] < $v_verify[1] || $fields[$k_field] > $v_verify[2]) {
                                        $error_msg = sprintf('%s:%s 不在[%s, %s]区间内', self::getFieldDesc($k_field, $instance), $fields[$k_field], $v_verify[1], $v_verify[2]);
                                    }
                                }
                            }
                            break;
                        case 'max':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if ($each_value > $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最大值%s', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if ($fields[$k_field] > $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最大值%s', self::getFieldDesc($k_field, $instance), $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'min':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if ($each_value < $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最小值%s', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if ($fields[$k_field] < $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最小值%s', self::getFieldDesc($k_field, $instance), $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'length':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (ClString::getLength($each_value) != $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 长度为%s', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (ClString::getLength($fields[$k_field]) != $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 长度为%s', self::getFieldDesc($k_field, $instance), $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'length_max':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (ClString::getLength($each_value) > $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最大长度%s', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (ClString::getLength($fields[$k_field]) > $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最大长度%s', self::getFieldDesc($k_field, $instance), $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                        case 'length_min':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (ClString::getLength($each_value) < $v_verify[1]) {
                                            $error_msg = sprintf('%s:%s 最小长度%s', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE), $v_verify[1]);
                                            break;
                                        }
                                    }
                                } else {
                                    if (ClString::getLength($fields[$k_field]) < $v_verify[1]) {
                                        $error_msg = sprintf('%s:%s 最小长度%s', self::getFieldDesc($k_field, $instance), $fields[$k_field], $v_verify[1]);
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    switch ($v_verify) {
                        case 'is_required':
                            //更新不必判断是否必须，必须字段是在新增的时候判断
                            if ($type == 'update') {
                                if (isset($fields[$k_field]) && !self::fieldNeedCheck($fields, $k_field)) {
                                    $error_msg = sprintf('%s不可为空', self::getFieldDesc($k_field, $instance));
                                }
                            } else {
                                if (!isset($fields[$k_field]) || !self::fieldNeedCheck($fields, $k_field)) {
                                    $error_msg = sprintf('%s为必填项', self::getFieldDesc($k_field, $instance));
                                }
                            }
                            break;
                        case 'email':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isEmail($each_value)) {
                                            $error_msg = sprintf('%s:%s 邮箱格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isEmail($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 邮箱格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'mobile':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isMobile($each_value)) {
                                            $error_msg = sprintf('%s:%s 手机号码格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isMobile($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 手机号码格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'ip':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isIp($each_value)) {
                                            $error_msg = sprintf('%s:%s ip格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isIp($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s ip格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'postcode':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isPostcode($each_value)) {
                                            $error_msg = sprintf('%s:%s 邮编格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isPostcode($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 邮编格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'id_card':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isIdCard($each_value)) {
                                            $error_msg = sprintf('%s:%s 身份证格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isIdCard($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 身份证格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChinese($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChinese($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese_alpha':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChineseAlpha($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文、英文格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChineseAlpha($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文、英文格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese_alpha_num':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChineseAlphaNum($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文、英文、数字格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChineseAlphaNum($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文、英文、数字格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'chinese_alpha_num_dash':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isChineseAlphaNumDash($each_value)) {
                                            $error_msg = sprintf('%s:%s 中文、英文、数字、-、_格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isChineseAlphaNumDash($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 中文、英文、数字、-、_格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'alpha':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isAlpha($each_value)) {
                                            $error_msg = sprintf('%s:%s 英文格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isAlpha($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 英文格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'alpha_num':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isAlphaNum($each_value)) {
                                            $error_msg = sprintf('%s:%s 英文、数字格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isAlphaNum($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 英文、数字格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'alpha_num_dash':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isAlphaNumDash($each_value)) {
                                            $error_msg = sprintf('%s:%s 英文、数字、-、_格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                            break;
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isAlphaNumDash($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 英文、数字、-、_格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'url':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isUrl($each_value)) {
                                            $error_msg = sprintf('%s:%s url格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isUrl($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s url格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'number':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!is_numeric($each_value)) {
                                            $error_msg = sprintf('%s:%s 数字格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!is_numeric($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 数字格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'tel':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isTel($each_value)) {
                                            $error_msg = sprintf('%s:%s 固话格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isTel($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 固话格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'array':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (!is_array($fields[$k_field])) {
                                    $error_msg = sprintf('%s:%s 数组格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                }
                            }
                            break;
                        case 'unique':
                            if (self::fieldNeedCheck($fields, $k_field) && !is_null($instance)) {
                                $new_instance = $instance::instance(-2);
                                if($type == 'insert'){
                                    //插入，则只需要判断是否存在
                                    $count = $new_instance->where([
                                        $k_field => $fields[$k_field]
                                    ])->count();
                                    if ($count > 0) {
                                        $error_msg = sprintf('%s:%s 该值为unique，不可重复', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
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
                                            $error_msg = sprintf('%s:%s 该值为unique，不可重复，已经存在记录id=%s', self::getFieldDesc($k_field, $instance), $fields[$k_field], $field_id);
                                        }
                                    }
                                }
                            }
                            break;
                        case 'is_date':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isDate($each_value)) {
                                            $error_msg = sprintf('%s:%s 时间格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isDate($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 时间格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
                                    }
                                }
                            }
                            break;
                        case 'is_domain':
                            if (self::fieldNeedCheck($fields, $k_field)) {
                                if (is_array($fields[$k_field])) {
                                    foreach ($fields[$k_field] as $each_value) {
                                        if (!ClVerify::isDomain($each_value)) {
                                            $error_msg = sprintf('%s:%s 域名格式错误', self::getFieldDesc($k_field, $instance), json_encode($fields[$k_field], JSON_UNESCAPED_UNICODE));
                                        }
                                    }
                                } else {
                                    if (!ClVerify::isDomain($fields[$k_field])) {
                                        $error_msg = sprintf('%s:%s 域名格式错误', self::getFieldDesc($k_field, $instance), $fields[$k_field]);
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

    /**
     * 获取字段描述
     * @param $key_field
     * @param BaseModel|null $instance
     * @return string
     */
    private static function getFieldDesc($key_field, BaseModel $instance = null){
        if(is_null($instance)){
            return $key_field;
        }
        if(isset($instance::$fields_names[$key_field]) && !empty($instance::$fields_names[$key_field])){
            return $instance::$fields_names[$key_field].'('.$key_field.')';
        }else{
            return $key_field;
        }
    }

    /**
     * 字段是否校验
     * @param $fields
     * @param $k_field
     * @return bool
     */
    private static function fieldNeedCheck($fields, $k_field){
        return isset($fields[$k_field]) && (is_numeric($fields[$k_field]) || !empty($fields[$k_field]));
    }

}