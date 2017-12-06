<?php
/**
 * update或install之后执行复制文件
 * Created by PhpStorm.
 * User: SongKejing
 * QQ: 597481334
 * Date: 2017/12/06
 * Time: 15:56
 */
include_once "ClassLibrary/ClFile.php";
include_once "ClassLibrary/ClSystem.php";

$files = \ClassLibrary\ClFile::dirGetFiles(__DIR__.DIRECTORY_SEPARATOR.'scripts');
//往上3个目录
$document_root_dir = explode(DIRECTORY_SEPARATOR, __DIR__);
$document_root_dir = array_slice($document_root_dir, 0, count($document_root_dir)-3);
$document_root_dir = implode(DIRECTORY_SEPARATOR, $document_root_dir);
//var_dump($document_root_dir);
//循环覆盖文件
foreach($files as $file){
    $target_file = $document_root_dir.str_replace(__DIR__.DIRECTORY_SEPARATOR.'scripts', '', $file);
    //如果目标文件不存在，则新建
    \ClassLibrary\ClFile::dirCreate($target_file);
    //覆盖文件
    echo 'copy file: '.$target_file.PHP_EOL;
    copy($file, $target_file);
    $file_content = file_get_contents($target_file);
    //替换命名空间
    $file_content = str_replace('//namespace ', 'namespace ', $file_content);
    //回写文件
    file_put_contents($target_file, $file_content);
}