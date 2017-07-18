<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:30
 */

namespace ClassLibrary;

use think\Exception;

/**
 * Class ClEmail(class library邮件)
 * @package Common\Common
 */
class ClEmail
{

    /**
     * model实例
     * @var null
     */
    private static $model_instance = null;

    /**
     * SMTP 服务器
     * @var string
     */
    private static $smtp_host = '';

    /**
     * SMTP 端口
     * @var string
     */
    private static $smtp_port = '';

    /**
     * 账号
     * @var string
     */
    private static $smtp_user = '';

    /**
     * 密码
     * @var string
     */
    private static $smtp_password = '';

    /**
     * @var string
     */
    private static $from_email = '';

    /**
     * @var string
     */
    private static $from_name = '';

    /**
     * @var string
     */
    private static $reply_email = '';

    /**
     * @var string
     */
    private static $reply_name = '';

    /**
     * 初始化
     * @param $smtp_user 账号
     * @param $smtp_password 密码
     * @param $smtp_host SMTP 服务器
     * @param int $smtp_port SMTP 端口
     * @param string $from_email 发件人邮箱
     * @param string $from_name 发件人姓名
     * @param string $reply_email 回复邮箱
     * @param string $reply_name 回复姓名
     */
    public static function init($smtp_user, $smtp_password, $smtp_host, $smtp_port = 25, $from_email = '', $from_name = '', $reply_email = '', $reply_name = '')
    {
        self::$smtp_host = $smtp_host;
        self::$smtp_port = $smtp_port;
        self::$smtp_user = $smtp_user;
        self::$smtp_password = $smtp_password;
        self::$from_email = !empty($from_email) ? $from_email : $smtp_user;
        self::$from_name = !empty($from_name) ? $from_name : $smtp_user;
        self::$reply_email = !empty($reply_email) ? $reply_email : self::$from_email;
        self::$reply_name = !empty($reply_name) ? $reply_name : self::$from_name;
    }

    /**
     * 获取实例
     * @return \PHPMailer
     */
    private static function getInstance()
    {
        if (self::$model_instance == null) {
            include_once "PHPMailer/PHPMailerAutoload.php";
            self::$model_instance = new \PHPMailer();
        }
        return self::$model_instance;
    }

    /**
     * 发送邮件
     * @param array $to_email_info 接收邮件者[['email' => '', 'name' => '']]
     * @param string $title 邮件主题
     * @param string $body 邮件内容
     * @param array $attachment 附件列表
     * @return boolean
     * @throws Exception
     */
    public static function send($to_email_info, $title = '', $body = '', $attachment = [])
    {
        if (empty(self::$smtp_host)) {
            throw new Exception('please call ClEmail::init() first.');
        }
        self::getInstance()->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        self::getInstance()->IsSMTP();  // 设定使用SMTP服务
        self::getInstance()->SMTPAuth = true;
        self::getInstance()->SMTPDebug = 0;                     // 关闭SMTP调试功能
        // 1 = errors and messages
        // 2 = messages only
        self::getInstance()->SMTPAuth = true;                  // 启用 SMTP 验证功能
        self::getInstance()->SMTPSecure = '';                 // 使用安全协议
        self::getInstance()->Host = self::$smtp_host;  // SMTP 服务器
        self::getInstance()->Port = self::$smtp_port;  // SMTP服务器的端口号
        self::getInstance()->Username = self::$smtp_user;  // SMTP服务器用户名
        self::getInstance()->Password = self::$smtp_password;  // SMTP服务器密码
        self::getInstance()->SetFrom(self::$from_email, self::$from_name);
        self::getInstance()->AddReplyTo(self::$reply_email, self::$reply_name);
        self::getInstance()->Subject = $title;
        self::getInstance()->MsgHTML($body);
        foreach($to_email_info as $each){
            self::getInstance()->AddAddress($each['email'], $each['name']);
        }
        self::getInstance()->isHTML(true);
//        self::getInstance()->setLanguage('zh_cn');
        if (is_array($attachment)) { // 添加附件
            foreach ($attachment as $file) {
                is_file($file) && self::getInstance()->AddAttachment($file);
            }
        }
        $result = self::getInstance()->Send();
        //清除数据
        self::getInstance()->clearAllRecipients();
        return $result ? true : self::getInstance()->ErrorInfo;
    }

}
