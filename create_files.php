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
include_once "ClassLibrary/ClString.php";

$files = \ClassLibrary\ClFile::dirGetFiles(__DIR__.DIRECTORY_SEPARATOR.'scripts');
//往上3个目录
$document_root_dir = explode(DIRECTORY_SEPARATOR, __DIR__);
$document_root_dir = array_slice($document_root_dir, 0, count($document_root_dir)-3);
$document_root_dir = implode(DIRECTORY_SEPARATOR, $document_root_dir);
//var_dump($document_root_dir);
//循环覆盖文件
foreach($files as $file){
    $target_file = $document_root_dir.str_replace(__DIR__.DIRECTORY_SEPARATOR.'scripts', '', $file);
    //替换文件名
    $target_file = str_replace('.php.tpl', '.php', $target_file);
    //如果目标文件不存在，则新建
    \ClassLibrary\ClFile::dirCreate($target_file);
    //覆盖文件
    echo 'copy file: '.$target_file.PHP_EOL;
    copy($file, $target_file);
}
//linux 环境处理mkdir 755问题
$files = [
    //日志
    $document_root_dir.'/thinkphp/library/think/File.php',
    //日志
    $document_root_dir.'/thinkphp/library/think/log/driver/File.php',
    //缓存
    $document_root_dir.'/thinkphp/library/think/cache/driver/File.php',
    //模板缓存
    $document_root_dir.'/thinkphp/library/think/template/driver/File.php',
];
foreach($files as $file){
    //替换目录分隔符
    $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
    if(!is_file($file)){
        echo $file.' not exist'.PHP_EOL;
        continue;
    }
    echo 'chown file: '.$file.PHP_EOL;
    $file_content = file_get_contents($file);
    if(strpos($file_content, 'chmod 0777 %s -R') !== false){
        //已经处理过，不再进行处理
        continue;
    }
    $file_content_array = explode("\n", $file_content);
    $file_content_array_new = [];
    foreach($file_content_array as $file_line){
        $file_content_array_new[] = $file_line;
        //新增文件夹，自动更改用户组
        if(strpos($file_line, 'mkdir') !== false){
            $dir = \ClassLibrary\ClString::getBetween($file_line, 'mkdir', ',', false);
            //去除左侧（
            $dir = \ClassLibrary\ClString::getBetween($dir, '(', '', false);
            //cli模式下文件夹权限修改
            $file_content_array_new[] = "IS_CLI && !IS_WIN && exec(sprintf('chmod 0777 %s -R', $dir));";
        }
    }
    $file_content = implode("\n", $file_content_array_new);
    //重新写入文件
    file_put_contents($file, $file_content);
}
