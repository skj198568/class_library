<?php
/**
 * Created by PhpStorm.
 * User: SongKeJing
 * Date: 2016/11/8
 * Time: 13:22
 */

namespace ClassLibrary;

use Michelf\Markdown;

/**
 * markdown解析器
 * Class ClMarkDown
 * @package Common\ClassLibrary
 */
class ClMarkDown {

    /**
     * 解析markdown->html
     * @param $content
     * @return string
     */
    public static function parse($content) {
        spl_autoload_register(function ($class) {
            if (is_file(__DIR__ . DIRECTORY_SEPARATOR . preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php')) {
                require_once __DIR__ . DIRECTORY_SEPARATOR . preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php';
            }
        });
        return Markdown::defaultTransform($content);
    }

}