<?php
/**
 * Created by PhpStorm.
 * User: skj19
 * Date: 2016/11/28
 * Time: 18:22
 */

namespace ClassLibrary;


use think\Cache;
use think\Config;
use think\Request;

class ClMergeResource {

    /**
     * 未找到的资源文件
     * @var array
     */
    private static $not_has_files = [];

    /**
     * base64压缩图片最大体积，大于该体积，则不进行处理
     * @var int
     */
    private static $image_base64_max_size = 0;

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
        //临时修改缓存配置
        $config           = config('cache');
        $config['type']   = 'File';
        $config['prefix'] = 'merge_resource';
        Config::set('cache', $config);
        //缓存key
        $key = ClCache::getKey(ClString::toCrc32($content));;
        //非本地局域网请求
        if (!ClVerify::isLocalIp()) {
            $merge_content  = Cache::get($key);
            $resource_items = ClString::parseToArray($merge_content, '/resource/', '"');
            $not_exist      = false;
            foreach ($resource_items as $each_item) {
                $each_item = DOCUMENT_ROOT_PATH . trim($each_item, '"');
                if (!is_file($each_item)) {
                    $not_exist = true;
                    break;
                }
            }
            if ($merge_content !== false && $not_exist === false) {
                return $merge_content;
            }
        }
        //替换掉所有的版本控制
        $content  = str_replace('?v=<?php echo VERSION;?>', '', $content);
        $js_files = ClString::parseToArray($content, '<script ', '</script>');
        //去除重复js
        $js_files = array_unique($js_files);
        //去除无效js
        foreach ($js_files as $k => $v) {
            if (!(strpos($v, 'uncompressed') === false && strpos($v, ' src="') !== false && strpos($v, '.js') !== false)) {
                unset($js_files[$k]);
                continue;
            }
            //处理业务js
            if (strpos($v, 'application') !== false) {
                $content = self::moveApplicationJs($content, $v);
                unset($js_files[$k]);
                continue;
            }
            //不存在的文件
            if (!is_file(self::getJsAbsolutePath($v))) {
                unset($js_files[$k]);
                continue;
            }
        }
        $js_files = array_values($js_files);
        if (count($js_files) > 0) {
            if (!ClVerify::isLocalIp()) {
                //非局域网请求
                //合并js
                $merge_js_file = self::mergeJs($js_files);
                //替换js文件
                foreach ($js_files as $k => $v) {
                    if ($k == 0) {
                        //替换
                        $content = str_replace($v, sprintf('<script src="%s"></script>', $merge_js_file), $content);
                    } else {
                        //删除
                        $content = str_replace($v, '', $content);
                    }
                }
            } else {
                //局域网请求
                foreach ($js_files as $each) {
                    //替换
                    $content = str_replace($each, str_replace(ClString::getBetween($each, '.js', '"'), '.js?v=' . md5_file(self::getJsAbsolutePath($each)) . '"', $each), $content);
                }
            }
        }
        $css_files = ClString::parseToArray($content, '<link ', '>');
        //去除重复css
        $css_files = array_unique($css_files);
        //去除无效css
        foreach ($css_files as $k => $v) {
            if (!(strpos($v, 'uncompressed') === false && strpos($v, ' rel="stylesheet"') !== false && strpos($v, '.css') !== false)) {
                unset($css_files[$k]);
                continue;
            }
            if (!is_file(self::getCssAbsolutePath($v))) {
                unset($css_files[$k]);
                continue;
            }
        }
        $css_files = array_values($css_files);
        if (count($css_files) > 0) {
            if (!ClVerify::isLocalIp()) {
                //合并css文件
                $merge_css_file = sprintf('<link rel="stylesheet" href="%s"/>', self::mergeCss($css_files));
                //替换css文件
                foreach ($css_files as $k => $v) {
                    if ($k == 0) {
                        //替换
                        $content = str_replace($v, $merge_css_file, $content);
                    } else {
                        //删除
                        $content = str_replace($v, '', $content);
                    }
                }
            } else {
                //局域网请求
                foreach ($css_files as $each) {
                    //替换
                    $content = str_replace($each, str_replace(ClString::getBetween($each, '.css', '"'), '.css?v=' . md5_file(self::getCssAbsolutePath($each)) . '"', $each), $content);
                }
            }
        }
        if (!empty(self::$not_has_files)) {
            log_info('MergeResourceBehavior:', self::$not_has_files);
        }
        //处理资源的images base64处理
        $content = self::dealBase64Images($content);
        if (!ClVerify::isLocalIp()) {
            //写入缓存
            Cache::set($key, $content);
        }
        return $content;
    }

    /**
     * 合并js文件
     * @param $js_files
     * @return mixed
     */
    private static function mergeJs($js_files) {
        //合并js文件
        $js_temp_file = DOCUMENT_ROOT_PATH . '/resource/js/' . ClString::toCrc32(json_encode($js_files)) . '/temp.js';
        //创建文件夹
        ClFile::dirCreate($js_temp_file);
        $js_temp_file_handle = fopen($js_temp_file, 'w+');
        $js_path             = '';
        foreach ($js_files as $v) {
            $js_path = self::getJsAbsolutePath($v);
            if (is_file($js_path)) {
                fputs($js_temp_file_handle, file_get_contents($js_path) . "\n");
            }
        }
        fclose($js_temp_file_handle);
        $merge_js_file = str_replace('temp.js', md5_file($js_temp_file) . '.js', $js_temp_file);
        //移动文件
        rename($js_temp_file, $merge_js_file);
        return str_replace(DOCUMENT_ROOT_PATH, '', $merge_js_file);
    }

    /**
     * 获取js绝对路径
     * @param $js
     * @return string
     */
    private static function getJsAbsolutePath($js) {
        return DOCUMENT_ROOT_PATH . ClString::getBetween($js, 'src="', '.js', false) . '.js';
    }

    /**
     * 移动业务逻辑js
     * @param $content
     * @param $js
     * @return bool
     */
    private static function moveApplicationJs($content, $js) {
        $js_file_right = ClString::getBetween($js, 'src="', '.js', false) . '.js';
        while (strpos($js_file_right, '//') !== false) {
            $js_file_right = str_replace('//', '/', $js_file_right);
        }
        if (!is_file(DOCUMENT_ROOT_PATH . '/' . $js_file_right)) {
            return $content;
        }
        //格式化new_js
        $new_js = '/resource/logic/' . $js_file_right;
        while (strpos($new_js, '..')) {
            $new_js = str_replace('..', '', $new_js);
        }
        while (strpos($new_js, '//') !== false) {
            $new_js = str_replace('//', '/', $new_js);
        }
        //创建文件夹
        ClFile::dirCreate(DOCUMENT_ROOT_PATH . $new_js);
        //复制文件
        copy(DOCUMENT_ROOT_PATH . '/' . $js_file_right, DOCUMENT_ROOT_PATH . $new_js);
        //替换js文件
        $content = str_replace($js, sprintf('<script src="%s?v=%s"></script>', $new_js, md5_file(DOCUMENT_ROOT_PATH . $new_js)), $content);
        return $content;
    }

    /**
     * 合并css文件
     * @param $css_files
     * @return mixed
     */
    private static function mergeCss($css_files) {
        //合并js文件
        $temp_file = DOCUMENT_ROOT_PATH . '/resource/css/' . ClString::toCrc32(json_encode($css_files)) . '/temp.css';
        //创建文件夹
        ClFile::dirCreate($temp_file);
        $temp_file_handle = fopen($temp_file, 'w+');
        $file_path        = '';
        $file_content     = '';
        $resource_files   = [];
        foreach ($css_files as $v) {
            $file_path      = self::getCssAbsolutePath($v);
            $file_content   = file_get_contents($file_path);
            $resource_files = ClString::parseToArray($file_content, 'url\(', '\)');
            if (!empty($resource_files)) {
                foreach ($resource_files as $resource_file) {
                    $path = self::getCssResourcePath($file_path, $resource_file);
                    if (!empty($path)) {
                        //替换css资源
                        $file_content = str_replace($resource_file, sprintf('url("%s")', $path), $file_content);
                    }
                }
            }
            if (is_file($file_path)) {
                fputs($temp_file_handle, $file_content . "\n");
            }
        }
        fclose($temp_file_handle);
        $merge_file = str_replace('temp.css', md5_file($temp_file) . '.css', $temp_file);
        //移动文件
        rename($temp_file, $merge_file);
        return str_replace(DOCUMENT_ROOT_PATH, '', $merge_file);
    }

    /**
     * 获取css的路径
     * @param $file
     * @return string
     */
    private static function getCssAbsolutePath($file) {
        return DOCUMENT_ROOT_PATH . ClString::getBetween($file, 'href="', '"', false);
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
     * 处理图片base64
     * @param $content
     * @return mixed
     */
    private static function dealBase64Images($content) {
        if (self::$image_base64_max_size == 0) {
            return $content;
        }
        $image_files   = ClString::parseToArray($content, '<img ', '>');
        $image_search  = [];
        $image_replace = [];
        if (!empty($image_files)) {
            $image_url = '';
            foreach ($image_files as $image_each) {
                $image_url = ClString::getBetween($image_each, '"', '"', false);
                if (is_file(DOCUMENT_ROOT_PATH . $image_url) && filesize(DOCUMENT_ROOT_PATH . $image_url) <= self::$image_base64_max_size) {
                    $image_search[]  = $image_url;
                    $image_replace[] = ClImage::base64Encode(DOCUMENT_ROOT_PATH . $image_url);
                }
            }
            log_info($image_search);
            //替换
            if (!empty($image_search)) {
                $content = str_replace($image_search, $image_replace, $content);
            }
        }
        return $content;
    }

    /**
     * 清空缓存
     */
    public static function clearCache() {
        $temp_dir = DOCUMENT_ROOT_PATH . '/../runtime/cache/merge_resource';
        if (is_dir($temp_dir)) {
            //清空文件夹
            $cmd = sprintf('rm %s/* -rf', $temp_dir);
            exec($cmd);
        }
    }

}