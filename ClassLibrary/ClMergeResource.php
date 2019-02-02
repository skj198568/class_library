<?php
/**
 * Created by PhpStorm.
 * User: skj19
 * Date: 2016/11/28
 * Time: 18:22
 */

namespace ClassLibrary;

use think\Request;

/**
 * 压缩资源文件
 * Class ClMergeResource
 * @package ClassLibrary
 */
class ClMergeResource {

    /**
     * 未找到的资源文件
     * @var array
     */
    private static $not_has_files = [];

    /**
     * 所有资源映射创建时间，用于判断文件是否修改
     * @var array
     */
    private static $all_resource_file_map_create_time = [];

    /**
     * 获取存储key
     * @param $content_key
     * @return string
     */
    private static function getAllResourceFileCacheKey($content_key) {
        return $content_key . '_file_map';
    }

    /**
     * 执行
     * @param $content
     * @param array $un_merge_module 忽略的模块
     * @return bool|mixed
     */
    public static function merge($content, $un_merge_module = []) {
        if (!empty($un_merge_module) && in_array(request()->controller(), $un_merge_module)) {
            return $content;
        }
        //缓存key
        $key = ClCache::getKey(ClString::toCrc32($content));
        //非本地局域网请求
        if (!ClVerify::isLocalIp()) {
            $merge_content = cache($key);
            if ($merge_content !== false) {
                $resource_items  = ClString::parseToArray($merge_content, '/resource/', '"');
                $all_files_is_ok = true;
                foreach ($resource_items as $each_item) {
                    $each_item = DOCUMENT_ROOT_PATH . trim($each_item, '"');
                    if (!is_file($each_item)) {
                        $all_files_is_ok = false;
                        break;
                    }
                }
                if ($all_files_is_ok) {
                    //判断文件创建时间是否一致
                    $all_file_time_map = cache(self::getAllResourceFileCacheKey($key));
                    if ($all_file_time_map === false) {
                        $all_files_is_ok = false;
                    } else {
                        foreach ($all_file_time_map as $each_file => $file_create_time) {
                            if (!is_file($each_file)) {
                                $all_files_is_ok = false;
                                break;
                            }
                            if (self::getFileVersion($each_file) != $file_create_time) {
                                $all_files_is_ok = false;
                                break;
                            }
                        }
                    }
                }
                if ($all_files_is_ok) {
                    return $merge_content;
                }
            }
        }
        //替换掉所有的版本控制
        $content  = str_replace('?v=<?php echo VERSION;?>', '', $content);
        $js_files = ClString::parseToArray($content, '<script ', '</script>');
        //去除重复js
        $js_files = array_unique($js_files);
        //去除无效js
        foreach ($js_files as $k => $v) {
            $js_absolute_path = self::getJsAbsolutePath($v);
            //不存在的文件
            if (!is_file($js_absolute_path)) {
                unset($js_files[$k]);
                continue;
            }
            if (!(strpos($v, 'uncompressed') === false && strpos($v, ' src="') !== false && strpos($v, '.js') !== false)) {
                //替换
                $content = self::replaceJsAddVersion($content, $v);
                unset($js_files[$k]);
                continue;
            }
        }
        $js_files = array_values($js_files);
        if (count($js_files) > 0) {
            foreach ($js_files as $each) {
                $content = self::replaceJsAddVersion($content, $each);
            }
        }
        $css_files = ClString::parseToArray($content, '<link ', '>');
        //去除重复css
        $css_files = array_unique($css_files);
        //去除无效css
        foreach ($css_files as $k => $v) {
            $css_absolute_path = self::getCssAbsolutePath($v);
            if (!is_file($css_absolute_path)) {
                unset($css_files[$k]);
                continue;
            }
            if (!(strpos($v, 'uncompressed') === false && strpos($v, ' rel="stylesheet"') !== false && strpos($v, '.css') !== false)) {
                //替换
                $content = self::replaceCssAddVersion($content, $v);
                unset($css_files[$k]);
                continue;
            }
        }
        $css_files = array_values($css_files);
        if (count($css_files) > 0) {
            foreach ($css_files as $each) {
                //替换
                $content = self::replaceCssAddVersion($content, $each);
            }
        }
        if (!empty(self::$not_has_files)) {
            log_info('MergeResourceBehavior:', self::$not_has_files);
        }
        if (!ClVerify::isLocalIp()) {
            //写入缓存
            cache($key, $content);
            //写入文件映射缓存
            cache(self::getAllResourceFileCacheKey($key), self::$all_resource_file_map_create_time);
        }
        return $content;
    }

    /**
     * 获取js绝对路径
     * @param $js
     * @return string
     */
    private static function getJsAbsolutePath($js) {
        $js_file = ClString::getBetween($js, 'src="', '.js', false);
        if (empty($js_file)) {
            //可能是js代码块
            return '';
        }
        $js_file .= '.js';
        if (ClVerify::isUrl($js_file)) {
            if (strpos($js_file, 'http') === 0 || strpos($js_file, '//') === 0) {
                return $js_file;
            }
        }
        $js_file = DOCUMENT_ROOT_PATH . $js_file;
        //添加至时间映射
        if (is_file($js_file)) {
            self::$all_resource_file_map_create_time[$js_file] = self::getFileVersion($js_file);
        }
        return $js_file;
    }

    /**
     * 获取css的路径
     * @param $file
     * @return string
     */
    private static function getCssAbsolutePath($file) {
        $css_file = ClString::getBetween($file, 'href="', '"', false);
        if (empty($css_file)) {
            return '';
        }
        if (ClVerify::isUrl($css_file)) {
            if (strpos($css_file, 'http') === 0 || strpos($css_file, '//') === 0) {
                return $css_file;
            }
        }
        $css_file = DOCUMENT_ROOT_PATH . $css_file;
        if (strpos($css_file, '.css') === false) {
            return '';
        }
        $css_file = ClString::getBetween($css_file, '', '.css');
        if (is_file($css_file)) {
            //添加至时间映射
            self::$all_resource_file_map_create_time[$css_file] = self::getFileVersion($css_file);
        }
        return $css_file;
    }

    /**
     * 获取css使用的资源路径
     * @param $absolute_path
     * @param $relative_path
     * @return mixed|string
     */
    private static function getCssResourcePath($absolute_path, $relative_path) {
        $relative_path_temp = $relative_path;
        //图片资源忽略
        if (strpos($relative_path, 'data:image') !== false) {
            return '';
        }
        $absolute_path_array = explode('/', $absolute_path);
        //去除引号
        $relative_path = str_replace(['"', '\''], ['', ''], $relative_path);
        //去除所有空格
        $relative_path = ClString::spaceTrim($relative_path);
        $relative_path = ClString::getBetween($relative_path, 'url(', ')', false);
        if (empty($relative_path)) {
            return '';
        }
        $relative_path_array = explode('/', $relative_path);
        if (empty($relative_path_array)) {
            return '';
        }
        //绝对路径，忽略
        if (empty($relative_path_array[0])) {
            return '';
        }
        //相对路径
        array_pop($absolute_path_array);
        foreach ($relative_path_array as $each) {
            if ($each == '..') {
                array_pop($absolute_path_array);
                array_shift($relative_path_array);
            }
        }
        $absolute_path_array = array_merge($absolute_path_array, $relative_path_array);
        $absolute_path       = implode('/', $absolute_path_array);
        $absolute_path_true  = '';
        if (strpos($absolute_path, '?') !== false || strpos($absolute_path, '#') !== false) {
            if (strpos($absolute_path, '?') !== false) {
                $absolute_path_true = ClString::split($absolute_path, '?', true, false);
            } else if (strpos($absolute_path, '#') !== false) {
                $absolute_path_true = ClString::split($absolute_path, '#', true, false);
            }
        } else {
            $absolute_path_true = $absolute_path;
        }
        if (is_file($absolute_path_true)) {
            if (strpos($absolute_path, '?') !== false) {
                $absolute_path_true .= ClString::split($absolute_path, '?', false);
            } else if (strpos($absolute_path, '#') !== false) {
                $absolute_path_true .= ClString::split($absolute_path, '#', false, false);
            }
            return str_replace(DOCUMENT_ROOT_PATH, '', $absolute_path_true);
        } else {
            if (strpos($absolute_path, '?') !== false) {
                $absolute_path_true .= ClString::split($absolute_path, '?', false);
            } else if (strpos($absolute_path, '#') !== false) {
                $absolute_path_true .= ClString::split($absolute_path, '#', false, false);
            }
            self::$not_has_files[$relative_path_temp] = str_replace(DOCUMENT_ROOT_PATH, '', $absolute_path_true);
            return '';
        }
    }

    /**
     * 获取文件版本
     * @param string $file_absolute_url 文件绝对地址
     * @return false|string
     */
    private static function getFileVersion($file_absolute_url) {
        return date('YmdHis', filectime($file_absolute_url));
    }

    /**
     * 替换css为带版本的路径
     * @param $content
     * @param $css_old_include_string
     * @return mixed
     */
    private static function replaceCssAddVersion($content, $css_old_include_string) {
        $css_absolute_path = self::getCssAbsolutePath($css_old_include_string);
        $content           = str_replace($css_old_include_string, '<link href="' . str_replace(DOCUMENT_ROOT_PATH, '', $css_absolute_path) . '?v=' . self::getFileVersion($css_absolute_path) . '" rel="stylesheet">', $content);
        return $content;
    }

    /**
     * 替换js为带版本的路径
     * @param $content
     * @param $js_old_include_string
     * @return mixed
     */
    private static function replaceJsAddVersion($content, $js_old_include_string) {
        $js_absolute_path = self::getJsAbsolutePath($js_old_include_string);
        $content          = str_replace($js_old_include_string, '<script src="' . str_replace(DOCUMENT_ROOT_PATH, '', $js_absolute_path) . '?v=' . self::getFileVersion($js_absolute_path) . '"></script>', $content);
        return $content;
    }

}