<?php
/**
 * Created by PhpStorm.
 * User: kejing.song
 * QQ: 597481334
 * Email: skj198568@163.com
 * Date: 2015/12/28
 * Time: 22:16
 */

namespace ClassLibrary;

/**
 * xml类库
 * Class ClXml
 * @package ClassLibrary
 */
class ClXml {

    /**
     * xml to array
     * @param $xml
     * @return mixed
     */
    public static function toArray($xml) {
        $obj  = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($obj);
        $arr  = json_decode($json, true);
        return $arr;
    }

}