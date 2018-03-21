<?php
/**
 * Created by PhpStorm.
 * User: SongKeJing
 * Email: 597481334@qq.com
 * Date: 2015/9/22
 * Time: 22:04
 */

namespace ClassLibrary\WX;

use ClassLibrary\ClArray;
use ClassLibrary\ClCache;
use ClassLibrary\ClFile;
use ClassLibrary\ClHttp;
use ClassLibrary\ClString;
use think\Exception;

/**
 * 微信类库
 * Class ClWeiXin
 * @package ClassLibrary
 */
class ClWX {

    /**
     * 缓存安全时间
     */
    const V_CACHE_SAFE_SECOND = 100;

    const URL = 'https://api.weixin.qq.com/cgi-bin';

    public static $app_id = '';

    public static $app_secret = '';

    /**
     * 微信请求过来的内容
     * @var string
     */
    public static $input = '';

    /**
     * access token
     * @var string
     */
    private static $access_token = '';

    /**
     * 校验
     * @param $token
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @return bool
     */
    public static function checkSignature($token, $signature, $timestamp, $nonce) {
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * POST数据
     * @param $url
     * @param $param
     * @param bool $post_file
     * @param string $result_type
     * @return bool|mixed
     * @throws Exception
     */
    public static function httpPost($url, $param = [], $post_file = false, $result_type = 'json') {
        $function_name = ClCache::getFunctionHistory(2);
        if (empty(self::$access_token)) {
            if (ClCache::getFunctionHistory(2) != 'ClWXGetAccessToken') {
                throw new Exception(sprintf('[function_name]: %s, %s', $function_name, '[请先调用]: ClWX::setAccessToken($app_id, $app_secret)'));
            }
        }
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        $strPOST = '';
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else if (!empty($param)) {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $output  = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            if ($result_type == 'json') {
                $r = json_decode($output, true);
                if (config('app_debug')) {
                    log_info([
                        'url'       => $url,
                        'params'    => $param,
                        'post_file' => $post_file,
                        'result'    => $r
                    ]);
                }
                if (isset($r['errcode']) && $r['errcode'] != 0) {
                    $msg = self::getErrorMsgByCode($r['errcode']);
                    if (empty($msg)) {
                        return sprintf('[function_name]: %s, [error_code]: %s, [error_msg]: %s', $function_name, $r['errcode'], $r['errmsg']);
                    } else {
                        return sprintf('[function_name]: %s, [error_code]: %s, [error_msg]: %s, [msg]: %s', $function_name, $r['errcode'], $r['errmsg'], $msg);
                    }
                } else {
                    return $r;
                }
            } else {
                return $output;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取所有的错误信息
     * @return array
     */
    public static function getAllErrorMsg() {
        return array(
            array('code' => -1, 'msg' => '系统繁忙，此时请开发者稍候再试'),
            array('code' => 0, 'msg' => '请求成功'),
            array('code' => 40001, 'msg' => '获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口'),
            array('code' => 40002, 'msg' => '不合法的凭证类型'),
            array('code' => 40003, 'msg' => '不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID'),
            array('code' => 40004, 'msg' => '不合法的媒体文件类型'),
            array('code' => 40005, 'msg' => '不合法的文件类型'),
            array('code' => 40006, 'msg' => '不合法的文件大小'),
            array('code' => 40007, 'msg' => '不合法的媒体文件id'),
            array('code' => 40008, 'msg' => '不合法的消息类型'),
            array('code' => 40009, 'msg' => '不合法的图片文件大小'),
            array('code' => 40010, 'msg' => '不合法的语音文件大小'),
            array('code' => 40011, 'msg' => '不合法的视频文件大小'),
            array('code' => 40012, 'msg' => '不合法的缩略图文件大小'),
            array('code' => 40013, 'msg' => '不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写'),
            array('code' => 40014, 'msg' => '不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口'),
            array('code' => 40015, 'msg' => '不合法的菜单类型'),
            array('code' => 40016, 'msg' => '不合法的按钮个数'),
            array('code' => 40017, 'msg' => '不合法的按钮个数'),
            array('code' => 40018, 'msg' => '不合法的按钮名字长度'),
            array('code' => 40019, 'msg' => '不合法的按钮KEY长度'),
            array('code' => 40020, 'msg' => '不合法的按钮URL长度'),
            array('code' => 40021, 'msg' => '不合法的菜单版本号'),
            array('code' => 40022, 'msg' => '不合法的子菜单级数'),
            array('code' => 40023, 'msg' => '不合法的子菜单按钮个数'),
            array('code' => 40024, 'msg' => '不合法的子菜单按钮类型'),
            array('code' => 40025, 'msg' => '不合法的子菜单按钮名字长度'),
            array('code' => 40026, 'msg' => '不合法的子菜单按钮KEY长度'),
            array('code' => 40027, 'msg' => '不合法的子菜单按钮URL长度'),
            array('code' => 40028, 'msg' => '不合法的自定义菜单使用用户'),
            array('code' => 40029, 'msg' => '不合法的oauth_code'),
            array('code' => 40030, 'msg' => '不合法的refresh_token'),
            array('code' => 40031, 'msg' => '不合法的openid列表'),
            array('code' => 40032, 'msg' => '不合法的openid列表长度'),
            array('code' => 40033, 'msg' => '不合法的请求字符，不能包含\uxxxx格式的字符'),
            array('code' => 40035, 'msg' => '不合法的参数'),
            array('code' => 40038, 'msg' => '不合法的请求格式'),
            array('code' => 40039, 'msg' => '不合法的URL长度'),
            array('code' => 40050, 'msg' => '不合法的分组id'),
            array('code' => 40051, 'msg' => '分组名字不合法'),
            array('code' => 40117, 'msg' => '分组名字不合法'),
            array('code' => 40118, 'msg' => 'media_id大小不合法'),
            array('code' => 40119, 'msg' => 'button类型错误'),
            array('code' => 40120, 'msg' => 'button类型错误'),
            array('code' => 40121, 'msg' => '不合法的media_id类型'),
            array('code' => 40132, 'msg' => '微信号不合法'),
            array('code' => 40137, 'msg' => '不支持的图片格式'),
            array('code' => 41001, 'msg' => '缺少access_token参数'),
            array('code' => 41002, 'msg' => '缺少appid参数'),
            array('code' => 41003, 'msg' => '缺少refresh_token参数'),
            array('code' => 41004, 'msg' => '缺少secret参数'),
            array('code' => 41005, 'msg' => '缺少多媒体文件数据'),
            array('code' => 41006, 'msg' => '缺少media_id参数'),
            array('code' => 41007, 'msg' => '缺少子菜单数据'),
            array('code' => 41008, 'msg' => '缺少oauth code'),
            array('code' => 41009, 'msg' => '缺少openid'),
            array('code' => 42001, 'msg' => 'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明'),
            array('code' => 42002, 'msg' => 'refresh_token超时'),
            array('code' => 42003, 'msg' => 'oauth_code超时'),
            array('code' => 43001, 'msg' => '需要GET请求'),
            array('code' => 43002, 'msg' => '需要POST请求'),
            array('code' => 43003, 'msg' => '需要HTTPS请求'),
            array('code' => 43004, 'msg' => '需要接收者关注'),
            array('code' => 43005, 'msg' => '需要好友关系'),
            array('code' => 44001, 'msg' => '多媒体文件为空'),
            array('code' => 44002, 'msg' => 'POST的数据包为空'),
            array('code' => 44003, 'msg' => '图文消息内容为空'),
            array('code' => 44004, 'msg' => '文本消息内容为空'),
            array('code' => 45001, 'msg' => '多媒体文件大小超过限制'),
            array('code' => 45002, 'msg' => '消息内容超过限制'),
            array('code' => 45003, 'msg' => '标题字段超过限制'),
            array('code' => 45004, 'msg' => '描述字段超过限制'),
            array('code' => 45005, 'msg' => '链接字段超过限制'),
            array('code' => 45006, 'msg' => '图片链接字段超过限制'),
            array('code' => 45007, 'msg' => '语音播放时间超过限制'),
            array('code' => 45008, 'msg' => '图文消息超过限制'),
            array('code' => 45009, 'msg' => '接口调用超过限制'),
            array('code' => 45010, 'msg' => '创建菜单个数超过限制'),
            array('code' => 45015, 'msg' => '回复时间超过限制'),
            array('code' => 45016, 'msg' => '系统分组，不允许修改'),
            array('code' => 45017, 'msg' => '分组名字过长'),
            array('code' => 45018, 'msg' => '分组数量超过上限'),
            array('code' => 46001, 'msg' => '不存在媒体数据'),
            array('code' => 46002, 'msg' => '不存在的菜单版本'),
            array('code' => 46003, 'msg' => '不存在的菜单数据'),
            array('code' => 46004, 'msg' => '不存在的用户'),
            array('code' => 47001, 'msg' => '解析JSON/XML内容错误'),
            array('code' => 48001, 'msg' => 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限'),
            array('code' => 50001, 'msg' => '用户未授权该api'),
            array('code' => 50002, 'msg' => '用户受限，可能是违规后接口被封禁'),
            array('code' => 61451, 'msg' => '参数错误(invalid parameter)'),
            array('code' => 61452, 'msg' => '无效客服账号(invalid kf_account)'),
            array('code' => 61453, 'msg' => '客服帐号已存在(kf_account exsited)'),
            array('code' => 61454, 'msg' => '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount length)'),
            array('code' => 61455, 'msg' => '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_account)'),
            array('code' => 61456, 'msg' => '客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)'),
            array('code' => 61457, 'msg' => '无效头像文件类型(invalid file type)'),
            array('code' => 61450, 'msg' => '系统错误(system error)'),
            array('code' => 61500, 'msg' => '日期格式错误'),
            array('code' => 61501, 'msg' => '日期范围错误'),
            array('code' => 9001001, 'msg' => 'POST数据参数不合法'),
            array('code' => 9001002, 'msg' => '远端服务不可用'),
            array('code' => 9001003, 'msg' => 'Ticket不合法'),
            array('code' => 9001004, 'msg' => '获取摇周边用户信息失败'),
            array('code' => 9001005, 'msg' => '获取商户信息失败'),
            array('code' => 9001006, 'msg' => '获取OpenID失败'),
            array('code' => 9001007, 'msg' => '上传文件缺失'),
            array('code' => 9001008, 'msg' => '上传素材的文件类型不合法'),
            array('code' => 9001009, 'msg' => '上传素材的文件尺寸不合法'),
            array('code' => 9001010, 'msg' => '上传失败'),
            array('code' => 9001020, 'msg' => '帐号不合法'),
            array('code' => 9001021, 'msg' => '已有设备激活率低于50%，不能新增设备'),
            array('code' => 9001022, 'msg' => '设备申请数不合法，必须为大于0的数字'),
            array('code' => 9001023, 'msg' => '已存在审核中的设备ID申请'),
            array('code' => 9001024, 'msg' => '一次查询设备ID数量不能超过50'),
            array('code' => 9001025, 'msg' => '设备ID不合法'),
            array('code' => 9001026, 'msg' => '页面ID不合法'),
            array('code' => 9001027, 'msg' => '页面参数不合法'),
            array('code' => 9001028, 'msg' => '一次删除页面ID数量不能超过10'),
            array('code' => 9001029, 'msg' => '页面已应用在设备中，请先解除应用关系再删除'),
            array('code' => 9001030, 'msg' => '一次查询页面ID数量不能超过50'),
            array('code' => 9001031, 'msg' => '时间区间不合法'),
            array('code' => 9001032, 'msg' => '保存设备与页面的绑定关系参数错误'),
            array('code' => 9001033, 'msg' => '门店ID不合法'),
            array('code' => 9001034, 'msg' => '设备备注信息过长'),
            array('code' => 9001035, 'msg' => '设备申请参数不合法'),
            array('code' => 9001036, 'msg' => '查询起始值begin不合法')
        );
    }

    /**
     * 获取内容
     * @return array|string
     * @throws \Exception
     */
    public static function getInput() {
        if (empty(self::$input)) {
            self::$input = file_get_contents("php://input");
            if (!empty(self::$input)) {
                self::$input = self::xml2data(self::$input);
            }
        }
        return self::$input;
    }

    /**
     * 获取错误信息
     * @param $code
     * @return string
     */
    public static function getErrorMsgByCode($code) {
        $codes = self::getAllErrorMsg();
        $msg   = '';
        foreach ($codes as $each) {
            if ($each['code'] == $code) {
                $msg = $each['msg'];
                break;
            }
        }
        return $msg;
    }

    /**
     * 获取access_token
     * @param $app_id
     * @param $app_secret
     * @return mixed
     */
    public static function getAccessToken($app_id, $app_secret) {
        $key = ClCache::getKey(1, $app_id, $app_secret);
        $r   = cache($key);
        if ($r !== false) {
            return $r;
        }
        $r = self::httpPost(self::URL . "/token?grant_type=client_credential&appid=$app_id&secret=$app_secret");
        //写入缓存
        cache($key, $r['access_token'], $r['expires_in'] - self::V_CACHE_SAFE_SECOND);
        return $r['access_token'];
    }

    /**
     * 设置access_token
     * @param $app_id
     * @param $app_secret
     */
    public static function setAccessToken($app_id, $app_secret) {
        self::$app_id       = $app_id;
        self::$app_secret   = $app_secret;
        self::$access_token = self::getAccessToken($app_id, $app_secret);
        if (config('app_debug')) {
            log_info('access_token:', self::$access_token);
        }
    }

    /**
     * 获取微信ips
     * @return mixed
     */
    public static function getServerIps() {
        $r = self::httpPost(self::URL . "/getcallbackip?access_token=" . self::$access_token);
        return $r['ip_list'];
    }

    /**
     * 数据XML编码
     * @param  object $xml XML对象
     * @param  mixed $data 数据
     * @param  string $item 数字索引时的节点名称
     * @return string
     */
    protected static function data2xml($xml, $data, $item = 'item') {
        foreach ($data as $key => $value) {
            /* 指定默认的数字key */
            is_numeric($key) && $key = $item;
            /* 添加子元素 */
            if (is_array($value) || is_object($value)) {
                $child = $xml->addChild($key);
                self::data2xml($child, $value, $item);
            } else {
                if (is_numeric($value)) {
                    $child = $xml->addChild($key, $value);
                } else {
                    $child = $xml->addChild($key);
                    $node  = dom_import_simplexml($child);
                    $cdata = $node->ownerDocument->createCDATASection($value);
                    $node->appendChild($cdata);
                }
            }
        }
    }

    /**
     * XML数据解码
     * @param $str :原始XML字符串
     * @return array
     */
    protected static function xml2data($str) {
        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $str, true)) {
            xml_parser_free($xml_parser);
            return $str;
        } else {
            $xml = new \SimpleXMLElement($str);
            if (!$xml) {
                return $str;
            }
            $data = array();
            foreach ($xml as $key => $value) {
                $data[$key] = strval($value);
            }
            return $data;
        }
    }

    /**
     * 消息回复
     * @param $msg
     */
    private static function msgReply($msg) {
        if (app_debug()) {
            log_info($msg);
        }
        exit($msg);
    }

    /**
     * 被动回复文本消息
     * @param $content :回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
     * @param string $to_user :接收方帐号（收到的OpenID）
     * @param string $from_user :开发者微信号
     */
    public static function msgReplyText($content, $to_user = '', $from_user = '') {
        $time = time();
        if (empty($to_user) || empty($from_user)) {
            $input     = self::getInput();
            $to_user   = $input['FromUserName'];
            $from_user = $input['ToUserName'];
        }
        self::msgReply("<xml>
<ToUserName><![CDATA[$to_user]]></ToUserName>
<FromUserName><![CDATA[$from_user]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[$content]]></Content>
</xml>");
    }

    /**
     * 被动回复图片消息
     * @param $img_media_id :通过素材管理接口上传多媒体文件，得到的id。
     * @param string $to_user :接收方帐号（收到的OpenID）
     * @param string $from_user :开发者微信号
     */
    public static function msgReplyImg($img_media_id, $to_user = '', $from_user = '') {
        $time = time();
        if (empty($to_user) || empty($from_user)) {
            $input     = self::getInput();
            $to_user   = $input['FromUserName'];
            $from_user = $input['ToUserName'];
        }
        self::msgReply("<xml>
<ToUserName><![CDATA[$to_user]]></ToUserName>
<FromUserName><![CDATA[$from_user]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[$img_media_id]]></MediaId>
</Image>
</xml>");
    }

    /**
     * 被动回复语音消息
     * @param $voice_media_id :通过素材管理接口上传多媒体文件，得到的id。
     * @param string $to_user :接收方帐号（收到的OpenID）
     * @param string $from_user :开发者微信号
     */
    public static function msgReplayVoice($voice_media_id, $to_user = '', $from_user = '') {
        $time = time();
        if (empty($to_user) || empty($from_user)) {
            $input     = self::getInput();
            $to_user   = $input['FromUserName'];
            $from_user = $input['ToUserName'];
        }
        self::msgReply("<xml>
<ToUserName><![CDATA[$to_user]]></ToUserName>
<FromUserName><![CDATA[$from_user]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
<Voice>
<MediaId><![CDATA[$voice_media_id]]></MediaId>
</Voice>
</xml>");
    }

    /**
     * 被动回复视频消息
     * @param $video_media_id :通过素材管理接口上传多媒体文件，得到的id。
     * @param string $title :视频消息的标题
     * @param string $desc :视频消息的描述
     * @param string $to_user :接收方帐号（收到的OpenID）
     * @param string $from_user :开发者微信号
     */
    public static function msgReplyVideo($video_media_id, $title = '', $desc = '', $to_user = '', $from_user = '') {
        $time = time();
        if (empty($to_user) || empty($from_user)) {
            $input     = self::getInput();
            $to_user   = $input['FromUserName'];
            $from_user = $input['ToUserName'];
        }
        self::msgReply("<xml>
<ToUserName><![CDATA[$to_user]]></ToUserName>
<FromUserName><![CDATA[$from_user]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
<Video>
<MediaId><![CDATA[$video_media_id]]></MediaId>
<Title><![CDATA[$title]]></Title>
<Description><![CDATA[$desc]]></Description>
</Video>
</xml>");
    }

    /**
     * 回复音乐消息
     * @param $to_user :接收方帐号（收到的OpenID）
     * @param $from_user :开发者微信号
     * @param string $title :音乐标题
     * @param string $desc :音乐描述
     * @param string $music_url :音乐链接
     * @param string $hq_music_url :高质量音乐链接，WIFI环境优先使用该链接播放音乐
     * @param string $thumb_media_id :缩略图的媒体id，通过素材管理接口上传多媒体文件，得到的id
     */
    public static function msgReplyMusic($title = '', $desc = '', $music_url = '', $hq_music_url = '', $thumb_media_id = '', $to_user = '', $from_user = '') {
        $time = time();
        if (empty($to_user) || empty($from_user)) {
            $input     = self::getInput();
            $to_user   = $input['FromUserName'];
            $from_user = $input['ToUserName'];
        }
        self::msgReply("<xml>
<ToUserName><![CDATA[$to_user]]></ToUserName>
<FromUserName><![CDATA[$from_user]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
<Music>
<Title><![CDATA[$title]]></Title>
<Description><![CDATA[$desc]]></Description>
<MusicUrl><![CDATA[$music_url]]></MusicUrl>
<HQMusicUrl><![CDATA[$hq_music_url]]></HQMusicUrl>
<ThumbMediaId><![CDATA[$thumb_media_id]]></ThumbMediaId>
</Music>
</xml>");
    }

    /**
     * 回复图文消息
     * @param array $articles = array(array('Title' => '图文消息标题', 'Description' => '图文消息描述', 'PicUrl' => '图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200', 'Url' => '点击图文消息跳转链接'))
     * @param string $to_user
     * @param string $from_user
     */
    public static function msgReplyArticles(array $articles, $to_user = '', $from_user = '') {
        $time = time();
        if (empty($to_user) || empty($from_user)) {
            $input     = self::getInput();
            $to_user   = $input['FromUserName'];
            $from_user = $input['ToUserName'];
        }
        $article_count = count($articles);
        $article_str   = '';
        foreach ($articles as $each) {
            $article_str .= sprintf('<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>', $each['Title'], $each['Description'], $each['PicUrl'], $each['Url']);
        }
        self::msgReply(sprintf('<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
%s
</Articles>
</xml>', $to_user, $from_user, $time, $article_count, $article_str));
    }

    /**
     * 客服url
     */
    const URL_CUSTOM = 'https://api.weixin.qq.com/customservice/kfaccount';

    /**
     * 返回信息code
     */
    const F_RETURN_ERR_CODE = 'errcode';

    /**
     * 返回信息msg
     */
    const F_RETURN_ERR_MSG = 'errmsg';

    /**
     * 添加客服账号，每个公众号最多添加10个客服账号
     * @param $kf_account :客服微信账号
     * @param $nickname :昵称
     * @param $password :密码
     * @return bool
     */
    public static function customerAdd($kf_account, $nickname, $password) {
        $r = self::httpPost(self::URL_CUSTOM . "/add?access_token=" . self::$access_token, array(
            'kf_account' => $kf_account,
            'nickname'   => $nickname,
            'password'   => $password
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 修改客服
     * @param $kf_account :客服微信账号
     * @param $nickname :昵称
     * @param $password :密码
     * @return bool
     */
    public static function customerUpdate($kf_account, $nickname, $password) {
        $r = self::httpPost(self::URL_CUSTOM . "/update?access_token=" . self::$access_token, array(
            'kf_account' => $kf_account,
            'nickname'   => $nickname,
            'password'   => $password
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 删除客服
     * @param $kf_account :客服微信账号
     * @param $nickname :昵称
     * @param $password :密码
     * @return bool
     */
    public static function customerDelete($kf_account, $nickname, $password) {
        $r = self::httpPost(self::URL_CUSTOM . "/del?access_token=" . self::$access_token, array(
            'kf_account' => $kf_account,
            'nickname'   => $nickname,
            'password'   => $password
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 设置客服帐号的头像
     * @param $kf_account
     * @param $head_img_absolute_url
     * @return mixed|string
     */
    public static function customerUploadHeadImg($kf_account, $head_img_absolute_url) {
        $result = ClHttp::uploadFile(self::URL_CUSTOM . "/uploadheadimg?access_token=" . self::$access_token . "&kf_account=$kf_account", array(), ClFile::getName($head_img_absolute_url), 'media', $head_img_absolute_url);
        return $result;
    }

    /**
     * 获取客服列表
     * @return mixed: array(array( "kf_account": "test1@test", "kf_nick": "ntest1", "kf_id": "1001", "kf_headimgurl": "http://mmbiz.qpic.cn/mmbiz/4whpV1VZl2iccsvYbHvnphkyGtnvjfUS8Ym0GSaLic0FD3vN0V8PILcibEGb2fPfEOmw/0"
     * ))
     */
    public static function customerList() {
        $r = self::httpPost(self::URL_CUSTOM . "/getkflist?access_token=" . self::$access_token);
        return $r['kf_list'];
    }

    /**
     * 客服发文本消息
     * @param $to_user :普通用户openid
     * @param $text
     * @param string $kf_account :客服账号，可为空
     * @return bool
     */
    public static function customerMsgSendText($to_user, $text, $kf_account = '') {
        $params = array(
            'touser'  => $to_user,
            'msgtype' => 'text',
            'text'    => array(
                'content' => $text
            )
        );
        if (!empty($kf_account)) {
            $params['customservice'] = array('kf_account' => $kf_account);
        }
        $r = self::httpPost(self::URL . "/message/custom/send?access_token=" . self::$access_token, $params);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 客服发图片消息
     * @param $to_user :普通用户openid
     * @param $img_media_id :发送的图片/语音/视频的媒体ID
     * @param string $kf_account :客服账号，可为空
     * @return bool
     */
    public static function customerMsgSendImg($to_user, $img_media_id, $kf_account = '') {
        $params = array(
            'touser'  => $to_user,
            'msgtype' => 'image',
            'image'   => array(
                'media_id' => $img_media_id
            )
        );
        if (!empty($kf_account)) {
            $params['customservice'] = array('kf_account' => $kf_account);
        }
        $r = self::httpPost(self::URL . "/message/custom/send?access_token=" . self::$access_token, $params);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 客服发语音消息
     * @param $to_user :普通用户openid
     * @param $voice_media_id :发送的图片/语音/视频的媒体ID
     * @param string $kf_account :客服账号，可为空
     * @return bool
     */
    public static function customerMsgSendVoice($to_user, $voice_media_id, $kf_account = '') {
        $params = array(
            'touser'  => $to_user,
            'msgtype' => 'voice',
            'voice'   => array(
                'media_id' => $voice_media_id
            )
        );
        if (!empty($kf_account)) {
            $params['customservice'] = array('kf_account' => $kf_account);
        }
        $r = self::httpPost(self::URL . "/message/custom/send?access_token=" . self::$access_token, $params);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 客服发视频消息
     * @param $to_user :普通用户openid
     * @param $video_media_id :发送的图片/语音/视频的媒体ID
     * @param $thumb_media_id :缩略图的媒体ID
     * @param $title :图文消息/视频消息/音乐消息的标题
     * @param $desc :图文消息/视频消息/音乐消息的描述
     * @param string $kf_account :客服账号，可为空
     * @return bool
     */
    public static function customerMsgSendVideo($to_user, $video_media_id, $thumb_media_id, $title, $desc, $kf_account = '') {
        $params = array(
            'touser'  => $to_user,
            'msgtype' => 'video',
            'video'   => array(
                'media_id'       => $video_media_id,
                'thumb_media_id' => $thumb_media_id,
                'title'          => $title,
                'description'    => $desc
            )
        );
        if (!empty($kf_account)) {
            $params['customservice'] = array('kf_account' => $kf_account);
        }
        $r = self::httpPost(self::URL . "/message/custom/send?access_token=" . self::$access_token, $params);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 发送音乐消息
     * @param $to_user :普通用户openid
     * @param $title :图文消息/视频消息/音乐消息的标题
     * @param $desc :图文消息/视频消息/音乐消息的描述
     * @param $music_url :音乐链接
     * @param $hd_music_url :高品质音乐链接，wifi环境优先使用该链接播放音乐
     * @param $thumb_media_id :缩略图的媒体ID
     * @param string $kf_account :客服账号，可为空
     * @return bool
     */
    public static function customerMsgSendMusic($to_user, $title, $desc, $music_url, $hd_music_url, $thumb_media_id, $kf_account = '') {
        $params = array(
            'touser'  => $to_user,
            'msgtype' => 'music',
            'music'   => array(
                'title'          => $title,
                'description'    => $desc,
                'musicurl'       => $music_url,
                'hqmusicurl'     => $hd_music_url,
                'thumb_media_id' => $thumb_media_id
            )
        );
        if (!empty($kf_account)) {
            $params['customservice'] = array('kf_account' => $kf_account);
        }
        $r = self::httpPost(self::URL . "/message/custom/send?access_token=" . self::$access_token, $params);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 发送图文消息
     * @param $to_user
     * @param $articles : array(array('title' => '标题', 'description' => '描述', 'url' => '图文消息被点击后跳转的链接 ', 'picurl' => '图文消息的图片链接，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80'))
     * @param string $kf_account :客服账号，可为空
     * @return bool
     */
    public static function customerMsgSendArticles($to_user, $articles, $kf_account = '') {
        $params = array(
            'touser'  => $to_user,
            'msgtype' => 'news',
            'news'    => array(
                'articles' => $articles
            )
        );
        if (!empty($kf_account)) {
            $params['customservice'] = array('kf_account' => $kf_account);
        }
        $r = self::httpPost(self::URL . "/message/custom/send?access_token=" . self::$access_token, $params);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 发送卡券
     * @param $to_user
     * @param $card_id
     * @param array $card_ext
     * @param string $kf_account :客服账号，可为空
     * @return bool
     */
    public static function customerMsgSendCard($to_user, $card_id, array $card_ext, $kf_account = '') {
        $params = array(
            'touser'  => $to_user,
            'msgtype' => 'wxcard',
            'wxcard'  => array(
                'card_id'  => $card_id,
                'card_ext' => ClArray::jsonUnicode($card_ext)
            )
        );
        if (!empty($kf_account)) {
            $params['customservice'] = array('kf_account' => $kf_account);
        }
        $r = self::httpPost(self::URL . "/message/custom/send?access_token=" . self::$access_token, $params);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 群发消息上传图文消息内的图片获取URL【订阅号与服务号认证后均可用】
     * @param $img_absolute_url :图片绝对地址，图片不占用公众号的素材库中图片数量的5000个的限制。图片仅支持jpg/png格式，大小必须在1MB以下。
     * @return mixed
     */
    public static function massMediaUploadImg($img_absolute_url) {
        $r = ClHttp::uploadFile(self::URL . "/media/uploadimg?access_token=" . self::$access_token, array(), 'media', ClFile::getName($img_absolute_url), $img_absolute_url);
        return $r['url'];
    }

    /**
     * 群发上传图文消息素材【订阅号与服务号认证后均可用】
     * @param array $articles :array(array(
     * 'thumb_media_id' => '图文消息缩略图的media_id，可以在基础支持-上传多媒体文件接口中获得',
     * 'title' => '图文消息的标题',
     * 'content' => '图文消息页面的内容，支持HTML标签',
     * 'content_source_url' => '在图文消息页面点击“阅读原文”后的页面，可为空',
     * 'author' => '图文消息的作者，可为空',
     * 'digest' => '图文消息描述，可为空',
     * 'show_cover_pic' => '是否显示封面，1为显示，0为不显示，可为空'
     * ))
     * @return mixed
     */
    public static function massMediaUploadArticles(array $articles) {
        $r = self::httpPost(self::URL . "/media/uploadnews?access_token=" . self::$access_token, array(
            'articles' => $articles
        ));
        return $r['media_id'];
    }

    /**
     * 获取群发消息的filter
     * @param $group_id
     * @return array
     */
    private static function massSendGetFilter($group_id) {
        $filter = array();
        if ($group_id == 0) {
            $filter = array('is_to_all' => true);
        } else {
            $filter = array(
                'is_to_all' => false,
                'group_id'  => $group_id
            );
        }
        return $filter;
    }

    /**
     * 群发图文消息
     * @param $article_media_id
     * @param int $group_id :默认0，则不分组发送，表示群发所有人员，否则是按分组发送消息
     * @return mixed
     */
    public static function massSendArticles($article_media_id, $group_id = 0) {
        return self::httpPost(self::URL . "/message/mass/sendall?access_token=" . self::$access_token, array(
            'filter'  => self::massSendGetFilter($group_id),
            'mpnews'  => array(
                'media_id' => $article_media_id
            ),
            'msgtype' => 'mpnews'
        ));
    }

    /**
     * 群发文本消息
     * @param $text_content
     * @param int $group_id :默认0，则不分组发送，表示群发所有人员，否则是按分组发送消息
     * @return mixed
     */
    public static function massSendText($text_content, $group_id = 0) {
        return self::httpPost(self::URL . "/message/mass/sendall?access_token=" . self::$access_token, array(
            'filter'  => self::massSendGetFilter($group_id),
            'text'    => array(
                'content' => $text_content
            ),
            'msgtype' => 'text'
        ));
    }

    /**
     * 群发语音消息
     * @param $voice_media_id :注意此处media_id需通过基础支持中的上传下载多媒体文件来得到
     * @param int $group_id :默认0，则不分组发送，表示群发所有人员，否则是按分组发送消息
     * @return mixed
     */
    public static function massSendVoice($voice_media_id, $group_id = 0) {
        return self::httpPost(self::URL . "/message/mass/sendall?access_token=" . self::$access_token, array(
            'filter'  => self::massSendGetFilter($group_id),
            'voice'   => array(
                'media_id' => $voice_media_id
            ),
            'msgtype' => 'voice'
        ));
    }

    /**
     * 群发图片消息
     * @param $img_media_id :注意此处media_id需通过基础支持中的上传下载多媒体文件来得到
     * @param int $group_id :默认0，则不分组发送，表示群发所有人员，否则是按分组发送消息
     * @return mixed
     */
    public static function massSendImg($img_media_id, $group_id = 0) {
        return self::httpPost(self::URL . "/message/mass/sendall?access_token=" . self::$access_token, array(
            'filter'  => self::massSendGetFilter($group_id),
            'image'   => array(
                'media_id' => $img_media_id
            ),
            'msgtype' => 'image'
        ));
    }

    /**
     * 群发视频消息
     * @param $video_media_id :注意此处media_id需通过基础支持中的上传下载多媒体文件来得到
     * @param $title
     * @param $desc
     * @param int $group_id :默认0，则不分组发送，表示群发所有人员，否则是按分组发送消息
     * @return mixed
     */
    public static function massSendVideo($video_media_id, $title, $desc, $group_id = 0) {
        //置换media_id
        $r = self::httpPost("https://file.api.weixin.qq.com/cgi-bin/media/uploadvideo?access_token=" . self::$access_token, array(
            'media_id'    => $video_media_id,
            'title'       => $title,
            'description' => $desc
        ));
        return self::httpPost(self::URL . "/message/mass/sendall?access_token=" . self::$access_token, array(
            'filter'  => self::massSendGetFilter($group_id),
            'mpvideo' => array(
                'media_id' => $r['media_id']
            ),
            'msgtype' => 'mpvideo'
        ));
    }

    /**
     * 群发卡券消息
     * @param $card_media_id
     * @param int $group_id :默认0，则不分组发送，表示群发所有人员，否则是按分组发送消息
     * @return mixed
     */
    public static function massSendCard($card_media_id, $group_id = 0) {
        return self::httpPost(self::URL . "/message/mass/sendall?access_token=" . self::$access_token, array(
            'filter'  => self::massSendGetFilter($group_id),
            'wxcard'  => array(
                'media_id' => $card_media_id
            ),
            'msgtype' => 'wxcard'
        ));
    }

    /**
     * 群发图文消息
     * @param $article_media_id
     * @param array $to_users :array(openid1, openid2)
     * @return mixed
     */
    public static function massSendArticlesToUsers($article_media_id, array $to_users) {
        return self::httpPost(self::URL . "/message/mass/send?access_token=" . self::$access_token, array(
            'touser'  => $to_users,
            'mpnews'  => array(
                'media_id' => $article_media_id
            ),
            'msgtype' => 'mpnews'
        ));
    }

    /**
     * 群发文本消息
     * @param $text_content
     * @param array $to_users :array(openid1, openid2)
     * @return mixed
     */
    public static function massSendTextToUsers($text_content, array $to_users) {
        return self::httpPost(self::URL . "/message/mass/send?access_token=" . self::$access_token, array(
            'touser'  => $to_users,
            'text'    => array(
                'content' => $text_content
            ),
            'msgtype' => 'text'
        ));
    }

    /**
     * 群发语音消息
     * @param $voice_media_id :注意此处media_id需通过基础支持中的上传下载多媒体文件来得到
     * @param array $to_users :array(openid1, openid2)
     * @return mixed
     */
    public static function massSendVoiceToUsers($voice_media_id, array $to_users) {
        return self::httpPost(self::URL . "/message/mass/send?access_token=" . self::$access_token, array(
            'touser'  => $to_users,
            'voice'   => array(
                'media_id' => $voice_media_id
            ),
            'msgtype' => 'voice'
        ));
    }

    /**
     * 群发图片消息
     * @param $img_media_id :注意此处media_id需通过基础支持中的上传下载多媒体文件来得到
     * @param array $to_users :array(openid1, openid2)
     * @return mixed
     */
    public static function massSendImgToUsers($img_media_id, array $to_users) {
        return self::httpPost(self::URL . "/message/mass/send?access_token=" . self::$access_token, array(
            'touser'  => $to_users,
            'image'   => array(
                'media_id' => $img_media_id
            ),
            'msgtype' => 'image'
        ));
    }

    /**
     * 群发视频消息
     * @param $video_media_id :注意此处media_id需通过基础支持中的上传下载多媒体文件来得到
     * @param $title
     * @param $desc
     * @param array $to_users :array(openid1, openid2)
     * @return mixed
     */
    public static function massSendVideoToUsers($video_media_id, $title, $desc, array $to_users) {
        //置换media_id
        $r = self::httpPost("https://file.api.weixin.qq.com/cgi-bin/media/uploadvideo?access_token=" . self::$access_token, array(
            'media_id'    => $video_media_id,
            'title'       => $title,
            'description' => $desc
        ));
        return self::httpPost(self::URL . "/message/mass/send?access_token=" . self::$access_token, array(
            'touser'  => $to_users,
            'mpvideo' => array(
                'media_id' => $r['media_id']
            ),
            'msgtype' => 'mpvideo'
        ));
    }

    /**
     * 群发卡券消息
     * @param $card_media_id
     * @param array $to_users :array(openid1, openid2)
     * @return mixed
     */
    public static function massSendCardToUsers($card_media_id, array $to_users) {
        return self::httpPost(self::URL . "/message/mass/send?access_token=" . self::$access_token, array(
            'touser'  => $to_users,
            'wxcard'  => array(
                'media_id' => $card_media_id
            ),
            'msgtype' => 'wxcard'
        ));
    }

    /**
     * 预览文本消息
     * @param $to_user
     * @param $content
     * @return mixed
     */
    public static function massPreviewText($to_user, $content) {
        $r = self::httpPost(self::URL . "/message/mass/preview?access_token=" . self::$access_token, array(
            'touser'  => $to_user,
            'text'    => array(
                'content' => $content
            ),
            'msgtype' => 'text'
        ));
        return $r['msg_id'];
    }

    /**
     * 预览图文消息
     * @param $to_user
     * @param $media_id :与根据分组群发中的media_id相同
     * @return mixed
     */
    public static function massPreviewArticles($to_user, $media_id) {
        $r = self::httpPost(self::URL . "/message/mass/preview?access_token=" . self::$access_token, array(
            'touser'  => $to_user,
            'mpnews'  => array(
                'media_id' => $media_id
            ),
            'msgtype' => 'mpnews'
        ));
        return $r['msg_id'];
    }

    /**
     * 预览语音消息
     * @param $to_user
     * @param $media_id :与根据分组群发中的media_id相同
     * @return mixed
     */
    public static function massPreviewVoice($to_user, $media_id) {
        $r = self::httpPost(self::URL . "/message/mass/preview?access_token=" . self::$access_token, array(
            'touser'  => $to_user,
            'voice'   => array(
                'media_id' => $media_id
            ),
            'msgtype' => 'voice'
        ));
        return $r['msg_id'];
    }

    /**
     * 预览图片消息
     * @param $to_user
     * @param $media_id :与根据分组群发中的media_id相同
     * @return mixed
     */
    public static function massPreviewImg($to_user, $media_id) {
        $r = self::httpPost(self::URL . "/message/mass/preview?access_token=" . self::$access_token, array(
            'touser'  => $to_user,
            'image'   => array(
                'media_id' => $media_id
            ),
            'msgtype' => 'image'
        ));
        return $r['msg_id'];
    }

    /**
     * 预览视频消息
     * @param $to_user
     * @param $media_id :与根据分组群发中的media_id相同
     * @return mixed
     */
    public static function massPreviewVideo($to_user, $media_id) {
        $r = self::httpPost(self::URL . "/message/mass/preview?access_token=" . self::$access_token, array(
            'touser'  => $to_user,
            'mpvideo' => array(
                'media_id' => $media_id
            ),
            'msgtype' => 'mpvideo'
        ));
        return $r['msg_id'];
    }

    /**
     * 预览卡券消息
     * @param $to_user
     * @param $card_id
     * @param $card_ext
     * @return mixed
     */
    public static function massPreviewCard($to_user, $card_id, $card_ext) {
        $r = self::httpPost(self::URL . "/message/mass/preview?access_token=" . self::$access_token, array(
            'touser'  => $to_user,
            'wxcard'  => array(
                'card_id'  => $card_id,
                'card_ext' => $card_ext
            ),
            'msgtype' => 'wxcard'
        ));
        return $r['msg_id'];
    }

    /**
     * 获取商业父级类型
     * @return array
     */
    public static function businessGetTypes() {
        return array(
            'IT科技',
            '金融业',
            '餐饮',
            '酒店旅游',
            '运输与仓储',
            '教育',
            '政府与公共事业',
            '医药护理',
            '交通工具',
            '房地产',
            '消费品',
            '商业服务',
            '文体娱乐',
            '印刷',
            '其它'
        );
    }

    /**
     * 获取商业子级类型
     * @param $father_business_type
     * @return array
     */
    public static function businessGetSonTypes($father_business_type) {
        $r = array(
            'IT科技'    => array(
                '互联网/电子商务',
                'IT软件与服务',
                'IT硬件与设备',
                '电子技术',
                '通信与运营商',
                '网络游戏'
            ),
            '金融业'     => array(
                '银行',
                '基金|理财|信托',
                '保险',
            ),
            '餐饮'      => array(
                '餐饮'
            ),
            '酒店旅游'    => array(
                '酒店',
                '旅游'
            ),
            '运输与仓储'   => array(
                '快递',
                '物流',
                '仓储'
            ),
            '教育'      => array(
                '培训',
                '院校'
            ),
            '政府与公共事业' => array(
                '学术科研',
                '交警',
                '博物馆',
                '公共事业|非盈利机构'
            ),
            '医药护理'    => array(
                '医药医疗',
                '护理美容',
                '保健与卫生'
            ),
            '交通工具'    => array(
                '汽车相关',
                '摩托车相关',
                '火车相关',
                '飞机相关'
            ),
            '房地产'     => array(
                '建筑',
                '物业'
            ),
            '消费品'     => array(
                '消费品'
            ),
            '商业服务'    => array(
                '法律',
                '会展',
                '中介服务',
                '认证',
                '审计'
            ),
            '文体娱乐'    => array(
                '传媒',
                '体育',
                '娱乐休闲'
            ),
            '印刷'      => array(
                '印刷'
            ),
            '其它'      => array(
                '其他'
            )
        );
        return isset($r[$father_business_type]) ? $r[$father_business_type] : array();
    }

    /**
     * 获取行业编码
     * @param $son_business_type
     * @return int
     */
    public static function businessGetCodeBySonType($son_business_type) {
        $r = array(
            "互联网/电子商务"   => 1,
            "IT软件与服务"    => 2,
            "IT硬件与设备"    => 3,
            "电子技术"       => 4,
            "通信与运营商"     => 5,
            "网络游戏"       => 6,
            "银行"         => 7,
            "基金|理财|信托"   => 8,
            "保险"         => 9,
            "餐饮"         => 10,
            "酒店"         => 11,
            "旅游"         => 12,
            "快递"         => 13,
            "物流"         => 14,
            "仓储"         => 15,
            "培训"         => 16,
            "院校"         => 17,
            "学术科研"       => 18,
            "交警"         => 19,
            "博物馆"        => 20,
            "公共事业|非盈利机构" => 21,
            "医药医疗"       => 22,
            "护理美容"       => 23,
            "保健与卫生"      => 24,
            "汽车相关"       => 25,
            "摩托车相关"      => 26,
            "火车相关"       => 27,
            "飞机相关"       => 28,
            "建筑"         => 29,
            "物业"         => 30,
            "消费品"        => 31,
            "法律"         => 32,
            "会展"         => 33,
            "中介服务"       => 34,
            "认证"         => 35,
            "审计"         => 36,
            "传媒"         => 37,
            "体育"         => 38,
            "娱乐休闲"       => 39,
            "印刷"         => 40,
            "其它"         => 41
        );
        return isset($r[$son_business_type]) ? $r[$son_business_type] : 0;
    }

    /**
     * 设置所属行业
     * @param $business_type_1
     * @param $business_type_2
     * @return mixed
     */
    public static function businessSet($business_type_1, $business_type_2) {
        $r = self::httpPost(self::URL . "/template/api_set_industry?access_token=" . self::$access_token, array(
            'industry_id1' => $business_type_1,
            'industry_id2' => $business_type_2
        ));
        return $r;
    }

    /**
     * 获取模板id
     * @param $template_id_short
     * @return mixed
     */
    private static function templateGet($template_id_short) {
        $r = self::httpPost(self::URL . "/template/api_add_template?access_token=" . self::$access_token, array(
            'template_id_short' => $template_id_short
        ));
        return $r['template_id'];
    }

    /**
     * 发送模板消息
     * @param $template_id
     * @param $to_user
     * @param $url
     * @param $data
     * @return bool
     */
    public static function templateSend($template_id, $to_user, $url, $data) {
        $r = self::httpPost(self::URL . "/message/template/send?access_token=" . self::$access_token, json_encode([
            'touser'      => $to_user,
            'template_id' => $template_id,
            'url'         => $url,
            'data'        => $data
        ], JSON_UNESCAPED_UNICODE));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? $r['msgid'] : false;
    }

    /**
     * 获取自动回复规则
     * is_add_friend_reply_open:关注后自动回复是否开启，0代表未开启，1代表开启
     * is_autoreply_open:消息自动回复是否开启，0代表未开启，1代表开启
     * add_friend_autoreply_info:关注后自动回复的信息
     * type:自动回复的类型。关注后自动回复和消息自动回复的类型仅支持文本（text）、图片（img）、语音（voice）、视频（video），关键词自动回复则还多了图文消息（news）
     * content:对于文本类型，content是文本内容，对于图文、图片、语音、视频类型，content是mediaID
     * message_default_autoreply_info:消息自动回复的信息
     * keyword_autoreply_info:关键词自动回复的信息
     * rule_name:规则名称
     * create_time:创建时间
     * reply_mode:回复模式，reply_all代表全部回复，random_one代表随机回复其中一条
     * keyword_list_info:匹配的关键词列表
     * match_mode:匹配模式，contain代表消息中含有该关键词即可，equal表示消息内容必须和关键词严格相同
     * news_info:图文消息的信息
     * title:图文消息的标题
     * digest:摘要
     * author:作者
     * show_cover:是否显示封面，0为不显示，1为显示
     * cover_url:封面图片的URL
     * content_url:正文的URL
     * source_url:原文的URL，若置空则无查看原文入口
     * @return mixed
     */
    public static function getCurrentAutoReplyInfo() {
        return self::httpPost(self::URL . "/get_current_autoreply_info?access_token=" . self::$access_token);
    }

    /**
     * 图片
     */
    const MEDIA_TYPE_IMAGE = 'image';

    /**
     * 语音
     */
    const MEDIA_TYPE_VOICE = 'voice';

    /**
     * 视频
     */
    const MEDIA_TYPE_VIDEO = 'video';

    /**
     * 缩略图
     */
    const MEDIA_TYPE_THUMB = 'thumb';

    /**
     * 上传临时性的多媒体素材
     * 图片（image）:1M，支持bmp/png/jpeg/jpg/gif格式
     * 语音（voice）:2M，播放长度不超过60s，支持AMR\MP3格式
     * 视频（video）:10MB，支持MP4格式
     * 缩略图（thumb）:64KB，支持bmp/png/jpeg/jpg/gif格式
     * @param $media_type
     * @param $media_absolute_url
     * @return mixed
     */
    public static function mediaUpload($media_type, $media_absolute_url) {
        $r = ClHttp::uploadFile("/media/upload?access_token=" . self::$access_token . "&type=$media_type", array(), $media_type, ClFile::getName($media_absolute_url), $media_absolute_url);
        return $r['media_id'];
    }

    /**
     * 下载临时多媒体接口
     * @param $media_id
     * @param $file_absolute_url :下载文件存储的绝对地址
     * @param bool|false $is_video :视频文件不支持https下载，调用该接口需http协议。
     */
    public static function mediaDown($media_id, $file_absolute_url, $is_video = false) {
        $url = self::URL . "/cgi-bin/media/get?access_token=" . self::$access_token . "&media_id=$media_id";
        if ($is_video) {
            $url = str_replace('https', 'http', $url);
        }
        Http::curlDownload($url, $file_absolute_url);
    }

    /**
     * 上传图片、语音、缩略图永久素材
     * @param $media_type :媒体文件类型
     * @param $media_absolute_file :媒体文件绝对地址
     * @return string
     */
    public static function materialUpload($media_type, $media_absolute_file) {
        $r = ClHttp::uploadFile(self::URL . "/material/add_material?access_token=" . self::$access_token . "&type=$media_type", array(), ClFile::getName($media_absolute_file), $media_absolute_file);
        return $r;
    }

    /**
     * 上传视频永久素材
     * @param $media_absolute_file :媒体文件绝对地址
     * @param $title
     * @param $desc
     * @return string
     */
    public static function materialUploadVideo($media_absolute_file, $title, $desc) {
        $r = ClHttp::uploadFile(self::URL . "/material/add_material?access_token=" . self::$access_token . "&type=" . self::MEDIA_TYPE_VIDEO, array(
            'description' => array(
                'title'        => $title,
                'introduction' => $desc
            )
        ), ClFile::getName($media_absolute_file), $media_absolute_file);
        return $r;
    }

    /**
     * 上传图文永久素材
     * @param $articles :array(array(
     * "title": 标题,
     * "thumb_media_id": 图文消息的封面图片素材id（必须是永久mediaID）,
     * "author": 作者,
     * "digest": 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空,
     * "show_cover_pic": 是否显示封面，0为false，即不显示，1为true，即显示,
     * "content": 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS,
     * "content_source_url": 图文消息的原文地址，即点击“阅读原文”后的URL
     * ))
     * @return mixed
     */
    public static function materialUploadArticles($articles) {
        $r = self::httpPost(self::URL . "/material/add_news?access_token=" . self::$access_token, array(
            'articles' => $articles
        ));
        return $r['media_id'];
    }

    /**
     * 修改永久图文素材
     * @param $media_id
     * @param $index
     * @param $articles :array(
     * "title": 标题,
     * "thumb_media_id": 图文消息的封面图片素材id（必须是永久mediaID）,
     * "author": 作者,
     * "digest": 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空,
     * "show_cover_pic": 是否显示封面，0为false，即不显示，1为true，即显示,
     * "content": 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS,
     * "content_source_url": 图文消息的原文地址，即点击“阅读原文”后的URL
     * )
     * @return bool|int
     */
    public static function materialUpdateArticles($media_id, $index, $articles) {
        $r = self::httpPost(self::URL . "/material/update_news?access_token=" . self::$access_token, array(
            'media_id' => $media_id,
            'index'    => $index,
            'articles' => $articles
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : 0;
    }

    /**
     * 获取永久素材，http://mp.weixin.qq.com/wiki/4/b3546879f07623cb30df9ca0e420a5d0.html
     * @param $media_id
     * @return mixed
     */
    public static function materialGet($media_id) {
        $r = self::httpPost(self::URL . "/material/get_material?access_token=" . self::$access_token, array(
            'media_id' => $media_id
        ));
        return $r;
    }

    /**
     * 永久素材删除，http://mp.weixin.qq.com/wiki/5/e66f61c303db51a6c0f90f46b15af5f5.html
     * @param $media_id
     * @return bool|int
     */
    public static function materialDelete($media_id) {
        $r = self::httpPost(self::URL . "/material/del_material?access_token=" . self::$access_token, array(
            'media_id' => $media_id
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : 0;
    }

    /**
     * 获取素材总数
     * @return mixed:array(
     * voice_count => 语音总数量,
     * video_count => 视频总数量,
     * image_count => 图片总数量,
     * news_count => 图文总数量
     * )
     */
    public static function materialGetCount() {
        $r = self::httpPost(self::URL . "/material/get_materialcount?access_token=" . self::$access_token);
        return $r;
    }

    /**
     * 获取素材列表，http://mp.weixin.qq.com/wiki/12/2108cd7aafff7f388f41f37efa710204.html
     * @param $type
     * @param $offset
     * @return mixed
     */
    public static function materialList($type, $offset) {
        $r = self::httpPost(self::URL . "/material/batchget_material?access_token=" . self::$access_token, array(
            'type'   => $type,
            'offset' => $offset,
            'count'  => 20
        ));
        return $r;
    }

    /**
     * 创建用户分组
     * @param $name :分组名字，UTF8编码
     * @return mixed:分组id，由微信分配
     */
    public static function userGroupsCreate($name) {
        $r = self::httpPost(self::URL . "/groups/create?access_token=" . self::$access_token, array(
            'group' => array(
                'name' => $name
            )
        ));
        return $r['group']['id'];
    }

    /**
     * 获取用户所有的分组
     * @return mixed
     */
    public static function userGroupsList() {
        $r = self::httpPost(self::URL . "/groups/get?access_token=" . self::$access_token);
        return $r['groups'];
    }

    /**
     * 获取用户所在分组
     * @param $open_id
     * @return mixed
     */
    public static function userGroupsSearchByOpenId($open_id) {
        $r = self::httpPost(self::URL . "/groups/getid?access_token=" . self::$access_token, array(
            'openid' => $open_id
        ));
        return $r['groupid'];
    }

    /**
     * 修改分组名称
     * @param $group_id
     * @param $group_name
     * @return bool
     */
    public static function userGroupUpdateName($group_id, $group_name) {
        $r = self::httpPost(self::URL . "/groups/update?access_token=" . self::$access_token, array(
            'group' => array(
                'id'   => $group_id,
                'name' => $group_name
            )
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 移动用户至新分组
     * @param $to_group_id
     * @param $open_id
     * @return bool
     */
    public static function userGroupMoveUser($to_group_id, $open_id) {
        $r = self::httpPost(self::URL . "/groups/members/update?access_token=" . self::$access_token, array(
            'openid'     => $open_id,
            'to_groupid' => $to_group_id
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 批量移动用户
     * @param $to_group_id
     * @param $open_ids :array(open_id1, open_id2)
     * @return bool
     */
    public static function userGroupMoveUsers($to_group_id, $open_ids) {
        $r = self::httpPost(self::URL . "/groups/members/batchupdate?access_token=" . self::$access_token, array(
            'openid_list' => $open_ids,
            'to_groupid'  => $to_group_id
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 删除分组
     * @param $group_id
     * @return bool
     */
    public static function userGroupDelete($group_id) {
        $r = self::httpPost(self::URL . "/groups/delete?access_token=" . self::$access_token, array(
            'group' => array(
                'id' => $group_id
            )
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 设置用户备注名称
     * @param $open_id
     * @param $remark
     * @return bool
     */
    public static function userInfoUpdateRemark($open_id, $remark) {
        $r = self::httpPost(self::URL . "/user/info/updateremark?access_token=" . self::$access_token, array(
            'openid' => $open_id,
            'remark' => $remark
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 获取用户信息
     * @param $open_id
     * @return mixed:array(
     * "subscribe": 1（用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。）,
     * "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M"（用户的标识，对当前公众号唯一）,
     * "nickname": "Band"（用户的昵称）,
     * "sex": 1（用户的性别，值为1时是男性，值为2时是女性，值为0时是未知）,
     * "language": "zh_CN",
     * "city": "广州",
     * "province": "广东",
     * "country": "中国",
     * "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0"（用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。）,
     * "subscribe_time": 1382694957（用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间）,
     * "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"（只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。）
     * "remark": ""（公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注）,
     * "groupid": 0（用户所在的分组ID）
     * )
     */
    public static function userInfoGet($open_id) {
        return self::httpPost(self::URL . "/user/info?access_token=" . self::$access_token . "&openid=$open_id&lang=zh_CN");
    }

    /**
     * 批量获取用户信息，最多支持一次拉取100条
     * @param $open_ids
     * @param string $lang :国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语，默认为zh-CN
     * @return mixed
     */
    public static function userInfoGetBatch($open_ids, $lang = 'zh-CN') {
        $user_list = array();
        foreach ($open_ids as $open_id) {
            $user_list[] = array(
                'openid' => $open_id,
                "lang"   => $lang
            );
        }
        unset($open_ids);
        $r = self::httpPost(self::URL . "/user/info/batchget?access_token=" . self::$access_token, array(
            'user_list' => $user_list
        ));
        return $r['user_info_list'];
    }

    /**
     * 公众号可通过本接口来获取帐号的关注者列表，关注者列表由一串OpenID（加密后的微信号，每个用户对每个公众号的OpenID是唯一的）组成。一次拉取调用最多拉取10000个关注者的OpenID，可以通过多次拉取的方式来满足需求。
     * @param string $next_open_id :第一个拉取的OPENID，不填默认从头开始拉取
     * @return mixed:array(
     * 'total' => 关注该公众账号的总用户数,
     * 'count' => 拉取的oepn_id个数，最大10000,
     * 'data' => 列表数据，OPENID的列表,
     * 'next_openid' => 拉取列表的最后一个用户的OPENID
     * )
     */
    public static function userListGet($next_open_id = '') {
        $url = empty($next_open_id) ? "/user/get?access_token=" . self::$access_token : "/user/get?access_token=" . self::$access_token . "&next_openid=$next_open_id";
        return self::httpPost(self::URL . $url);
    }

    /**
     * 网页获取获取code链接地址
     * @param $app_id :公众号的唯一标识
     * @param $redirect_url :授权后重定向的回调链接地址
     * @param $state :重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
     * @param string $scope :应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @return string
     */
    public static function webConnectGetCodeUrl($app_id, $redirect_url, $state, $scope = 'snsapi_base') {
        $redirect_url = urlencode($redirect_url);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$app_id&redirect_uri=$redirect_url&response_type=code&scope=$scope&state=$state#wechat_redirect";
    }

    /**
     * web获取用户open_id
     * @param $app_id
     * @param $app_secret
     * @param $code
     * @return string: open_id
     */
    public static function webGetOpenId($app_id, $app_secret, $code) {
        $r = self::httpPost("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$app_id&secret=$app_secret&code=$code&grant_type=authorization_code");
        return $r['openid'];
    }

    /**
     * 创建菜单：http://mp.weixin.qq.com/wiki/13/43de8269be54a0a6f64413e4dfa94f39.html
     * @param $buttons
     * @return bool
     */
    public static function menuCreate($buttons) {
        $r = self::httpPost(self::URL . "/menu/create?access_token=" . self::$access_token, ClArray::jsonUnicode([
            'button' => $buttons
        ]));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 获取按钮
     * @return mixed
     */
    public static function menuGet() {
        $r = self::httpPost(self::URL . "/menu/get?access_token=" . self::$access_token);
        return $r['menu']['button'];
    }

    /**
     * 删除按钮
     * @return bool
     */
    public static function menuDelete() {
        $r = self::httpPost(self::URL . "/menu/delete?access_token=" . self::$access_token);
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 获取菜单配置接口:http://mp.weixin.qq.com/wiki/17/4dc4b0514fdad7a5fbbd477aa9aab5ed.html
     * 1、第三方平台开发者可以通过本接口，在旗下公众号将业务授权给你后，立即通过本接口检测公众号的自定义菜单配置，并通过接口再次给公众号设置好自动回复规则，以提升公众号运营者的业务体验。
     * 2、本接口与自定义菜单查询接口的不同之处在于，本接口无论公众号的接口是如何设置的，都能查询到接口，而自定义菜单查询接口则仅能查询到使用API设置的菜单配置。
     * 3、认证/未认证的服务号/订阅号，以及接口测试号，均拥有该接口权限。
     * 4、从第三方平台的公众号登录授权机制上来说，该接口从属于消息与菜单权限集。
     * 5、本接口中返回的mediaID均为临时素材（通过素材管理-获取临时素材接口来获取这些素材），每次接口调用返回的mediaID都是临时的、不同的，在每次接口调用后3天有效，若需永久使用该素材，需使用素材管理接口中的永久素材。
     * @return mixed
     */
    public static function menuGetConfig() {
        return self::httpPost(self::URL . "/get_current_selfmenu_info?access_token=" . self::$access_token);
    }

    /**
     * 获取二维码
     * @param $scene_id :场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
     * @param int $expire_seconds :如果为0，则是永久二维码，否则为临时二维码失效时间，最长可以设置为在二维码生成后的7天（即604800秒）后过期
     * @param int $type : 1/返回图片地址，2/返回url内容地址，然后自己再生成二维码
     * @return mixed|string
     */
    public static function qrCodeGet($scene_id, $expire_seconds = 604800, $type = 1) {
        $key     = ClCache::getKey(1, $scene_id, $expire_seconds);
        $qr_info = cache($key);
        if (!empty($qr_info)) {
            if ($type == 1) {
                return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qr_info['ticket']);
            } else {
                return $qr_info['url'];
            }
        }
        if (!empty($expire_seconds)) {
            $post = [
                'expire_seconds' => $expire_seconds,
                'action_name'    => 'QR_SCENE',
                'action_info'    => [
                    'scene' => ['scene_id' => $scene_id]
                ]
            ];
        } else {
            $post = [
                'action_name' => 'QR_LIMIT_STR_SCENE',
                'action_info' => [
                    'scene' => ['scene_str' => $scene_id]
                ]
            ];
        }
        $qr_info = self::httpPost(self::URL . "/qrcode/create?access_token=" . self::$access_token, ClArray::jsonUnicode($post));
        if (empty($expire_seconds)) {
            cache($key, $qr_info);
        } else {
            cache($key, $qr_info, $expire_seconds);
        }
        if ($type == 1) {
            return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qr_info['ticket']);
        } else {
            return $qr_info['url'];
        }
    }

    /**
     * 长连接转短连接
     * @param $url :需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
     * @return mixed
     */
    public static function urlToShort($url) {
        $r = self::httpPost(self::URL . "/shorturl?access_token=" . self::$access_token, array(
            'action'   => 'long2short',
            'long_url' => $url
        ));
        return $r['short_url'];
    }

    /**
     * 创建门店
     * @param $sid :商户自己的id，用于后续审核通过收到poi_id 的通知时，做对应关系。请商户自己保证唯一识别性
     * @param $business_name :门店名称（仅为商户名，如：国美、麦当劳，不应包含地区、地址、分店名等信息，错误示例：北京国美）
     * @param $branch_name :分店名称（不应包含地区信息，不应与门店名有重复，错误示例：北京王府井店）
     * @param $province :门店所在的省份（直辖市填城市名,如：北京市）
     * @param $city :门店所在的城市
     * @param $district :门店所在地区
     * @param $address :门店所在的详细街道地址（不要填写省市信息）
     * @param $telephone :门店的电话（纯数字，区号、分机号均由“-”隔开）
     * @param $categories :array('美食', '小吃快餐')门店的类型（不同级分类用“,”隔开，如：美食，川菜，火锅。详细分类参见附件：微信门店类目表）
     * @param $longitude :门店所在地理位置的经度
     * @param $latitude :门店所在地理位置的纬度
     * @param $photo_list :array(array('photo_list' => img_url1), array('photo_list' => img_url2))图片列表，url 形式，可以有多张图片，尺寸为640*340px。必须为上一接口生成的url。图片内容不允许与门店不相关，不允许为二维码、员工合照（或模特肖像）、营业执照、无门店正门的街景、地图截图、公交地铁站牌、菜单截图等
     * @param $special :特色服务，如免费wifi，免费停车，送货上门等商户能提供的特色功能或服务
     * @param $introduction :商户简介，主要介绍商户信息等
     * @param $open_time :营业时间，24 小时制表示，用“-”连接，如 8:00-20:00
     * @param $avg_price :人均价格，大于0 的整数
     * @param $recommend :推荐品，餐厅可为推荐菜；酒店为推荐套房；景点为推荐游玩景点等，针对自己行业的推荐内容
     */
    public static function storeAdd($sid, $business_name, $branch_name, $province, $city, $district, $address, $telephone, $categories, $longitude, $latitude, $photo_list, $special, $introduction, $open_time, $avg_price, $recommend) {
        $r = self::httpPost(self::URL . "/poi/addpoi?access_token=" . self::$access_token, array(
            'business' => array(
                'base_info' => array(
                    "sid"           => $sid,
                    "business_name" => $business_name,
                    "branch_name"   => $branch_name,
                    "province"      => $province,
                    "city"          => $city,
                    "district"      => $district,
                    "address"       => $address,
                    "telephone"     => $telephone,
                    "categories"    => $categories,
                    "offset_type"   => 1,
                    "longitude"     => $longitude,
                    "latitude"      => $latitude,
                    "photo_list"    => $photo_list,
                    "special"       => $special,
                    "introduction"  => $introduction,
                    "open_time"     => $open_time,
                    "avg_price"     => $avg_price,
                    "recommend"     => $recommend
                )
            )
        ));
    }

    /**
     * 获取门店信息
     * @param $poi_id
     * @return mixed
     */
    public static function storeGet($poi_id) {
        $r = self::httpPost(self::URL . "/poi/getpoi?access_token=" . self::$access_token, array(
            'poi_id' => $poi_id
        ));
        return $r['business']['base_info'];
    }

    /**
     * 获取门店列表
     * @param int $begin
     * @param int $limit
     * @return mixed
     */
    public static function storeList($begin = 0, $limit = 50) {
        $r = self::httpPost(self::URL . "/poi/getpoilist?access_token=" . self::$access_token, array(
            'begin' => $begin,
            'limit' => $limit
        ));
        return $r;
    }

    /**
     * 更新门店信息
     * @param $poi_id :门店id
     * @param $telephone :电话
     * @param $photo_list :array(array('photo_list' => img_url1), array('photo_list' => img_url2))图片列表，url 形式，可以有多张图片，尺寸为640*340px。必须为上一接口生成的url。图片内容不允许与门店不相关，不允许为二维码、员工合照（或模特肖像）、营业执照、无门店正门的街景、地图截图、公交地铁站牌、菜单截图等
     * @param $recommend :推荐品，餐厅可为推荐菜；酒店为推荐套房；景点为推荐游玩景点等，针对自己行业的推荐内容
     * @param $special :特色服务，如免费wifi，免费停车，送货上门等商户能提供的特色功能或服务
     * @param $introduction :商户简介，主要介绍商户信息等
     * @param $open_time :营业时间，24 小时制表示，用“-”连接，如 8:00-20:00
     * @param $avg_price :人均价格，大于0 的整数
     * @return bool
     */
    public static function storeUpdate($poi_id, $telephone, $photo_list, $recommend, $special, $introduction, $open_time, $avg_price) {
        $r = self::httpPost(self::URL . "/poi/updatepoi?access_token=" . self::$access_token, array(
            'business' => array(
                'base_info' => array(
                    'poi_id'       => $poi_id,
                    'telephone'    => $telephone,
                    'photo_list'   => $photo_list,
                    'recommend'    => $recommend,
                    'special'      => $special,
                    'introduction' => $introduction,
                    'open_time'    => $open_time,
                    'avg_time'     => $avg_price
                )
            )
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 删除门店，请商户慎重调用该接口，门店信息被删除后，可能会影响其他与门店相关的业务使用，如卡券等。同样，该门店信息也不会在微信的商户详情页显示，不会再推荐入附近功能。
     * @param $poi_id
     * @return bool
     */
    public static function storeDelete($poi_id) {
        $r = self::httpPost(self::URL . "/poi/delpoi?access_token=" . self::$access_token, array(
            'poi_id' => $poi_id
        ));
        return $r[self::F_RETURN_ERR_CODE] == 0 ? true : false;
    }

    /**
     * 获取jsapi_ticket
     * @return mixed
     */
    public static function getJsApiTicket() {
        $key    = ClCache::getKey(1, self::$app_id, self::$app_secret);
        $ticket = cache($key);
        if (!empty($ticket)) {
            return $ticket;
        }
        $r = self::httpPost(sprintf(self::URL . "/ticket/getticket?access_token=%s&type=jsapi", self::$access_token));
        //存储
        cache($key, $r['ticket'], $r['expires_in'] - self::V_CACHE_SAFE_SECOND);
        return $r['ticket'];
    }

    /**
     * 获取sha1
     * @param $array
     * @return string
     */
    public static function getSHA1($array) {
        ksort($array, SORT_STRING);
        $s = '';
        foreach ($array as $k => $v) {
            if (empty($s)) {
                $s = $k . '=' . $v;
            } else {
                $s .= '&' . $k . '=' . $v;
            }
        }
        return strtoupper(sha1($s));
    }

    /**
     * 获取js sign
     * @param $url
     * @return array
     */
    public static function getJsSign($url) {
        $js_api_ticket = self::getJsApiTicket();
        $noncestr      = ClString::getRandomStr();
        $time          = time();
        return [
            "appid"     => self::$app_id,
            "noncestr"  => $noncestr,
            "timestamp" => $time,
            "url"       => $url,
            "signature" => self::getSHA1([
                'noncestr'     => $noncestr,
                'jsapi_ticket' => $js_api_ticket,
                'timestamp'    => $time,
                'url'          => $url
            ])
        ];
    }

    /**
     * url转换
     * @param $url
     * @return mixed
     */
    public static function urlMenuConvert($url) {
        if (strpos($url, '___') !== false || strpos($url, '---') !== false) {
            return str_replace(['___', '---'], ['/', '#'], $url);
        } else {
            return str_replace(['/', '#'], ['___', '---'], $url);
        }
    }

}
