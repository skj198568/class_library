<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * QQ: 597481334
 * Email: skj198568@163.com
 * Date: 2016/3/21
 * Time: 22:29
 */

namespace ClassLibrary;

use Home\Model\ChineseWordModel;

/**
 * 中文分词算法
 * 使用方法：
 * $chinese_word = new ClChineseWord();
 * $chinese_word->setSourceString('江苏省中医院（南京中医药大学附属医院、江苏省红十字中医院），1954年10月创建，现已成为一所集医疗、教学、科研、预防保健、急救等于一体的现代化综');
 * $chinese_word->start();
 * $str = $chinese_word->getFinallyResult();
 * Class ClChineseWord
 * @package ClassLibrary
 */
class ClChineseWord {

    const UCS2 = 'ucs-2be';

    private static $SP = '';

    /**
     * 生成的分词结果数据类型 1 为全部， 2为 词典词汇及单个中日韩简繁字符及英文， 3 为词典词汇及英文
     * @var int
     */
    private $result_type = 1;

    /**
     * 句子小于此长度，则不进行分词
     * @var int
     */
    private $not_split_length = 5;

    /**
     * 使用最大切分模式对二元词进行消岐
     * @var bool
     */
    private $differ_max = false;

    /**
     * 尝试合并单字
     * @var bool
     */
    private $unit_word = true;

    /**
     * 使用热门词优先模式进行消岐(词频)
     * @var bool
     */
    private $differ_freq = false;

    /**
     * 源字符串(utf-8)
     * @var string
     */
    private $source_string = '';

    /**
     * 主词典词语最大长度 x / 2
     * @var int
     */
    private $dic_word_max = 14;

    /**
     * 粗分后的数组（通常是截取句子等用途）
     * @var array
     */
    private $simple_result = array();

    /**
     * 最终结果(用空格分开的词汇列表)
     * @var string
     */
    private $finally_result = '';

    /**
     * 系统识别或合并的新词
     * @var array
     */
    public $new_words = array();

    /**
     * 是否进行词性词频分析
     * @var bool
     */
    private $word_property = false;

    /**
     * 英文是否转换为小写
     * @var bool
     */
    private $english_to_lower = false;

    public $found_word_str = '';

    /**
     * 是否对结果进行优化
     * @var bool
     */
    private $is_optimize = true;

    private $add_on_dic = array(
        //停止词
        's' => array('并', '让', '才', '上', '被', '把', '近', '而', '是', '为', '由', '等', '合', '子', '除', '均', '很', '也', '称', '还', '分', '据', '后', '向', '经', '对', '但', '只', '则', '设', '靠', '至', '到', '将', '及', '与', '或', '来', '了', '从', '说', '就', '的', '和', '在', '方', '以', '已', '有', '都', '给', '要'),
        //姓或其它专用前缀词
        'n' => array('新', '肖', '胡', '罗', '程', '施', '满', '石', '秦', '苏', '范', '包', '袁', '许', '舒', '薛', '蒋', '董', '白', '田', '季', '丁', '汪', '段', '梁', '林', '杜', '杨', '毛', '江', '熊', '王', '潘', '沈', '汤', '谢', '谭', '韩', '顾', '雷', '陈', '阎', '陆', '马', '高', '龙', '龚', '黎', '黄', '魏', '钱', '钟', '赵', '邓', '赖', '贾', '贺', '邱', '邵', '郭', '金', '郝', '郑', '邹', '李', '武', '余', '夏', '唐', '朱', '何', '姚', '孟', '孙', '孔', '姜', '周', '吴', '卢', '单', '刘', '冯', '史', '叶', '吕', '候', '傅', '宋', '任', '文', '戴', '徐', '张', '万', '方', '曾', '曹', '易', '廖', '彭', '常', '尹', '乔', '于', '康', '崔', '布', '钟离', '令狐', '公冶', '公孙', '闻人', '鲜于', '上官', '仲孙', '万俟', '东方', '闾丘', '长孙', '诸葛', '申屠', '皇甫', '尉迟', '濮阳', '澹台', '欧阳', '慕容', '淳于', '宗政', '宇文', '司徒', '轩辕', '单于', '赫连', '司空', '太叔', '夏侯', '司马', '公羊', '勿', '成吉', '埃', '哈'),
        //单位或专用后缀词
        'u' => array('u‰', '℃', '℉', '毛', '段', '步', '毫', '池', '滴', '派', '洲', '款', '次', '桩', '档', '桌', '桶', '梯', '楼', '棵', '炮', '点', '盏', '盆', '界', '盒', '盘', '眼', '画', '男', '环', '版', '片', '班', '瓣', '生', '瓶', '案', '格', '族', '方', '斤', '日', '时', '期', '月', '曲', '斗', '文', '指', '拳', '拨', '掌', '排', '丈', '撮', '本', '朵', '栋', '柜', '柄', '栏', '株', '根', '样', '架', '枪', '条', '束', '村', '杯', '枝', '枚', '石', '码', '辈', '辆', '轮', '连', '通', '里', '部', '遍', '转', '车', '言', '角', '袋', '课', '起', '路', '趟', '重', '针', '项', '顷', '顶', '顿', '颗', '首', '餐', '页', '集', '锅', '钱', '钟', '门', '间', '隅', '队', '行', '节', '筐', '笔', '筒', '箱', '篮', '篓', '篇', '章', '站', '磅', '碟', '碗', '种', '科', '窝', '秒', '簇', '米', '脚', '股', '群', '船', '艇', '色', '艘', '罐', '级', '粒', '类', '组', '维', '缸', '缕', '招', '支', '发', '双', '厘', '口', '句', '台', '只', '厅', '卷', '包', '勺', '匙', '匹', '升', '区', '叶', '号', '地', '圈', '圆', '场', '块', '堆', '坪', '团', '回', '吨', '名', '拍', '员', '周', '副', '剑', '代', '付', '件', '伏', '份', '人', '亩', '世', '下', '两', '个', '串', '伙', '位', '划', '分', '列', '则', '剂', '刻', '刀', '出', '倍', '例', '元', '克', '册', '具', '声', '听', '幅', '帧', '房', '批', '师', '岁', '尾', '尺', '局', '层', '届', '手', '壶', '成', '张', '截', '户', '扇', '年', '度', '座', '尊', '幢', '室', '寸', '头', '宗', '字', '孔', '所', '女', '套', '拉', '家', '处', '折', '天', '把', '夜', '担', '號', '个月', '公斤', '公分', '公克', '公担', '公亩', '公升', '公尺', '像素', '月份', '盎司', '位数', '公里', '年级', '点钟', '克拉', '英亩', '平方', '加仑', '公顷', '秒钟', '千克', '世纪', '千米', '分钟', '海里', '英寸', '英尺', '英里', '年代', '周年', '小时', '阶段', '平米', '立方米', '立方码', '平方米', '平方码', '平方厘米', '立方英寸', '立方厘米', '立方分米', '立方公尺', '立方英尺', '平方公尺', '平方英尺', '平方英寸', '平方分米', '平方公里', '平方英里', '百位', '十位', '百次', '千次', '千名', '千亩', '千里', '千人', '千台', '千位', '万次', '万元', '万里', '万位', '万件', '万单', '万个', '万台', '万名', '万人', '亿元', '亿', '万', '千', '萬'),
        //地名等后置词
        'a' => array('语', '署', '苑', '街', '省', '湖', '乡', '海', '观', '路', '娃', '山', '阁', '部', '镇', '江', '河', '厅', '郡', '厂', '楼', '园', '区', '党', '井', '亭', '塔', '县', '家', '市', '弄', '巷', '寺', '局', '中路', '村委', '诺夫', '斯基', '维奇', '村委会', '机', '型', '率'),
        //数量前缀词
        'c' => array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '百', '千', '万', '亿', '第', '半', '几', '俩', '卅', '两', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖', '拾', '伯', '仟'),
        //省会等专用词
        't' => array('京', '津', '沪', '渝', '冀', '豫', '云', '辽', '黑', '湘', '皖', '鲁', '新', '苏', '浙', '赣', '鄂', '桂', '甘', '晋', '蒙', '陕', '吉', '闽', '贵', '粤', '青', '藏', '川', '宁', '琼')
    );

    /**
     * 构造函数
     * @param bool $differ_freq 岐义处理
     * @param bool $unit_word 新词识别
     * @param bool $differ_max 多元切分
     * @param bool $word_property 词性标注
     */
    public function __construct($differ_freq = true, $unit_word = true, $differ_max = true, $word_property = false) {
        self::$SP            = chr(0xFF) . chr(0xFE);
        $this->differ_freq   = $differ_freq;
        $this->unit_word     = $unit_word;
        $this->differ_max    = $differ_max;
        $this->word_property = $word_property;
        //转换$add_on_dic词典
        foreach ($this->add_on_dic as $k => $v) {
            foreach ($v as $v_k => $v_each) {
                $this->add_on_dic[$k][$v_k] = $this->inStringEncode($v_each);
            }
        }
    }

    /**
     * 生成的分词结果数据类型 1 为全部， 2为 词典词汇及单个中日韩简繁字符及英文， 3 为词典词汇及英文
     * @param int $result_type
     */
    public function setResultType($result_type = 1) {
        $this->result_type = $result_type;
    }

    /**
     * 设置句子分词长度，小于此长度则不会进行分词
     * @param int $not_split_length
     */
    public function setNotSplitLength($not_split_length = 5) {
        $this->not_split_length = $not_split_length;
    }

    /**
     * 设置岐义处理
     * @param bool $differ_freq
     */
    public function setDifferFreq($differ_freq = true) {
        $this->differ_freq = $differ_freq;
    }

    /**
     * 设置新词识别
     * @param bool $unit_word
     */
    public function setUnitWord($unit_word = true) {
        $this->unit_word = $unit_word;
    }

    /**
     * 设置多元切分
     * @param bool $differ_max
     */
    public function setDifferMax($differ_max = true) {
        $this->differ_max = $differ_max;
    }

    /**
     * 设置词性标注
     * @param bool $word_property
     */
    public function setWordProperty($word_property = true) {
        $this->word_property = $word_property;
    }

    /**
     * 设置待分词的字符串utf-8
     * @param $source_string
     */
    public function setSourceString($source_string) {
        //先去除所有空格
        $source_string = trim(preg_replace('/^[(\xc2\xa0)|\s]+/', '', $source_string));
        //转码
        $this->source_string = $this->inStringEncode($source_string);
    }

    /**
     * 设置是否对结果进行优化
     * @param bool $is_optimize
     */
    public function setIsOptimize($is_optimize = true) {
        $this->is_optimize = $is_optimize;
    }

    /**
     * 获取词的信息
     * @param $word 词
     * @param int $is_new
     * @return array|mixed
     */
    private function getWordInfo($word, $is_new = 0) {
        if (empty($word)) {
            return array();
        }
        $word = $this->outStringEncoding($word);
        return ChineseWordModel::getByWord($word, $is_new);
    }

    /**
     * 删除词信息缓存
     * @param $word
     */
    private function getWordInfoRc($word) {
        ChineseWordModel::getByWordRc($word);
    }

    /**
     * 获得某个词的词性及词频信息
     * @parem $word unicode编码的词
     * @return void
     */
    public function getWordProperty($word) {
        if (strlen($word) < 4) {
            return '/s';
        }
        $word_info = $this->getWordInfo($word);
        return empty($word_info) ? '/s' : "/{$word_info['category']}{$word_info['frequency']}";
    }

    /**
     * 设置 word info
     * @param $word
     * @param $word_info
     * @return bool
     */
    private function setWordInfo($word, $word_info) {
        if (empty($word)) {
            return false;
        }
        //判断该词汇是否存在
        $is_exist_word_info = $this->getWordInfo($word, -1);
        $word               = $this->outStringEncoding($word);
        if (!empty($is_exist_word_info)) {
            //词频+1
            return ChineseWordModel::setIncFrequency($is_exist_word_info['id']);
        } else {
            //删除词信息缓存
            $this->getWordInfoRc($word);
            //新增词汇
            return ChineseWordModel::addNew(array(
                'word'      => $word,
                'frequency' => intval($word_info['c']),
                'category'  => strval($word_info['m']),
                'is_new'    => 1
            ));
        }
    }

    /**
     * 是否存在当前词
     * @param $word
     * @return bool
     */
    private function isExistWord($word) {
        $word_info = $this->getWordInfo($word);
        return empty($word_info) ? false : true;
    }

    /**
     * 开始分词
     */
    public function start() {
        //重置数据
        $this->resetValueForNewStart();
        $this->source_string .= chr(0) . chr(32);
        $s_len               = strlen($this->source_string);
        //全角与半角字符对照表
        $sbcArr = array();
        $j      = 0;
        $scb    = '';
        for ($i = 0xFF00; $i < 0xFF5F; $i++) {
            $scb = 0x20 + $j;
            $j++;
            $sbcArr[$i] = $scb;
        }
        //对字符串进行粗分
        $onstr          = '';
        $lastc          = 1; //1 中/韩/日文, 2 英文/数字/符号('.', '@', '#', '+'), 3 ANSI符号 4 纯数字 5 非ANSI符号或不支持字符
        $s              = 0;
        $ansiWordMatch  = "[0-9a-z@#%\+\.-]";
        $notNumberMatch = "[a-z@#%\+]";
        for ($i = 0; $i < $s_len; $i++) {
            $c  = $this->source_string[$i] . $this->source_string[++$i];
            $cn = hexdec(bin2hex($c));
            $cn = isset($sbcArr[$cn]) ? $sbcArr[$cn] : $cn;
            //ANSI字符
            if ($cn < 0x80) {
                if (preg_match('/' . $ansiWordMatch . '/i', chr($cn))) {
                    if ($lastc != 2 && $onstr != '') {
                        $this->simple_result[$s]['w'] = $onstr;
                        $this->simple_result[$s]['t'] = $lastc;
                        $this->deepAnalysis($onstr, $lastc, $s);
                        $s++;
                        $onstr = '';
                    }
                    $lastc = 2;
                    $onstr .= chr(0) . chr($cn);
                } else {
                    if ($onstr != '') {
                        $this->simple_result[$s]['w'] = $onstr;
                        if ($lastc == 2) {
                            if (!preg_match('/' . $notNumberMatch . '/i', iconv(self::UCS2, 'utf-8', $onstr)))
                                $lastc = 4;
                        }
                        $this->simple_result[$s]['t'] = $lastc;
                        if ($lastc != 4)
                            $this->deepAnalysis($onstr, $lastc, $s);
                        $s++;
                    }
                    $onstr = '';
                    $lastc = 3;
                    if ($cn < 31) {
                        continue;
                    } else {
                        $this->simple_result[$s]['w'] = chr(0) . chr($cn);
                        $this->simple_result[$s]['t'] = 3;
                        $s++;
                    }
                }
            }//普通字符
            else {
                //正常文字
                if (($cn > 0x3FFF && $cn < 0x9FA6) || ($cn > 0xF8FF && $cn < 0xFA2D)
                    || ($cn > 0xABFF && $cn < 0xD7A4) || ($cn > 0x3040 && $cn < 0x312B)
                ) {
                    if ($lastc != 1 && $onstr != '') {
                        $this->simple_result[$s]['w'] = $onstr;
                        if ($lastc == 2) {
                            if (!preg_match('/' . $notNumberMatch . '/i', iconv(self::UCS2, 'utf-8', $onstr)))
                                $lastc = 4;
                        }
                        $this->simple_result[$s]['t'] = $lastc;
                        if ($lastc != 4)
                            $this->deepAnalysis($onstr, $lastc, $s);
                        $s++;
                        $onstr = '';
                    }
                    $lastc = 1;
                    $onstr .= $c;
                }//特殊符号
                else {
                    if ($onstr != '') {
                        $this->simple_result[$s]['w'] = $onstr;
                        if ($lastc == 2) {
                            if (!preg_match('/' . $notNumberMatch . '/i', iconv(self::UCS2, 'utf-8', $onstr)))
                                $lastc = 4;
                        }
                        $this->simple_result[$s]['t'] = $lastc;
                        if ($lastc != 4)
                            $this->deepAnalysis($onstr, $lastc, $s);
                        $s++;
                    }

                    //检测书名
                    if ($cn == 0x300A) {
                        $tmpw = '';
                        $n    = 1;
                        $isok = false;
                        $ew   = chr(0x30) . chr(0x0B);
                        while (true) {
                            $w = $this->source_string[$i + $n] . $this->source_string[$i + $n + 1];
                            if ($w == $ew) {
                                $this->simple_result[$s]['w'] = $c;
                                $this->simple_result[$s]['t'] = 5;
                                $s++;

                                $this->simple_result[$s]['w'] = $tmpw;
                                $this->new_words[$tmpw]       = 1;
                                if (!isset($this->new_words[$tmpw])) {
                                    $this->found_word_str .= $this->outStringEncoding($tmpw) . '/nb, ';
                                    $this->setWordInfo($tmpw, array('c' => 1, 'm' => 'nb'));
                                }
                                $this->simple_result[$s]['t'] = 13;

                                $s++;

                                //最大切分模式对书名继续分词
                                if ($this->differ_max) {
                                    $this->simple_result[$s]['w'] = $tmpw;
                                    $this->simple_result[$s]['t'] = 21;
                                    $this->deepAnalysis($tmpw, $lastc, $s);
                                    $s++;
                                }

                                $this->simple_result[$s]['w'] = $ew;
                                $this->simple_result[$s]['t'] = 5;
                                $s++;

                                $i     = $i + $n + 1;
                                $isok  = true;
                                $onstr = '';
                                $lastc = 5;
                                break;
                            } else {
                                $n    = $n + 2;
                                $tmpw .= $w;
                                if (strlen($tmpw) > 60) {
                                    break;
                                }
                            }
                        }//while
                        if (!$isok) {
                            $this->simple_result[$s]['w'] = $c;
                            $this->simple_result[$s]['t'] = 5;
                            $s++;
                            $onstr = '';
                            $lastc = 5;
                        }
                        continue;
                    }

                    $onstr = '';
                    $lastc = 5;
                    if ($cn == 0x3000) {
                        continue;
                    } else {
                        $this->simple_result[$s]['w'] = $c;
                        $this->simple_result[$s]['t'] = 5;
                        $s++;
                    }
                }//2byte symbol

            }//end 2byte char

        }//end for

        //处理分词后的结果
        $this->sortFinallyResult();
    }

    /**
     * 为了再次分词设置程序默认值
     */
    private function resetValueForNewStart() {
        $this->finally_result = array();
        $this->simple_result  = array();
    }

    /**
     * 深入分词
     * @parem $str
     * @parem $ctype (1 中文 2 英文类， 3 中/韩/日文类)
     * @parem $spos   当前粗分结果游标
     * @return bool
     */
    private function deepAnalysis(&$str, $ctype, $spos) {

        //中文句子
        if ($ctype == 1) {
            $s_len = strlen($str);
            //小于系统配置分词要求长度的句子
            if ($s_len < $this->not_split_length) {
                $tmpstr   = '';
                $lastType = 0;
                if ($spos > 0)
                    $lastType = $this->simple_result[$spos - 1]['t'];
                if ($s_len < 5) {
                    //echo iconv(self::UCS2, 'utf-8', $str).'<br/>';
                    if ($lastType == 4 && in_array($str, $this->add_on_dic['u'], true) || in_array(substr($str, 0, 2), $this->add_on_dic['u'], true)) {
                        $str2 = '';
                        if (!in_array($str, $this->add_on_dic['u'], true) && in_array(substr($str, 0, 2), $this->add_on_dic['u'], true)) {
                            $str2 = substr($str, 2, 2);
                            $str  = substr($str, 0, 2);
                        }
                        $ww                                  = $this->simple_result[$spos - 1]['w'] . $str;
                        $this->simple_result[$spos - 1]['w'] = $ww;
                        $this->simple_result[$spos - 1]['t'] = 4;
                        if (!isset($this->new_words[$this->simple_result[$spos - 1]['w']])) {
                            $this->found_word_str .= $this->outStringEncoding($ww) . '/mu, ';
                            $this->setWordInfo($ww, array('c' => 1, 'm' => 'mu'));
                        }
                        $this->simple_result[$spos]['w'] = '';
                        if ($str2 != '') {
                            $this->finally_result[$spos - 1][] = $ww;
                            $this->finally_result[$spos - 1][] = $str2;
                        }
                    } else {
                        $this->finally_result[$spos][] = $str;
                    }
                } else {
                    $this->deepAnalysisCn($str, $ctype, $spos, $s_len);
                }
            }//正常长度的句子，循环进行分词处理
            else {
                $this->deepAnalysisCn($str, $ctype, $spos, $s_len);
            }
        }//英文句子，转为小写
        else {
            if ($this->english_to_lower) {
                $this->finally_result[$spos][] = strtolower($str);
            } else {
                $this->finally_result[$spos][] = $str;
            }
        }
    }

    /**
     * 中文的深入分词
     * @parem $str
     * @return void
     */
    private function deepAnalysisCn(&$str, $lastec, $spos, $s_len) {
        $quote1 = chr(0x20) . chr(0x1C);
        $tmparr = array();
        $hasw   = 0;
        //如果前一个词为 “ ， 并且字符串小于3个字符当成一个词处理。
        if ($spos > 0 && $s_len < 11 && $this->simple_result[$spos - 1]['w'] == $quote1) {
            $tmparr[] = $str;
            if (!isset($this->new_words[$str])) {
                $this->found_word_str .= $this->outStringEncoding($str) . '/nq, ';
                $this->setWordInfo($str, array('c' => 1, 'm' => 'nq'));
            }
            if (!$this->differ_max) {
                $this->finally_result[$spos][] = $str;
                return;
            }
        }
        //进行切分
        for ($i = $s_len - 1; $i > 0; $i -= 2) {
            //单个词
            $nc = $str[$i - 1] . $str[$i];
            //是否已经到最后两个字
            if ($i <= 2) {
                $tmparr[] = $nc;
                $i        = 0;
                break;
            }
            $isok = false;
            $i    = $i + 1;
            for ($k = $this->dic_word_max; $k > 1; $k = $k - 2) {
                if ($i < $k)
                    continue;
                $w = substr($str, $i - $k, $k);
                if (strlen($w) <= 2) {
                    $i = $i - 1;
                    break;
                }
                if ($this->isExistWord($w)) {
                    $tmparr[] = $w;
                    $i        = $i - $k + 1;
                    $isok     = true;
                    break;
                }
            }
            //echo '<hr />';
            //没适合词
            if (!$isok) {
                $tmparr[] = $nc;
            }
        }
        $wcount = count($tmparr);
        if ($wcount == 0) {
            return;
        }
        $this->finally_result[$spos] = array_reverse($tmparr);
        //优化结果(岐义处理、新词、数词、人名识别等)
        if ($this->is_optimize) {
            $this->optimizeResult($this->finally_result[$spos], $spos);
        }
    }

    /**
     * 对最终分词结果进行优化（把simple_result结果合并，并尝试新词识别、数词合并等）
     * @parem $optimize 是否优化合并的结果
     * @return bool
     */
    //t = 1 中/韩/日文, 2 英文/数字/符号('.', '@', '#', '+'), 3 ANSI符号 4 纯数字 5 非ANSI符号或不支持字符
    private function optimizeResult(&$smarr, $spos) {
        $newarr = array();
        $prePos = $spos - 1;
        $arlen  = count($smarr);
        $i      = $j = 0;
        //检测数量词
        if ($prePos > -1 && !isset($this->finally_result[$prePos])) {
            $lastw = $this->simple_result[$prePos]['w'];
            $lastt = $this->simple_result[$prePos]['t'];
            if (($lastt == 4 || in_array($lastw, $this->add_on_dic['c'], true)) && in_array($smarr[0], $this->add_on_dic['u'], true)) {
                $this->simple_result[$prePos]['w'] = $lastw . $smarr[0];
                $this->simple_result[$prePos]['t'] = 4;
                if (!isset($this->new_words[$this->simple_result[$prePos]['w']])) {
                    $this->found_word_str .= $this->outStringEncoding($this->simple_result[$prePos]['w']) . '/mu, ';
                    $this->setWordInfo($this->simple_result[$prePos]['w'], array('c' => 1, 'm' => 'mu'));
                }
                $smarr[0] = '';
                $i++;
            }
        }
        for (; $i < $arlen; $i++) {
            if (!isset($smarr[$i + 1])) {
                $newarr[$j] = $smarr[$i];
                break;
            }
            $cw      = $smarr[$i];
            $nw      = $smarr[$i + 1];
            $ischeck = false;
            //检测数量词
            if (in_array($cw, $this->add_on_dic['c'], true) && in_array($nw, $this->add_on_dic['u'], true)) {
                //最大切分时保留合并前的词
                if ($this->differ_max) {
                    $newarr[$j] = chr(0) . chr(0x28);
                    $j++;
                    $newarr[$j] = $cw;
                    $j++;
                    $newarr[$j] = $nw;
                    $j++;
                    $newarr[$j] = chr(0) . chr(0x29);
                    $j++;
                }
                $newarr[$j] = $cw . $nw;
                if (!isset($this->new_words[$newarr[$j]])) {
                    $this->found_word_str .= $this->outStringEncoding($newarr[$j]) . '/mu, ';
                    $this->setWordInfo($newarr[$j], array('c' => 1, 'm' => 'mu'));
                }
                $j++;
                $i++;
                $ischeck = true;
            }//检测前导词(通常是姓)
            else if (in_array($smarr[$i], $this->add_on_dic['n'], true)) {
                $is_rs = false;
                //词语是副词或介词或频率很高的词不作为人名
                if (strlen($nw) == 4) {
                    $winfos = $this->getWordInfo($nw);
                    if (isset($winfos['m']) && ($winfos['m'] == 'r' || $winfos['m'] == 'c' || $winfos['c'] > 500)) {
                        $is_rs = true;
                    }
                }
                if (!in_array($nw, $this->add_on_dic['s'], true) && strlen($nw) < 5 && !$is_rs) {
                    $newarr[$j] = $cw . $nw;
                    //echo iconv(self::UCS2, 'utf-8', $newarr[$j])."<br />";
                    //尝试检测第三个词
                    if (strlen($nw) == 2 && isset($smarr[$i + 2]) && strlen($smarr[$i + 2]) == 2 && !in_array($smarr[$i + 2], $this->add_on_dic['s'], true)) {
                        $newarr[$j] .= $smarr[$i + 2];
                        $i++;
                    }
                    if (!isset($this->new_words[$newarr[$j]])) {
                        $this->setWordInfo($newarr[$j], array('c' => 1, 'm' => 'nr'));
                        $this->found_word_str .= $this->outStringEncoding($newarr[$j]) . '/nr, ';
                    }
                    //为了防止错误，保留合并前的姓名
                    if (strlen($nw) == 4) {
                        $j++;
                        $newarr[$j] = chr(0) . chr(0x28);
                        $j++;
                        $newarr[$j] = $cw;
                        $j++;
                        $newarr[$j] = $nw;
                        $j++;
                        $newarr[$j] = chr(0) . chr(0x29);
                    }
                    $j++;
                    $i++;
                    $ischeck = true;
                }
            }//检测后缀词(地名等)
            else if (in_array($nw, $this->add_on_dic['a'], true)) {
                $is_rs = false;
                //词语是副词或介词不作为前缀
                if (strlen($cw) > 2) {
                    $winfos = $this->getWordInfo($cw);
                    if (isset($winfos['m']) && ($winfos['m'] == 'a' || $winfos['m'] == 'r' || $winfos['m'] == 'c' || $winfos['c'] > 500)) {
                        $is_rs = true;
                    }
                }
                if (!in_array($cw, $this->add_on_dic['s'], true) && !$is_rs) {
                    $newarr[$j] = $cw . $nw;
                    if (!isset($this->new_words[$newarr[$j]])) {
                        $this->found_word_str .= $this->outStringEncoding($newarr[$j]) . '/na, ';
                        $this->setWordInfo($newarr[$j], array('c' => 1, 'm' => 'na'));
                    }
                    $i++;
                    $j++;
                    $ischeck = true;
                }
            }//新词识别（暂无规则）
            else if ($this->unit_word) {
                if (strlen($cw) == 2 && strlen($nw) == 2
                    && !in_array($cw, $this->add_on_dic['s'], true) && !in_array($cw, $this->add_on_dic['t'], true) && !in_array($cw, $this->add_on_dic['a'], true)
                    && !in_array($nw, $this->add_on_dic['s'], true) && !in_array($nw, $this->add_on_dic['c'], true)
                ) {
                    $newarr[$j] = $cw . $nw;
                    //尝试检测第三个词
                    if (isset($smarr[$i + 2]) && strlen($smarr[$i + 2]) == 2 && (in_array($smarr[$i + 2], $this->add_on_dic['a'], true)) || in_array($smarr[$i + 2], $this->add_on_dic['u'], true)) {
                        $newarr[$j] .= $smarr[$i + 2];
                        $i++;
                    }
                    if (!isset($this->new_words[$newarr[$j]])) {
                        $this->found_word_str .= $this->outStringEncoding($newarr[$j]) . '/ms, ';
                        $this->setWordInfo($newarr[$j], array('c' => 1, 'm' => 'ms'));
                    }
                    $i++;
                    $j++;
                    $ischeck = true;
                }
            }
            //不符合规则
            if (!$ischeck) {
                $newarr[$j] = $cw;
                //二元消岐处理——最大切分模式
                if ($this->differ_max && !in_array($cw, $this->add_on_dic['s'], true) && strlen($cw) < 5 && strlen($nw) < 7) {
                    $s_len   = strlen($nw);
                    $hasDiff = false;
                    for ($y = 2; $y <= $s_len - 2; $y = $y + 2) {
                        $nhead = substr($nw, $y - 2, 2);
                        $nfont = $cw . substr($nw, 0, $y - 2);
                        if ($this->isExistWord($nfont . $nhead)) {
                            if (strlen($cw) > 2)
                                $j++;
                            $hasDiff    = true;
                            $newarr[$j] = $nfont . $nhead;
                        }
                    }
                }
                $j++;
            }

        }//end for
        $smarr = $newarr;
    }

    /**
     * 转换最终分词结果到 finally_result 数组
     * @return void
     */
    private function sortFinallyResult() {
        $newarr = array();
        $i      = 0;
        foreach ($this->simple_result as $k => $v) {
            if (empty($v['w']))
                continue;
            if (isset($this->finally_result[$k]) && count($this->finally_result[$k]) > 0) {
                foreach ($this->finally_result[$k] as $w) {
                    if (!empty($w)) {
                        $newarr[$i]['w'] = $w;
                        $newarr[$i]['t'] = 20;
                        $i++;
                    }
                }
            } else if ($v['t'] != 21) {
                $newarr[$i]['w'] = $v['w'];
                $newarr[$i]['t'] = $v['t'];
                $i++;
            }
        }
        $this->finally_result = $newarr;
        $newarr               = '';
    }

    /**
     * 将utf-8字符串转换为usc2格式
     * @param $str
     * @return string
     */
    private function inStringEncode(&$str) {
        return mb_convert_encoding($str, self::UCS2, 'utf-8');
    }

    /**
     * 把uncode字符串转换为输出字符串
     * @parem str
     * return string
     */
    private function outStringEncoding(&$str) {
        return mb_convert_encoding($str, 'utf-8', self::UCS2);
    }

    /**
     * 获取最终结果字符串（用空格分开后的分词结果）
     * @param string $spword
     * @param bool $word_meanings
     * @return string
     */
    public function getFinallyResult($spword = ' ', $word_meanings = false) {
        $rsstr = '';
        foreach ($this->finally_result as $v) {
            if ($this->result_type == 2 && ($v['t'] == 3 || $v['t'] == 5)) {
                continue;
            }
            $m = '';
            if ($word_meanings) {
                $m = $this->getWordProperty($v['w']);
            }
            $w = $this->outStringEncoding($v['w']);
            if ($w != ' ') {
                if ($word_meanings) {
                    $rsstr .= $spword . $w . $m;
                } else {
                    $rsstr .= $spword . $w;
                }
            }
        }
        return $rsstr;
    }

    /**
     * 获取粗分结果，不包含粗分属性
     * @return array()
     */
    public function getSimpleResult() {
        $rearr = array();
        foreach ($this->simple_result as $k => $v) {
            if (empty($v['w']))
                continue;
            $w = $this->outStringEncoding($v['w']);
            if ($w != ' ')
                $rearr[] = $w;
        }
        return $rearr;
    }

    /**
     * 获取粗分结果，包含粗分属性（1中文词句、2 ANSI词汇（包括全角），3 ANSI标点符号（包括全角），4数字（包括全角），5 中文标点或无法识别字符）
     * @return array()
     */
    public function getSimpleResultAll() {
        $rearr = array();
        foreach ($this->simple_result as $k => $v) {
            $w = $this->outStringEncoding($v['w']);
            if ($w != ' ') {
                $rearr[$k]['w'] = $w;
                $rearr[$k]['t'] = $v['t'];
            }
        }
        return $rearr;
    }

    /**
     * 获取索引hash数组
     * @return array('word'=>count,...)
     */
    public function getFinallyIndex() {
        $rearr = array();
        foreach ($this->finally_result as $v) {
            if ($this->result_type == 2 && ($v['t'] == 3 || $v['t'] == 5)) {
                continue;
            }
            $w = $this->outStringEncoding($v['w']);
            if ($w == ' ') {
                continue;
            }
            if (isset($rearr[$w])) {
                $rearr[$w]++;
            } else {
                $rearr[$w] = 1;
            }
        }
        return $rearr;
    }

    /**
     * 析构函数
     */
    public function __destruct() {

    }

}
