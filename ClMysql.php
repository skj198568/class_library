<?php
/**
 * Created by PhpStorm.
 * User: skj19
 * Date: 2016/5/17
 * Time: 17:08
 */

namespace ClassLibrary;

/**
 * mysql简易函数
 * Class ClMysql
 * @package ClassLibrary
 */
class ClMysql
{

    /**
     * 实例对象
     * @var null
     */
    private static $mysql_instance = null;

    /**
     * 实例化对象
     * @param $host
     * @param $port
     * @param $user
     * @param $password
     * @param $database
     */
    public static function init($host, $port, $user, $password, $database)
    {
        if (empty($port)) {
            self::$mysql_instance = mysqli_connect($host, $user, $password, $database);
        } else {
            self::$mysql_instance = mysqli_connect($host, $user, $password, $database, $port);
        }
        mysqli_query(self::$mysql_instance, "set names 'utf8'");
    }

    /**
     * 查询
     * @param $sql
     * @return array
     */
    public static function query($sql)
    {
        if (self::$mysql_instance == null) {
            exit('please call ClMysql::init() first');
        }
        $result = mysqli_query(self::$mysql_instance, $sql);
        if(empty($result)){
            return [];
        }
        // 输出每行数据
        $rows = [];
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * 关闭链接
     */
    public static function close()
    {
        if (self::$mysql_instance != null) {
            mysqli_close(self::$mysql_instance);
        }
    }


}