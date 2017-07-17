<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * Email: 597481334@qq.com
 * Date: 2015/7/3
 * Time: 11:14
 */

namespace ClassLibrary;
use think\Exception;

/**
 * class library redis
 * Class ClRedis
 * @package Common\Common
 */
class ClRedis
{

    /**
     * 实例对象
     * @var null
     */
    private $redis_instance = null;

    private $persistent = false;

    private $host = '127.0.0.1';

    private $port = 6379;

    private $password = '';

    private $select = '';

    private $timeout = 0;

    private $expire = 0;

    private $prefix = '';

    /**
     * 获取实例
     * @param bool $persistent
     * @param string $host
     * @param int $port
     * @param string $password
     * @param int $select
     * @param int $timeout
     * @param int $expire
     * @param string $prefix
     * @return mixed
     * @throws Exception
     */
    public function __construct($persistent = false, $host = '127.0.0.1', $port = 6379, $password = '', $select = 0, $timeout = 0, $expire = 0, $prefix = ''){
        if (!extension_loaded('redis')) {
            throw new Exception('not support: redis');
        }
        $this->persistent = $persistent;
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->select = $select;
        $this->timeout = $timeout;
        $this->expire = $expire;
        $this->prefix = $prefix;
        $this->connect();
    }

    /**
     * 链接
     */
    private function connect(){
        $this->redis_instance = new \Redis;
        $function = $this->persistent ? 'pconnect' : 'connect';
        $this->redis_instance->$function($this->host, $this->port, $this->timeout);
        if ('' != $this->password) {
            $this->redis_instance->auth($this->password);
        }
        if (0 != $this->select) {
            $this->redis_instance->select($this->select);
        }
    }

    /**
     * 判断key是否存在
     * @param $key
     * @return boolean
     */
    public function exist($key)
    {
        $this->checkConnect();
        return $this->redis_instance->exist($key) === 1;
    }

    /**
     * 返回key值的长度
     * @param $key
     * @return mixed
     */
    public function strLen($key)
    {
        $this->checkConnect();
        return $this->redis_instance->strlen($key);
    }

    /**
     * 返回满足条件的所有key，比如keys('user*')
     * @param $pattern
     * @return mixed
     */
    public function keys($pattern)
    {
        $this->checkConnect();
        return $this->redis_instance->keys($pattern);
    }

    /**
     * 为给定 key 设置生存时间，当 key 过期时(生存时间为 0 )，它会被自动删除。
     * @param $key
     * @param $duration
     * @return bool
     */
    public function expire($key, $duration)
    {
        $this->checkConnect();
        return $this->redis_instance->expire($key, $duration) === 1;
    }

    /**
     * 返回key的剩余时间time to live
     * @param $key
     * @return mixed 当 key 不存在时，返回 -2;当 key 存在但没有设置剩余生存时间时，返回 -1;否则，以秒为单位，返回 key 的剩余生存时间。
     */
    public function ttl($key)
    {
        $this->checkConnect();
        return $this->redis_instance->ttl($key);
    }

    /**
     * 返回key存储的类型
     * @param $key
     * @return mixed none (key不存在)；string (字符串)；list (列表)；set (集合)；zset (有序集)；hash (哈希表)
     */
    public function type($key)
    {
        $this->checkConnect();
        return $this->redis_instance->type($key);
    }

    /**
     * 返回名称为key的list中index位置的元素
     * @param $index
     * @return mixed
     */
    public function lIndex($index)
    {
        $this->checkConnect();
        return $this->redis_instance->lindex($index);
    }

    /**
     * 在名称为key的list右边（头）添加一个值为value的元素,如果 key 不存在，一个空列表会被创建并执行 LPUSH 操作。当 key存在但不是列表类型时，返回一个错误。
     * @param $key
     * @param $value
     * @return mixed
     */
    public function lPush($key, $value)
    {
        $this->checkConnect();
        return $this->redis_instance->lPush($key, $value);
    }

    /**
     * 在名称为key的list左边(头)添加一个值为value的元素,当 key不存在时，LPUSHX命令什么也不做。
     * @param $key
     * @param $value
     * @return mixed
     */
    public function lPushX($key, $value)
    {
        $this->checkConnect();
        return $this->redis_instance->lPushx($key, $value);
    }

    /**
     * 返回列表 key 中指定区间内的元素，下标(index)参数 start 和 stop 都以 0 为底，也就是说，以 0 表示列表的第一个元素，以 1 表示列表的第二个元素，以此类推。
     * @param $key
     * @param $start
     * @param $end
     * @return mixed
     */
    public function lRange($key, $start, $end)
    {
        $this->checkConnect();
        return $this->redis_instance->lRange($key, $start, $end);
    }

    /**
     * 在名称为key的list右边（尾）添加一个值为value的 元素
     * @param $key
     * @param $value
     * @return mixed
     */
    public function rPush($key, $value)
    {
        $this->checkConnect();
        return $this->redis_instance->rPush($key, $value);
    }

    /**
     * 将值 value 插入到列表 key 的表尾，当且仅当 key 存在并且是一个列表。和 RPUSH 命令相反，当 key 不存在时， RPUSHX 命令什么也不做。
     * @param $key
     * @param $value
     * @return mixed
     */
    public function rPushX($key, $value)
    {
        $this->checkConnect();
        return $this->redis_instance->rPushx($key, $value);
    }

    /**
     * 头部pop
     * @param $key
     * @return mixed
     */
    public function lPop($key)
    {
        $this->checkConnect();
        return $this->redis_instance->lPop($key);
    }

    /**
     * 尾部pop
     * @param $key
     * @return mixed
     */
    public function rPop($key)
    {
        $this->checkConnect();
        return $this->redis_instance->rPop($key);
    }

    /**
     * 当前链接是否有效
     * @return bool
     */
    public function checkConnect(){
        try{
            if($this->redis_instance->ping() !== '+PONG'){
                $this->connect();
            }
        }catch (\RedisException $e){
            $this->connect();
        }
    }

}
