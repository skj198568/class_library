<?php
/**
 * Created by PhpStorm.
 * User: SongKeJing
 * Email: 597481334@qq.com
 * Date: 2015/9/22
 * Time: 22:09
 */

namespace ClassLibrary\WX;

use ClassLibrary\ClArray;
use ClassLibrary\ClCache;
use ClassLibrary\ClHttp;
use ClassLibrary\ClString;
use Org\Net\Http;
use Think\Exception;

/**
 * 微信企业类库
 * Class ClWeiXinQiYe
 * @package ClassLibrary
 */
class ClWXQY
{

    /**
     * 缓存安全时间
     */
    const V_CACHE_SAFE_SECOND = 100;

    /**
     * 访问域名
     */
    const URL = 'https://qyapi.weixin.qq.com/cgi-bin';

    /**
     * 返回码
     */
    const F_ERR_CODE = 'errcode';

    /**
     * 返回码的描述
     */
    const F_ERR_MSG = 'errmsg';

    /**
     * 成员id
     */
    const F_USER_ID = 'userid';

    const F_NAME = 'name';

    const F_DEPARTMENT = 'department';

    const F_POSITION = 'position';

    const F_MOBILE = 'mobile';

    const F_GENDER = 'gender';

    const F_EMAIL = 'email';

    /**
     * 微信id
     */
    const F_WEI_XIN_ID = 'weixinid';

    const F_AVATAR = 'avatar';

    const F_STATUS = 'status';

    /**
     * 扩展属性
     */
    const F_EXT_ATTR = 'extattr';

    /**
     * 部门列表
     */
    const F_PARTY_LIST = 'partylist';

    /**
     * 媒体文件——图片
     */
    const V_MEDIA_IMAGE = 'image';

    /**
     * 媒体文件——语音
     */
    const V_MEDIA_VOICE = 'voice';

    /**
     * 媒体文件——视频
     */
    const V_MEDIA_VIDEO = 'video';

    /**
     * 媒体文件-普通文件
     */
    const V_MEDIA_FILE = 'file';

    /**
     * 获取所有的错误信息
     * @return array
     */
    public static function getAllErrorMsg()
    {
        return array(
            array('code' => -1, 'msg' => '系统繁忙'),
            array('code' => 0, 'msg' => '请求成功'),
            array('code' => 40001, 'msg' => '获取access_token时Secret错误，或者access_token无效'),
            array('code' => 40002, 'msg' => '不合法的凭证类型'),
            array('code' => 40003, 'msg' => '不合法的UserID'),
            array('code' => 40004, 'msg' => '不合法的媒体文件类型'),
            array('code' => 40005, 'msg' => '不合法的文件类型'),
            array('code' => 40006, 'msg' => '不合法的文件大小'),
            array('code' => 40007, 'msg' => '不合法的媒体文件id'),
            array('code' => 40008, 'msg' => '不合法的消息类型'),
            array('code' => 40013, 'msg' => '不合法的corpid'),
            array('code' => 40014, 'msg' => '不合法的access_token'),
            array('code' => 40015, 'msg' => '不合法的菜单类型'),
            array('code' => 40016, 'msg' => '不合法的按钮个数'),
            array('code' => 40017, 'msg' => '不合法的按钮类型'),
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
            array('code' => 40028, 'msg' => '不合法的自定义菜单使用成员'),
            array('code' => 40029, 'msg' => '不合法的oauth_code'),
            array('code' => 40031, 'msg' => '不合法的UserID列表'),
            array('code' => 40032, 'msg' => '不合法的UserID列表长度'),
            array('code' => 40033, 'msg' => '不合法的请求字符，不能包含\uxxxx格式的字符'),
            array('code' => 40035, 'msg' => '不合法的参数'),
            array('code' => 40038, 'msg' => '不合法的请求格式'),
            array('code' => 40039, 'msg' => '不合法的URL长度'),
            array('code' => 40040, 'msg' => '不合法的插件token'),
            array('code' => 40041, 'msg' => '不合法的插件id'),
            array('code' => 40042, 'msg' => '不合法的插件会话'),
            array('code' => 40048, 'msg' => 'url中包含不合法domain'),
            array('code' => 40054, 'msg' => '不合法的子菜单url域名'),
            array('code' => 40055, 'msg' => '不合法的按钮url域名'),
            array('code' => 40056, 'msg' => '不合法的agentid'),
            array('code' => 40057, 'msg' => '不合法的callbackurl'),
            array('code' => 40058, 'msg' => '不合法的红包参数'),
            array('code' => 40059, 'msg' => '不合法的上报地理位置标志位'),
            array('code' => 40060, 'msg' => '设置上报地理位置标志位时没有设置callbackurl'),
            array('code' => 40061, 'msg' => '设置应用头像失败'),
            array('code' => 40062, 'msg' => '不合法的应用模式'),
            array('code' => 40063, 'msg' => '参数为空'),
            array('code' => 40064, 'msg' => '管理组名字已存在'),
            array('code' => 40065, 'msg' => '不合法的管理组名字长度'),
            array('code' => 40066, 'msg' => '不合法的部门列表'),
            array('code' => 40067, 'msg' => '标题长度不合法'),
            array('code' => 40068, 'msg' => '不合法的标签ID'),
            array('code' => 40069, 'msg' => '不合法的标签ID列表'),
            array('code' => 40070, 'msg' => '列表中所有标签（成员）ID都不合法'),
            array('code' => 40071, 'msg' => '不合法的标签名字，标签名字已经存在'),
            array('code' => 40072, 'msg' => '不合法的标签名字长度'),
            array('code' => 40073, 'msg' => '不合法的openid'),
            array('code' => 40074, 'msg' => 'news消息不支持指定为高保密消息'),
            array('code' => 40077, 'msg' => '不合法的预授权码'),
            array('code' => 40078, 'msg' => '不合法的临时授权码'),
            array('code' => 40079, 'msg' => '不合法的授权信息'),
            array('code' => 40080, 'msg' => '不合法的suitesecret'),
            array('code' => 40082, 'msg' => '不合法的suitetoken'),
            array('code' => 40083, 'msg' => '不合法的suiteid'),
            array('code' => 40084, 'msg' => '不合法的永久授权码'),
            array('code' => 40085, 'msg' => '不合法的suiteticket'),
            array('code' => 40086, 'msg' => '不合法的第三方应用appid'),
            array('code' => 40092, 'msg' => '导入文件存在不合法的内容'),
            array('code' => 41001, 'msg' => '缺少access_token参数'),
            array('code' => 41002, 'msg' => '缺少corpid参数'),
            array('code' => 41003, 'msg' => '缺少refresh_token参数'),
            array('code' => 41004, 'msg' => '缺少secret参数'),
            array('code' => 41005, 'msg' => '缺少多媒体文件数据'),
            array('code' => 41006, 'msg' => '缺少media_id参数'),
            array('code' => 41007, 'msg' => '缺少子菜单数据'),
            array('code' => 41008, 'msg' => '缺少oauth code'),
            array('code' => 41009, 'msg' => '缺少UserID'),
            array('code' => 41010, 'msg' => '缺少url'),
            array('code' => 41011, 'msg' => '缺少agentid'),
            array('code' => 41012, 'msg' => '缺少应用头像mediaid'),
            array('code' => 41013, 'msg' => '缺少应用名字'),
            array('code' => 41014, 'msg' => '缺少应用描述'),
            array('code' => 41015, 'msg' => '缺少Content'),
            array('code' => 41016, 'msg' => '缺少标题'),
            array('code' => 41017, 'msg' => '缺少标签ID'),
            array('code' => 41018, 'msg' => '缺少标签名字'),
            array('code' => 41021, 'msg' => '缺少suiteid'),
            array('code' => 41022, 'msg' => '缺少suitetoken'),
            array('code' => 41023, 'msg' => '缺少suiteticket'),
            array('code' => 41024, 'msg' => '缺少suitesecret'),
            array('code' => 41025, 'msg' => '缺少永久授权码'),
            array('code' => 42001, 'msg' => 'access_token超时'),
            array('code' => 42002, 'msg' => 'refresh_token超时'),
            array('code' => 42003, 'msg' => 'oauth_code超时'),
            array('code' => 42004, 'msg' => '插件token超时'),
            array('code' => 42007, 'msg' => '预授权码失效'),
            array('code' => 42008, 'msg' => '临时授权码失效'),
            array('code' => 42009, 'msg' => 'suitetoken失效'),
            array('code' => 43001, 'msg' => '需要GET请求'),
            array('code' => 43002, 'msg' => '需要POST请求'),
            array('code' => 43003, 'msg' => '需要HTTPS'),
            array('code' => 43004, 'msg' => '需要成员已关注'),
            array('code' => 43005, 'msg' => '需要好友关系'),
            array('code' => 43006, 'msg' => '需要订阅'),
            array('code' => 43007, 'msg' => '需要授权'),
            array('code' => 43008, 'msg' => '需要支付授权'),
            array('code' => 43010, 'msg' => '需要处于回调模式'),
            array('code' => 43011, 'msg' => '需要企业授权'),
            array('code' => 43013, 'msg' => '应用对成员不可见'),
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
            array('code' => 45008, 'msg' => '图文消息的文章数量不能超过10条'),
            array('code' => 45009, 'msg' => '接口调用超过限制'),
            array('code' => 45010, 'msg' => '创建菜单个数超过限制'),
            array('code' => 45015, 'msg' => '回复时间超过限制'),
            array('code' => 45016, 'msg' => '系统分组，不允许修改'),
            array('code' => 45017, 'msg' => '分组名字过长'),
            array('code' => 45018, 'msg' => '分组数量超过上限'),
            array('code' => 45024, 'msg' => '账号数量超过上限'),
            array('code' => 45025, 'msg' => '同一个成员每周只能邀请一次'),
            array('code' => 45026, 'msg' => '触发删除用户数的保护'),
            array('code' => 45027, 'msg' => 'mpnews每天只能发送100次'),
            array('code' => 45028, 'msg' => '素材数量超过上限'),
            array('code' => 45029, 'msg' => 'media_id对该应用不可见'),
            array('code' => 46001, 'msg' => '不存在媒体数据'),
            array('code' => 46002, 'msg' => '不存在的菜单版本'),
            array('code' => 46003, 'msg' => '不存在的菜单数据'),
            array('code' => 46004, 'msg' => '不存在的成员'),
            array('code' => 47001, 'msg' => '解析JSON/XML内容错误'),
            array('code' => 48001, 'msg' => 'Api未授权'),
            array('code' => 48002, 'msg' => 'Api禁用'),
            array('code' => 48003, 'msg' => 'suitetoken无效'),
            array('code' => 48004, 'msg' => '授权关系无效'),
            array('code' => 50001, 'msg' => 'redirect_uri未授权'),
            array('code' => 50002, 'msg' => '成员不在权限范围'),
            array('code' => 50003, 'msg' => '应用已停用'),
            array('code' => 50004, 'msg' => '成员状态不正确，需要成员为企业验证中状态'),
            array('code' => 50005, 'msg' => '企业已禁用'),
            array('code' => 60001, 'msg' => '部门长度不符合限制'),
            array('code' => 60002, 'msg' => '部门层级深度超过限制'),
            array('code' => 60003, 'msg' => '部门不存在'),
            array('code' => 60004, 'msg' => '父亲部门不存在'),
            array('code' => 60005, 'msg' => '不允许删除有成员的部门'),
            array('code' => 60006, 'msg' => '不允许删除有子部门的部门'),
            array('code' => 60007, 'msg' => '不允许删除根部门'),
            array('code' => 60008, 'msg' => '部门名称已存在'),
            array('code' => 60009, 'msg' => '部门名称含有非法字符'),
            array('code' => 60010, 'msg' => '部门存在循环关系'),
            array('code' => 60011, 'msg' => '管理组权限不足，（user/department/agent）无权限'),
            array('code' => 60012, 'msg' => '不允许删除默认应用'),
            array('code' => 60013, 'msg' => '不允许关闭应用'),
            array('code' => 60014, 'msg' => '不允许开启应用'),
            array('code' => 60015, 'msg' => '不允许修改默认应用可见范围'),
            array('code' => 60016, 'msg' => '不允许删除存在成员的标签'),
            array('code' => 60017, 'msg' => '不允许设置企业'),
            array('code' => 60019, 'msg' => '不允许设置应用地理位置上报开关'),
            array('code' => 60020, 'msg' => '访问ip不在白名单之中'),
            array('code' => 60023, 'msg' => '应用已授权予第三方，不允许通过分级管理员修改菜单'),
            array('code' => 60102, 'msg' => 'UserID已存在'),
            array('code' => 60103, 'msg' => '手机号码不合法'),
            array('code' => 60104, 'msg' => '手机号码已存在'),
            array('code' => 60105, 'msg' => '邮箱不合法'),
            array('code' => 60106, 'msg' => '邮箱已存在'),
            array('code' => 60107, 'msg' => '微信号不合法'),
            array('code' => 60108, 'msg' => '微信号已存在'),
            array('code' => 60109, 'msg' => 'QQ号已存在'),
            array('code' => 60110, 'msg' => '用户同时归属部门超过20个'),
            array('code' => 60111, 'msg' => 'UserID不存在'),
            array('code' => 60112, 'msg' => '成员姓名不合法'),
            array('code' => 60113, 'msg' => '身份认证信息（微信号/手机/邮箱）不能同时为空'),
            array('code' => 60114, 'msg' => '性别不合法'),
            array('code' => 60115, 'msg' => '已关注成员微信不能修改'),
            array('code' => 60116, 'msg' => '扩展属性已存在'),
            array('code' => 60118, 'msg' => '成员无有效邀请字段，详情参考(邀请成员关注)的接口说明'),
            array('code' => 60119, 'msg' => '成员已关注'),
            array('code' => 60120, 'msg' => '成员已禁用'),
            array('code' => 60121, 'msg' => '找不到该成员'),
            array('code' => 60122, 'msg' => '邮箱已被外部管理员使用'),
            array('code' => 60123, 'msg' => '无效的部门id'),
            array('code' => 60124, 'msg' => '无效的父部门id'),
            array('code' => 60125, 'msg' => '非法部门名字，长度超过限制、重名等'),
            array('code' => 60126, 'msg' => '创建部门失败'),
            array('code' => 60127, 'msg' => '缺少部门id'),
            array('code' => 60128, 'msg' => '字段不合法，可能存在主键冲突或者格式错误'),
            array('code' => 80001, 'msg' => '可信域名没有IPC备案，后续将不能在该域名下正常使用jssdk'),
            array('code' => 82001, 'msg' => '发送消息或者邀请的参数全部为空或者全部不合法'),
            array('code' => 82002, 'msg' => '不合法的PartyID列表长度'),
            array('code' => 82003, 'msg' => '不合法的TagID列表长度'),
            array('code' => 82004, 'msg' => '微信版本号过低'),
            array('code' => 85002, 'msg' => '包含不合法的词语'),
            array('code' => 86001, 'msg' => '不合法的会话ID'),
            array('code' => 86003, 'msg' => '不存在的会话ID'),
            array('code' => 86004, 'msg' => '不合法的会话名'),
            array('code' => 86005, 'msg' => '不合法的会话管理员'),
            array('code' => 86006, 'msg' => '不合法的成员列表大小'),
            array('code' => 86007, 'msg' => '不存在的成员'),
            array('code' => 86101, 'msg' => '需要会话管理员权限'),
            array('code' => 86201, 'msg' => '缺少会话ID'),
            array('code' => 86202, 'msg' => '缺少会话名'),
            array('code' => 86203, 'msg' => '缺少会话管理员'),
            array('code' => 86204, 'msg' => '缺少成员'),
            array('code' => 86205, 'msg' => '非法的会话ID长度'),
            array('code' => 86206, 'msg' => '非法的会话ID数值'),
            array('code' => 86207, 'msg' => '会话管理员不在用户列表中'),
            array('code' => 86208, 'msg' => '消息服务未开启'),
            array('code' => 86209, 'msg' => '缺少操作者'),
            array('code' => 86210, 'msg' => '缺少会话参数'),
            array('code' => 86211, 'msg' => '缺少会话类型（单聊或者群聊）'),
            array('code' => 86213, 'msg' => '缺少发件人'),
            array('code' => 86214, 'msg' => '非法的会话类型'),
            array('code' => 86215, 'msg' => '会话已存在'),
            array('code' => 86216, 'msg' => '非法会话成员'),
            array('code' => 86217, 'msg' => '会话操作者不在成员列表中'),
            array('code' => 86218, 'msg' => '非法会话发件人'),
            array('code' => 86219, 'msg' => '非法会话收件人'),
            array('code' => 86220, 'msg' => '非法会话操作者'),
            array('code' => 86221, 'msg' => '单聊模式下，发件人与收件人不能为同一人'),
            array('code' => 86222, 'msg' => '不允许消息服务访问的API')
        );
    }

    /**
     * 获取错误信息
     * @param $code
     * @return string
     */
    public static function getErrorMsgByCode($code)
    {
        $codes = self::getAllErrorMsg();
        $msg = '';
        foreach ($codes as $each) {
            if ($each['code'] == $code) {
                $msg = $each['msg'];
                break;
            }
        }
        return $msg;
    }

    /**
     * 获取access token
     * @param $corp_id :企业Id
     * @param $secret :管理组的凭证密钥
     * @return mixed
     * @throws Exception
     */
    public static function getAccessToken($corp_id, $secret)
    {
        $key = ClCache::getKey(1, $corp_id, $secret);
        $result = cache($key);
        if ($result !== false) {
            return $result;
        }
        $result = ClHttp::http(self::URL . "/gettoken?corpid=$corp_id&corpsecret=$secret");
        if (isset($result['errcode'])) {
            throw new Exception(ClArray::toString($result));
        }
        //缓存
        cache($key, $result['access_token'], $result['expires_in'] - self::V_CACHE_SAFE_SECOND);
        //返回结果
        return $result['access_token'];
    }

    /**
     * 验证URL
     * @param $token
     * @param $encoding_aes_key
     * @param $corp_id
     * @param $msg_signature :签名串，对应URL参数的msg_signature
     * @param $timestamp :时间戳，对应URL参数的timestamp
     * @param $nonce :随机串，对应URL参数的nonce
     * @param $echo_str :随机串，对应URL参数的echostr
     * @param $reply_echo_str :解密之后的echostr，当return返回0时有效
     * @return int
     */
    public static function verifyURL($token, $encoding_aes_key, $corp_id, $msg_signature, $timestamp, $nonce, $echo_str, &$reply_echo_str)
    {
        if (strlen($encoding_aes_key) != 43) {
            return ErrorCode::$IllegalAesKey;
        }
        //verify msg_signature
        $array = SHA1::getSHA1($token, $timestamp, $nonce, $echo_str);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];
        if ($signature != $msg_signature) {
            return ErrorCode::$ValidateSignatureError;
        }
        $result = PrpCrypt::decrypt($encoding_aes_key, $echo_str, $corp_id);
        if ($result[0] != 0) {
            return $result[0];
        }
        $reply_echo_str = $result[1];
        return ErrorCode::$OK;
    }

    /**
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param $replyMsg string 公众平台待回复用户的消息，xml格式的字符串
     * @param $timeStamp string 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @param $nonce string 随机串，可以自己生成，也可以用URL参数的nonce
     * @param &$encryptMsg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
     *                      当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public static function encryptMsg($token, $encoding_aes_key, $corp_id, $reply_msg, $timestamp, $nonce, &$encrypt_msg)
    {
        //加密
        $array = PrpCrypt::encrypt($encoding_aes_key, $reply_msg, $corp_id);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        if ($timestamp == null) {
            $timestamp = time();
        }
        $encrypt = $array[1];
        //生成安全签名
        $array = SHA1::getSHA1($token, $timestamp, $nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];
        //生成发送的xml
        $encrypt_msg = XMLParse::generate($encrypt, $signature, $timestamp, $nonce);
        return ErrorCode::$OK;
    }


    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     * @param $msgSignature :string 签名串，对应URL参数的msg_signature
     * @param $timestamp :string 时间戳 对应URL参数的timestamp
     * @param $nonce :string 随机串，对应URL参数的nonce
     * @param $postData :string 密文，对应POST请求的数据
     * @param &$msg string:解密后的原文，当return返回0时有效
     * @return int 成功0，失败返回对应的错误码
     */
    public static function decryptMsg($token, $encoding_aes_key, $corp_id, $msg_signature, $timestamp = null, $nonce, $post_data, &$msg)
    {
        if (strlen($encoding_aes_key) != 43) {
            return ErrorCode::$IllegalAesKey;
        }
        //提取密文
        $array = XMLParse::extract($post_data);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        if ($timestamp == null) {
            $timestamp = time();
        }
        $encrypt = $array[1];
        $to_user_name = $array[2];
        //验证安全签名
        $array = SHA1::getSHA1($token, $timestamp, $nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];
        if ($signature != $msg_signature) {
            return ErrorCode::$ValidateSignatureError;
        }
        $result = PrpCrypt::decrypt($encoding_aes_key, $encrypt, $corp_id);
        if ($result[0] != 0) {
            return $result[0];
        }
        $msg = $result[1];
        return ErrorCode::$OK;
    }

    /**
     * 获取微信服务器的ip段
     * @param $access_token
     * @return mixed
     */
    public static function getServerIps($access_token)
    {
        $ips = ClHttp::http(self::URL . '/getcallbackip?access_token=' . $access_token, array(), 3600);
        return $ips['ip_list'];
    }

    /**
     * 创建部门
     * @param $access_token
     * @param $name :部门名称。长度限制为1~64个字节，字符不能包括\:*?"<>｜
     * @param $parent_id :父亲部门id。根部门id为1
     * @param $order :在父部门中的次序值。order值小的排序靠前。
     * @param $id :部门id，整型。指定时必须大于1，不指定时则自动生成
     * @return array("errcode": 0, "errmsg": "created", "id": 2)
     * @return bool|int 成功返回部门id，失败返回false
     */
    public static function departmentCreate($access_token, $id, $name, $parent_id, $order)
    {
        $r = ClHttp::http(self::URL . '/department/create?access_token=' . $access_token, array(
            'name' => $name,
            'parentid' => $parent_id,
            'order' => $order,
            'id' => $id
        ));
        if ($r['errcode'] == 0) {
            return $r['id'];
        } else {
            return false;
        }
    }

    /**
     * @param $access_token
     * @param $id :部门id(必须)
     * @param string $name :更新的部门名称。长度限制为1~64个字节，字符不能包括\:*?"<>｜。修改部门名称时指定该参数
     * @param string $parent_id :父亲部门id。根部门id为1
     * @param string $order :在父部门中的次序值。order值小的排序靠前。
     * @return boolean
     */
    public static function departmentUpdate($access_token, $id, $name = '', $parent_id = '', $order = '')
    {
        $post = array(
            'id' => $id
        );
        if (!empty($name)) {
            $post['name'] = $name;
        }
        if (!empty($parent_id)) {
            $post['parentid'] = $parent_id;
        }
        if (!empty($order)) {
            $post['order'] = $order;
        }
        $r = ClHttp::http(self::URL . '/department/update?access_token=' . $access_token, $post);
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 删除部门
     * @param $access_token
     * @param $id :部门id
     * @return boolean
     */
    public static function departmentDelete($access_token, $id)
    {
        $r = ClHttp::http(self::URL . '/department/delete?access_token=' . $access_token, array('id' => $id));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 获取部门及其下的子部门
     * @param $access_token
     * @param $id
     * @return bool/array
     */
    public static function departmentGet($access_token, $id)
    {
        $r = ClHttp::http(self::URL . '/department/list?access_token=' . $access_token, array('id' => $id));
        return $r['errcode'] == 0 ? $r['department'] : false;
    }

    /**
     * 性别转换
     * @param $gender
     * @return int
     */
    public static function getSexFromGender($gender)
    {
        return $gender == 1 ? 1 : 0;
    }

    /**
     * 性别转换
     * @param $sex
     * @return int
     */
    public static function getGenderFormSex($sex)
    {
        return $sex == 1 ? 1 : 2;
    }

    /**
     * 创建成员(http://qydev.weixin.qq.com/wiki/index.php?title=%E7%AE%A1%E7%90%86%E6%88%90%E5%91%98)
     * @param $access_token
     * @param $user_id :成员UserID。对应管理端的帐号，企业内必须唯一。长度为1~64个字节
     * @param $name :成员名称。长度为1~64个字节
     * @param array $department :array(1, 2),成员所属部门id列表。注意，每个部门的直属成员上限为1000个
     * @param string $position :职位信息。长度为0~64个字节
     * @param string $mobile :手机号码。企业内必须唯一，mobile/wei_xin_id/email三者不能同时为空
     * @param int $sex :性别，1男，2女
     * @param string $email :邮箱。长度为0~64个字节。企业内必须唯一
     * @param $wei_xin_id :微信号。企业内必须唯一。（注意：是微信号，不是微信的名字）
     * @param $avatar_media_id :成员头像的mediaid，通过多媒体接口上传图片获得的mediaid
     * @param $ext_attr :扩展属性。扩展属性需要在WEB管理端创建后才生效，否则忽略未知属性的赋值，array(array('name' => '', 'value' => ''), array('name' => '', 'value' => ''))
     * @return bool
     */
    public static function userCreate($access_token, $user_id, $name, $department = array(), $position = '', $mobile = '', $sex = 1, $email = '', $wei_xin_id = '', $avatar_media_id = '', $ext_attr = array())
    {
        $post = array(
            'userid' => $user_id,
            'name' => $name,
            'department' => $department,
            'mobile' => $mobile,
            'gender' => self::getGenderFormSex($sex),
            'email' => $email,
            'winxinid' => $wei_xin_id,
            'avatar_mediaid' => $avatar_media_id,
            'extattr' => array('attrs' => $ext_attr)
        );
        $r = ClHttp::http(self::URL . '/user/create?access_token=' . $access_token, $post);
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 更新用户
     * @param $access_token
     * @param $user_id :成员UserID。对应管理端的帐号，企业内必须唯一。长度为1~64个字节
     * @param $name :成员名称。长度为1~64个字节
     * @param array $department :array(1, 2),成员所属部门id列表。注意，每个部门的直属成员上限为1000个
     * @param string $position :职位信息。长度为0~64个字节
     * @param string $mobile :手机号码。企业内必须唯一，mobile/wei_xin_id/email三者不能同时为空
     * @param int $sex :性别，1男，2女
     * @param string $email :邮箱。长度为0~64个字节。企业内必须唯一
     * @param $wei_xin_id :微信号。企业内必须唯一。（注意：是微信号，不是微信的名字）
     * @param $avatar_media_id :成员头像的mediaid，通过多媒体接口上传图片获得的mediaid
     * @param $ext_attr :扩展属性。扩展属性需要在WEB管理端创建后才生效，否则忽略未知属性的赋值，array(array('name' => '', 'value' => ''), array('name' => '', 'value' => ''))
     * @return bool
     */
    public static function userUpdate($access_token, $user_id, $name, $department = array(), $position = '', $mobile = '', $sex = 1, $email = '', $wei_xin_id = '', $avatar_media_id = '', $ext_attr = array())
    {
        $post = array(
            'userid' => $user_id,
            'name' => $name,
            'department' => $department,
            'mobile' => $mobile,
            'gender' => self::getGenderFormSex($sex),
            'email' => $email,
            'winxinid' => $wei_xin_id,
            'avatar_mediaid' => $avatar_media_id,
            'extattr' => array('attrs' => $ext_attr)
        );
        $r = ClHttp::http(self::URL . '/user/update?access_token=' . $access_token, $post);
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 删除用户
     * @param $access_token
     * @param $user_id
     * @return bool
     */
    public static function userDelete($access_token, $user_id)
    {
        $r = ClHttp::http(self::URL . '/user/delete?access_token=' . $access_token, array(
            'userid' => $user_id
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 批量删除用户
     * @param $access_token
     * @param $user_ids : array(1, 2, 3)
     * @return bool
     */
    public static function userDeleteList($access_token, $user_ids)
    {
        $r = ClHttp::http(self::URL . '/user/batchdelete?access_token=' . $access_token, array(
            'useridlist' => $user_ids
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 获取用户
     * @param $access_token
     * @param $user_id
     * @return bool|array()
     */
    public static function userGet($access_token, $user_id)
    {
        $r = ClHttp::http(self::URL . '/user/get?access_token=' . $access_token . '&userid=' . $user_id);
        return $r['errcode'] == 0 ? $r : false;
    }

    /**
     * 按部门获取用户
     * @param $access_token
     * @param $department_id :部门id
     * @param int $fetch_child :1/0：是否递归获取子部门下面的成员
     * @param int $status :0获取全部成员，1获取已关注成员列表，2获取禁用成员列表，4获取未关注成员列表。status可叠加，未填写则默认为4
     * @param bool $is_simple : 是否是获取简单信息，否，则获取用户所有信息
     * @return bool
     */
    public static function userGetByDepartment($access_token, $department_id, $fetch_child = 1, $status = 0, $is_simple = true)
    {
        $action = $is_simple ? 'simplelist' : 'list';
        $r = ClHttp::http(self::URL . "/user/$action?access_token=$access_token&department_id=$department_id&fetch_child=$fetch_child&status=$status");
        return $r['errcode'] == 0 ? $r['userlist'] : false;
    }

    /**
     * 邀请成员关注，认证号优先使用微信推送邀请关注，如果没有weixinid字段则依次对手机号，邮箱绑定的微信进行推送，全部没有匹配则通过邮件邀请关注。 邮箱字段无效则邀请失败。 非认证号只通过邮件邀请关注。邮箱字段无效则邀请失败。 已关注以及被禁用成员不允许发起邀请关注请求。为避免骚扰成员，企业应遵守以下邀请规则：每月邀请的总人次不超过成员上限的2倍；每7天对同一个成员只能邀请一次。（http://qydev.weixin.qq.com/wiki/index.php?title=%E7%AE%A1%E7%90%86%E6%88%90%E5%91%98#.E9.82.80.E8.AF.B7.E6.88.90.E5.91.98.E5.85.B3.E6.B3.A8）
     * @param $access_token
     * @param $user_id
     * @return bool|int 1:微信邀请 2.邮件邀请
     */
    public static function userInvite($access_token, $user_id)
    {
        $r = ClHttp::http(self::URL . "/invite/send?access_token=$access_token", array(
            'userid' => $user_id
        ));
        return $r['errcode'] == 0 ? $r['type'] : false;
    }

    /**
     * 创建标签
     * @param $access_token :调用接口凭证
     * @param $tag_id :标签id，整型，指定此参数时新增的标签会生成对应的标签id，不指定时则以目前最大的id自增。
     * @param $tag_name :标签名称，长度为1~64个字节，标签名不可与其他标签重名。
     * @return bool|int
     */
    public static function tagCreate($access_token, $tag_id, $tag_name)
    {
        $r = ClHttp::http(self::URL . "/tag/create?access_token=$access_token", array(
            'tagname' => $tag_name,
            'tagid' => $tag_id
        ));
        return $r['errcode'] == 0 ? $r['tagid'] : false;
    }

    /**
     * 更新标签
     * @param $access_token :调用接口凭证
     * @param $tag_id :标签id，整型，指定此参数时新增的标签会生成对应的标签id，不指定时则以目前最大的id自增。
     * @param $tag_name :标签名称，长度为1~64个字节，标签名不可与其他标签重名。
     * @return bool
     */
    public static function tagUpdate($access_token, $tag_id, $tag_name)
    {
        $r = ClHttp::http(self::URL . "/tag/update?access_token=$access_token", array(
            'tagname' => $tag_name,
            'tagid' => $tag_id
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 删除标签
     * @param $access_token
     * @param $tag_id :标签id
     * @return bool
     */
    public static function tagDelete($access_token, $tag_id)
    {
        $r = ClHttp::http(self::URL . "/tag/delete?access_token=$access_token&tagid=$tag_id");
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 获取标签成员
     * @param $access_token
     * @param $tag_id
     * @return bool|array:array('partylist' => '部门列表')
     */
    public static function tagGet($access_token, $tag_id)
    {
        $r = ClHttp::http(self::URL . "/tag/get?access_token=$access_token&tagid=$tag_id");
        return $r['errcode'] == 0 ? $r : false;
    }

    /**
     * 增加标签成员
     * @param $access_token
     * @param $tag_id
     * @param array $user_list :企业成员ID列表，array(1, 3)，注意：userlist、partylist不能同时为空，单次请求长度不超过1000
     * @param array $party_list :企业部门ID列表，array(3, 5)，注意：userlist、partylist不能同时为空，单次请求长度不超过100
     * @return bool
     */
    public static function tagUsersAdd($access_token, $tag_id, array $user_list, array $party_list)
    {
        $r = ClHttp::http(self::URL . "/tag/addtagusers?access_token=$access_token", array(
            'tagid' => $tag_id,
            'userlist' => $user_list,
            'partylist' => $party_list
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 删除标签成员
     * @param $access_token
     * @param $tag_id
     * @param array $user_list :企业成员ID列表，array(1, 3)，注意：userlist、partylist不能同时为空，单次请求长度不超过1000
     * @param array $party_list :企业部门ID列表，array(3, 5)，注意：userlist、partylist不能同时为空，单次请求长度不超过100
     * @return bool
     */
    public static function tagUsersDelete($access_token, $tag_id, array $user_list, array $party_list)
    {
        $r = ClHttp::http(self::URL . "/tag/deltagusers?access_token=$access_token", array(
            'tagid' => $tag_id,
            'userlist' => $user_list,
            'partylist' => $party_list
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 获取标签列表
     * @param $access_token
     * @return bool|array
     */
    public static function tagList($access_token)
    {
        $r = ClHttp::http(self::URL . "/tag/list?access_token=$access_token");
        return $r['errcode'] == 0 ? $r['taglist'] : false;
    }

    /**
     * 异步任务——邀请成员关注
     * @param $access_token
     * @param array $to_users :成员ID列表
     * @param array $to_parties :部门ID列表
     * @param array $to_tags :标签ID列表
     * @param $call_back_url :企业应用接收企业号推送请求的访问协议和地址，支持http或https协议
     * @param $call_back_token :用于生成签名
     * @param $call_back_encoding_aes_key :用于消息体的加密，是AES密钥的Base64编码
     * @return bool
     */
    public static function batchInvite($access_token, array $to_users, array $to_parties, array $to_tags, $call_back_url, $call_back_token, $call_back_encoding_aes_key)
    {
        $r = ClHttp::http(self::URL . "/batch/inviteuser?access_token=$access_token", array(
            'touser' => implode('|', $to_users),
            'toparty' => implode('|', $to_parties),
            'totag' => implode('|', $to_tags),
            'callback' => array(
                'url' => $call_back_url,
                'token' => $call_back_token,
                'encodingaeskey' => $call_back_encoding_aes_key
            )
        ));
        return $r['errcode'] == 0 ? $r['taglist'] : false;
    }

    /**
     * 上传临时素材文件(3天有效)
     * 所有文件size必须大于5个字节
     * 图片（image）:2MB，支持JPG,PNG格式
     * 语音（voice）：2MB，播放长度不超过60s，支持AMR格式
     * 视频（video）：10MB，支持MP4格式
     * 普通文件（file）：20MB
     * @param $access_token
     * @param $type :媒体文件类型，分别有图片（image）、语音（voice）、视频（video），普通文件(file)
     * @param $name
     * @param $filename
     * @param $file_absolute_url
     * @return bool|mixed array('type' => '文件类型', 'media_id' => '媒体文件上传后获取的唯一标识', 'created_at ' => '媒体上传时间戳')
     */
    public static function mediaUpload($access_token, $type, $name, $filename, $file_absolute_url)
    {
        $r = ClHttp::uploadFile(self::URL . "/media/upload?access_token=$access_token&type=$type", array(), $name, $filename, $file_absolute_url);
        $r = json_decode($r, true);
        return $r['errcode'] == 0 ? $r : false;
    }

    /**
     * @todo
     * @param $access_token
     * @param $media_id
     * @param $local_file_absolute_url
     */
    public static function mediaGet($access_token, $media_id, $local_file_absolute_url)
    {
        Http::curlDownload(self::URL . "/media/get?access_token=$access_token&media_id=$media_id", $local_file_absolute_url);
    }

    /**
     * 获取企业号某个应用的基本信息，包括头像、昵称、帐号类型、认证类型、可见范围等信息
     * @param $access_token
     * @param $agent_id :企业应用id
     * @return bool|mixed array(
     * "errcode":"0",
     * "errmsg":"ok" ,
     * "agentid":"1" , //企业应用id
     * "name":"NAME" , //企业应用名称
     * "square_logo_url":"xxxxxxxx" , //企业应用方形头像
     * "round_logo_url":"yyyyyyyy" , //企业应用圆形头像
     * "description":"desc" , //企业应用详情
     * "allow_userinfos":{  //企业应用可见范围（人员），其中包括userid和关注状态state
     * "user":[{
     * "userid":"id1",
     * "status":"1"
     * },{
     * "userid":"id2",
     * "status":"1"
     * }]
     * },
     * "allow_partys":{  //企业应用可见范围（部门）
     * "partyid": [1]
     * },
     * "allow_tags":{  //企业应用可见范围（标签）
     * "tagid": [1,2,3]
     * }
     * "close":0 , //企业应用是否被禁用
     * "redirect_domain":"www.qq.com", //企业应用可信域名
     * "report_location_flag":0, //企业应用是否打开地理位置上报 0：不上报；1：进入会话上报；2：持续上报
     * "isreportuser":0, //是否接收用户变更通知。0：不接收；1：接收
     * "isreportenter":0  //是否上报用户进入应用事件。0：不接收；1：接收
     * )
     */
    public static function agentGet($access_token, $agent_id)
    {
        $r = ClHttp::http(self::URL . "/agent/get?access_token=$access_token&agentid=$agent_id");
        return $r['errcode'] == 0 ? $r : false;
    }

    /**
     * 设置企业应用的选项设置信息
     * @param $access_token
     * @param $agent_id :企业应用的id
     * @param $report_location_flag :企业应用是否打开地理位置上报 0：不上报；1：进入会话上报；2：持续上报
     * @param $logo_media_id :企业应用头像的mediaid，通过多媒体接口上传图片获得mediaid，上传后会自动裁剪成方形和圆形两个头像
     * @param $name :企业应用名称
     * @param $description :企业应用详情
     * @param $redirect_domain :企业应用可信域名
     * @param $is_report_user :是否接收用户变更通知。0：不接收；1：接收
     * @param $is_report_enter :是否上报用户进入应用事件。0：不接收；1：接收
     * @return bool
     */
    public static function agentSet($access_token, $agent_id, $report_location_flag, $logo_media_id, $name, $description, $redirect_domain, $is_report_user, $is_report_enter)
    {
        $r = ClHttp::http(self::URL . "/agent/set?access_token=$access_token", array(
            'agentid' => $agent_id,
            'report_location_flag' => $report_location_flag,
            'logo_mediaid' => $logo_media_id,
            'name' => $name,
            'description' => $description,
            'redirect_domain' => $redirect_domain,
            'isreportuser' => $is_report_user,
            'isreportenter' => $is_report_enter
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 应用id
     */
    const F_AGENT_ID = 'agentid';

    /**
     * 应用名称
     */
    const F_AGENT_NAME = 'name';

    /**
     * 方形头像url
     */
    const F_AGENT_SQUARE_LOGO_URL = 'square_logo_url';

    /**
     * 圆形头像url
     */
    const F_AGENT_ROUND_LOGO_URL = 'round_logo_url';

    /**
     * 获取secret所在管理组内的应用概况，会返回管理组内应用的id及名称、头像等信息
     * @param $access_token
     * @return bool|array array(array(
     * "agentid": "5", //应用id
     * "name": "企业小助手", //应用名称
     * "square_logo_url": "url", //方形头像url
     * "round_logo_url": "url" //圆形头像url
     * ))
     */
    public static function agentList($access_token)
    {
        $r = ClHttp::http(self::URL . "/agent/list?access_token=$access_token");
        return $r['errcode'] == 0 ? $r['agentlist'] : false;
    }

    /**
     * 发送text消息
     * @param $access_token
     * @param $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $content :消息内容
     * @param $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendText($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, $content, $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "text",
            "agentid" => $agent_id,
            "text" => array(
                "content" => $content
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 发送image消息
     * @param $access_token
     * @param array $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param array $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param array $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $media_id :素材id
     * @param int $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendImage($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, $media_id, $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "image",
            "agentid" => $agent_id,
            "image" => array(
                "media_id" => $media_id
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 发送voice消息
     * @param $access_token
     * @param array $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param array $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param array $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $media_id :素材id
     * @param int $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendVoice($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, $media_id, $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "voice",
            "agentid" => $agent_id,
            "voice" => array(
                "media_id" => $media_id
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 发送video消息
     * @param $access_token
     * @param array $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param array $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param array $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $media_id :素材id
     * @param string $title :视频消息的标题
     * @param string $desc :视频消息的描述
     * @param int $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendVideo($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, $media_id, $title = '', $desc = '', $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "video",
            "agentid" => $agent_id,
            "video" => array(
                "media_id" => $media_id,
                'title' => $title,
                'description' => $desc
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 发送file消息
     * @param $access_token
     * @param array $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param array $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param array $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $media_id :素材id
     * @param int $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendFile($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, $media_id, $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "file",
            "agentid" => $agent_id,
            "file" => array(
                "media_id" => $media_id,
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 发送news消息
     * @param $access_token
     * @param array $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param array $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param array $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $news :10条以内，array(array('title' => '标题', 'description' => '描述', 'url' =>'点击后跳转的链接', 'picurl' => '图文消息的图片链接，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80。如不填，在客户端不显示图片'))
     * @param int $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendNews($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, array $news, $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "news",
            "agentid" => $agent_id,
            "news" => array(
                'articles' => $news
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 发送mp news消息，消息与news消息类似，不同的是图文消息内容存储在微信后台，并且支持保密选项。每个应用每天最多可以发送100次。
     * @param $access_token
     * @param array $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param array $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param array $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $news :10条以内，array(array('title' => '标题', 'description' => '描述', 'url' =>'点击后跳转的链接', 'picurl' => '图文消息的图片链接，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80。如不填，在客户端不显示图片'))
     * @param int $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendMpNews($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, array $news, $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "mpnews",
            "agentid" => $agent_id,
            "mpnews" => array(
                'articles' => $news
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 发送mp news消息，消息与news消息类似，不同的是图文消息内容存储在微信后台，并且支持保密选项。每个应用每天最多可以发送100次。
     * @param $access_token
     * @param array $to_users :成员ID列表（最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @param array $to_parties :部门ID列表，最多支持100个。当$to_users为@all时忽略本参数
     * @param array $to_tags :标签ID列表，当$to_users为为@all时忽略本参数
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param $media_id :素材资源标识ID，通过上传永久图文素材接口获得。注：必须是在该agent下创建的。
     * @param int $is_safe :表示是否是保密消息，0表示否，1表示是，默认0
     * @return bool
     */
    public static function msgSendMpNewsUseMedia($access_token, array $to_users, array $to_parties, array $to_tags, $agent_id, $media_id, $is_safe = 0)
    {
        $r = ClHttp::http(self::URL . "/message/send?access_token=$access_token", array(
            "touser" => is_array($to_users) ? implode('|', $to_users) : '@all',
            "toparty" => is_array($to_parties) ? implode('|', $to_parties) : '',
            "totag" => is_array($to_tags) ? implode('|', $to_tags) : '',
            "msgtype" => "mpnews",
            "agentid" => $agent_id,
            "mpnews" => array(
                'media_id' => $media_id
            ),
            "safe" => $is_safe
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 成员点击click类型按钮后，微信服务器会通过消息接口推送消息类型为event 的结构给开发者（参考消息接口指南），并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与成员进行交互；
     */
    const V_BUTTON_TYPE_CLICK = 'click';

    /**
     * 成员点击view类型按钮后，微信客户端将会打开开发者在按钮中填写的网页URL，可与网页授权获取成员基本信息接口结合，获得成员基本信息。
     */
    const V_BUTTON_TYPE_VIEW = 'view';

    /**
     * 成员点击按钮后，微信客户端将调起扫一扫工具，完成扫码操作后显示扫描结果（如果是URL，将进入URL），且会将扫码的结果传给开发者，开发者可以下发消息。
     */
    const V_BUTTON_TYPE_SCAN_CODE_PUSH = 'scancode_push';

    /**
     * 成员点击按钮后，微信客户端将调起扫一扫工具，完成扫码操作后，将扫码的结果传给开发者，同时收起扫一扫工具，然后弹出“消息接收中”提示框，随后可能会收到开发者下发的消息。
     */
    const V_BUTTON_TYPE_SCAN_CODE_WAIT_MSG = 'scancode_waitmsg';

    /**
     * 成员点击按钮后，微信客户端将调起系统相机，完成拍照操作后，会将拍摄的相片发送给开发者，并推送事件给开发者，同时收起系统相机，随后可能会收到开发者下发的消息。
     */
    const V_BUTTON_TYPE_PIC_SYS_PHOTO = 'pic_sysphoto';

    /**
     * 成员点击按钮后，微信客户端将弹出选择器供成员选择“拍照”或者“从手机相册选择”。成员选择后即走其他两种流程。
     */
    const V_BUTTON_TYPE_PIC_PHOTO_OR_ALBUM = 'pic_photo_or_album';

    /**
     * 成员点击按钮后，微信客户端将调起微信相册，完成选择操作后，将选择的相片发送给开发者的服务器，并推送事件给开发者，同时收起相册，随后可能会收到开发者下发的消息。
     */
    const V_BUTTON_TYPE_PIC_WEI_XIN = 'pic_weixin';

    /**
     * 成员点击按钮后，微信客户端将调起地理位置选择工具，完成选择操作后，将选择的地理位置发送给开发者的服务器，同时收起位置选择工具，随后可能会收到开发者下发的消息。
     */
    const V_BUTTON_TYPE_LOCATION_SELECT = 'location_select';

    /**
     * 创建按钮，除click和view外所有事件，仅支持微信iPhone5.4.1/Android5.4以上版本，旧版本微信成员点击后将没有回应，开发者也不能正常接收到事件推送。最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。请注意，创建自定义菜单后，由于微信客户端缓存，需要24小时微信客户端才会展现出来。建议测试时可以尝试取消关注企业号后再次关注，则可以看到创建后的效果。
     * @param $access_token
     * @param $agent_id :企业应用的id，整型。可在应用的设置页面查看
     * @param array $buttons : array(
     * array(
     *  'type' => 'click',
     *  'name' => '今日歌曲',
     *  'key' => '8898'
     * ),
     * array(
     *  'name': '扫码',
     *  'sub_button': array(
     *      array(
     *          'type': 'scancode_waitmsg',
     *          'name': '扫码带提示',
     *          'key': 'rselfmenu_0_0'
     *      ),
     *      array()
     *      )
     * ))
     * @return bool
     */
    public static function menuCreate($access_token, $agent_id, array $buttons)
    {
        $r = ClHttp::http(self::URL . "/menu/create?access_token=$access_token&agentid=$agent_id", array(
            'button' => array(
                $buttons
            )
        ));
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 删除菜单
     * @param $access_token
     * @param $agent_id
     * @return bool
     */
    public static function menuDelete($access_token, $agent_id)
    {
        $r = ClHttp::http(self::URL . "/menu/delete?access_token=$access_token&agentid=$agent_id");
        return $r['errcode'] == 0 ? true : false;
    }

    /**
     * 获取菜单
     * @param $access_token
     * @param $agent_id
     * @return mixed
     */
    public static function menuGet($access_token, $agent_id)
    {
        $r = ClHttp::http(self::URL . "/menu/get?access_token=$access_token&agentid=$agent_id");
        return $r;
    }

    /**
     * 企业获取code
     * @param $corp_id
     * @param $redirect_uri :授权后重定向的回调链接地址，请使用urlencode对链接进行处理
     * @param $state :重定向后会带上state参数，企业可以填写a-zA-Z0-9的参数值，长度不可超过128个字节
     * @return string
     */
    public static function oauthGetCodeUrl($corp_id, $redirect_uri, $state)
    {
        $redirect_uri = urlencode($redirect_uri);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$corp_id&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=$state#wechat_redirect";
    }

    public static function oauthGetUserInfo($access_token, $code)
    {
        $r = ClHttp::http(self::URL . "/user/getuserinfo?access_token=$access_token&code=$code");
        return isset($r['errcode']) ? false : $r;
    }

    /**
     * userid转换成openid
     * @param $access_token
     * @param $user_id :企业号内的成员id
     * @param string $agent_id :需要发送红包的应用ID，若只是使用微信支付和企业转账，则无需该参数
     * @return bool|string
     */
    public static function convertUserIdToOpenId($access_token, $user_id, $agent_id = '')
    {
        $post = array(
            'userid' => $user_id
        );
        if (!empty($agent_id)) {
            $post['agentid'] = $agent_id;
        }
        $r = ClHttp::http(self::URL . "/user/convert_to_openid?access_token=$access_token", $post);
        return $r['errcode'] == 0 ? $r['openid'] : false;
    }

    /**
     * openid转换成userid
     * @param $access_token
     * @param $open_id :在使用微信支付、微信红包和企业转账之后，返回结果的openid
     * @return bool|string
     */
    public static function convertOpenIdToUserId($access_token, $open_id)
    {
        $r = ClHttp::http(self::URL . "/user/convert_to_userid?access_token=$access_token", array(
            'openid' => $open_id
        ));
        return $r['errcode'] == 0 ? $r['userid'] : false;
    }

    /**
     * 获取调用微信JS接口的临时票据
     * @param $access_token
     * @return mixed
     * @throws Exception
     */
    public static function jsApiTicketGet($access_token)
    {
        $key = ClCache::getKey(1, $access_token);
        $r = cache($key);
        if ($r !== false) {
            return $r;
        }
        $r = ClHttp::http(self::URL . "/get_jsapi_ticket?access_token=$access_token");
        if ($r['errcode'] != 0) {
            throw new Exception(ClArray::toString($r));
        }
        //写入缓存
        cache($key, $r['ticket'], $r['expires_in'] - self::V_CACHE_SAFE_SECOND);
        return $r['ticket'];
    }

    /**
     * 获取JS-SDK权限验证的签名
     * @param $access_token
     * @param $nonce_str :16个随机字符
     * @param $timestamp :时间戳
     * @return string
     * @throws Exception
     */
    public static function jsApiSignatureGet($access_token, $nonce_str, $timestamp)
    {
        $js_api_ticket = self::jsApiTicketGet($access_token);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return sha1("jsapi_ticket=$js_api_ticket&noncestr=$nonce_str&timestamp=$timestamp&url=$url");
    }

}
