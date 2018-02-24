<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2018/2/24
 * Time: 14:28
 */

namespace ClassLibrary;

/**
 * 字段基础定义
 * Class ClFieldBase
 * @package ClassLibrary
 */
class ClFieldBase
{

    /**
     * 字段配置
     * @var array
     */
    protected $field_config = [];

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
     * 字符串长度
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
     * 时间格式
     * @return $this
     */
    public function verifyIsDate(){
        $this->field_config['verifies'][] = 'is_date';
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
     * 是否是域名
     * @return $this
     */
    public function verifyIsDomain()
    {
        $this->field_config['verifies'][] = 'is_domain';
        return $this;
    }

}