<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/9/27
 * Time: 15:47
 */

namespace ClassLibrary;

/**
 * api参数
 * Class ClApiParams
 * @package ClassLibrary
 */
class ClApiParams extends ClMigrate
{

    /**
     * 实例对象
     * @var null
     */
    private static $instance = null;

    /**
     * 实例对象
     * @return ClApiParams|null
     */
    public static function instance()
    {
        if(self::$instance == null){
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

}