<?php
/**
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/5/4
 * Time: 14:04
 */

namespace ClassLibrary;

/**
 * 日志
 * Class ClLog
 * @package ClassLibrary
 */
class ClLog
{

    /**
     * 包头长度
     * @var integer
     */
    const PACKAGE_FIXED_LENGTH = 17;

    /**
     * udp 包最大长度
     * @var integer
     */
    const MAX_UDP_PACKGE_SIZE = 65507;

    /**
     * 编码
     * @param array $msg
     * @return string
     */
    public static function encode($msg = [])
    {
        $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        // 防止msg过长
        $valid_size = self::MAX_UDP_PACKGE_SIZE - self::PACKAGE_FIXED_LENGTH - strlen($msg);
        if (strlen($msg) > $valid_size) {
            $msg = substr($msg, 0, $valid_size);
        }
        // 打包
        return $msg;
    }

    /**
     * 解包
     * @param string $bin_data
     * @return array
     */
    public static function decode($bin_data)
    {
        // 解包
        return json_decode($bin_data, true);
    }

    /**
     * 记录日志
     * @param int $uid
     * @param int $class_id
     * @param int $cid
     * @param array $more_info
     * @return bool
     */
    public static function record($uid = 0, $class_id = 0, $cid = 0, $more_info = [])
    {
        //不存在远程服务，则取消日志记录
        $report_address = config('REMOTE_UDP_LOG_ADDRESS');
        if (empty($report_address)) {
            return false;
        }
        $domain = request()->host();
        if(strpos($domain, 'www.') !== false){
            $domain = str_replace('www.', '', $domain);
        }
        //处理无效数据
        if(isset($more_info['session_id'])){
            unset($more_info['session_id']);
        }
        if(isset($more_info['token'])){
            unset($more_info['token']);
        }
        //cid特殊处理
        if(empty($cid) && isset($more_info['cid'])){
            $cid = $more_info['cid'];
        }
        //class_id特殊处理
        if(empty($class_id) && isset($more_info['class_id'])){
            $class_id = $more_info['class_id'];
        }
        $msg = [
            //当前域名
            $domain,
            //添加请求时间
            time(),
            //添加请求方式
            $_SERVER['REQUEST_METHOD'],
            //添加user_agent
            $_SERVER['HTTP_USER_AGENT'],
            //添加ip
            request()->ip(),
            //添加module_name
            request()->module(),
            //添加controller_name
            request()->controller(),
            //添加action_name
            request()->action(),
            $cid,
            $class_id,
            $uid,
            $more_info
        ];
        $socket = stream_socket_client($report_address);
        if (!$socket) {
            return false;
        }
        $bin_data = self::encode($msg);
        return stream_socket_sendto($socket, $bin_data) == strlen($bin_data);
    }
}