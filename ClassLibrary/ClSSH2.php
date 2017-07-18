<?php
/**
 * Created by PhpStorm.
 * User: skj19
 * Date: 2017/2/28
 * Time: 18:15
 */

namespace ClassLibrary;

/**
 * SSH操作
 * Class ClSSH
 * MySQL增量备份：mysqldump -uroot -p123456 --apply-slave-statements --master-data=2 --single-transaction --flush-logs --databases www.klf.t | gzip > today.sql.gz
 * @package ClassLibrary
 */
class ClSSH2
{

    /**
     * ssh链接resource
     * @var null|resource
     */
    private $ssh = null;

    private $commands_array = [];

    /**
     * 构造函数
     * ClSSH2 constructor.
     * @param $ip_or_domain
     * @param $user
     * @param $password
     * @param int $port
     */
    public function __construct($ip_or_domain, $user, $password, $port = 22)
    {
        $this->ssh = ssh2_connect($ip_or_domain, $port);
        if (empty($this->ssh)) {
            exit('ip or domain or post error, please check.'.PHP_EOL);
        }
        if (!ssh2_auth_password($this->ssh, $user, $password)) {
            exit('user or password error, please check.'.PHP_EOL);
        }
    }

    /**
     * 执行命令
     * @param $commands
     * @return string
     */
    public function exec($commands)
    {
        if (is_array($commands)) {
            $commands = implode(' && ', $commands);
        }
        $this->commands_array[] = $commands;
        $stream = ssh2_exec($this->ssh, $commands);
        $err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        $io_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);

        stream_set_blocking($err_stream, true);
        stream_set_blocking($io_stream, true);

        $result_err = stream_get_contents($err_stream);
        $result_io = stream_get_contents($io_stream);
        if(!empty($result_err)){
            log_info('result_err:', $result_err);
            log_info('result_io:', $result_io);
        }
        return $result_io;
    }

    /**
     * 进入目录
     * @param string $path
     * @return string
     */
    public function cd($path = '..')
    {
        return $this->exec('cd ' . $path);
    }

    /**
     * 下载文件
     * @param $remote_file_absolute_url
     * @param $local_file_absolute_url
     * @return bool
     */
    public function down($remote_file_absolute_url, $local_file_absolute_url)
    {
        return ssh2_scp_recv($this->ssh, $remote_file_absolute_url, $local_file_absolute_url);
    }

    /**
     * 上传文件
     * @param $local_file_absolute_url
     * @param $remote_file_absolute_url
     * @param int $mode
     * @return bool
     */
    public function upload($local_file_absolute_url, $remote_file_absolute_url, $mode = 0777)
    {
        return ssh2_scp_send($this->ssh, $local_file_absolute_url, $remote_file_absolute_url, $mode);
    }

    /**
     * 查看文件夹list
     * @param $dir_absolute_url
     * -a 列出目录所有文件，包含以.开始的隐藏文件;
     * -A 列出除.及..的其它文件;
     * -r 反序排列;
     * -t 以文件修改时间排序;
     * -S 以文件大小排序;
     * -h 以易读大小显示;
     * -l 除了文件名之外，还将文件的权限、所有者、文件大小等信息详细列出来
     * @return string
     */
    public function ls($dir_absolute_url)
    {
        $files = $this->exec(sprintf('ls -lA %s', $dir_absolute_url));
        $files = explode(PHP_EOL, $files);
        foreach($files as $k => $v){
            if(empty($v) || strpos($v, 'total') === 0){
                unset($files[$k]);
            }else{
                $v = explode(' ', ClString::spaceManyToOne($v));
                $v[5] = date('Y-m-d H:i:s', strtotime($v[5].' '.$v[6].' '.$v[7]));
                $files[$k] = ['name' => $v[8], 'size' => $v[4], 'date' => $v[5]];
            }
        }
        return array_values($files);
    }

    /**
     * 文件是否存在
     * @param $file_absolute_url
     * @return string
     */
    public function existsFile($file_absolute_url)
    {
        return $this->exec(sprintf('! -f "%s"', $file_absolute_url));
    }

    /**
     * 文件夹是否存在
     * @param $dir_absolute_url
     * @return string
     */
    public function existsDir($dir_absolute_url){
        return $this->exec(sprintf('! -d "%s"', $dir_absolute_url));
    }

    /**
     * 删除文件
     * @param $file_absolute_url
     * @return string
     */
    public function rm($file_absolute_url)
    {
        return $this->exec(sprintf('rm -f %s', $file_absolute_url));
    }

    /**
     * 移动文件
     * @param $old_file_absolute_url
     * @param $new_file_absolute_url
     * @return string
     */
    public function mv($old_file_absolute_url, $new_file_absolute_url)
    {
        return $this->exec(sprintf('mv %s %s ', $old_file_absolute_url, $new_file_absolute_url));
    }

    /**
     * 移动文件至文件下
     * @param $files_absolute_url
     * @param $dir_absolute_url
     * @return string
     */
    public function mvFilesToDir($files_absolute_url, $dir_absolute_url){
        if(is_array($files_absolute_url)){
            $files_absolute_url = implode(' ', $files_absolute_url);
        }
        return $this->exec(sprintf('mv %s %s', $files_absolute_url, $dir_absolute_url));
    }

    /**
     * 创建文件夹
     * @param $dir_absolute_url
     * @return string
     */
    public function mkDir($dir_absolute_url)
    {
        return $this->exec(sprintf('mkdir -m 777 -p %s', $dir_absolute_url));
    }

    /**
     * 删除文件夹
     * @param $dir_absolute_url
     * @return string
     */
    public function rmDir($dir_absolute_url)
    {
        return $this->exec(sprintf('rm -rf %s', $dir_absolute_url));
    }

    /**
     * 更改类型
     * @param $file_or_dir_absolute_url
     * @param int $mode
     * @return string
     */
    public function chmod($file_or_dir_absolute_url, $mode = 0777){
        return $this->exec(sprintf('chmod %s %s', $mode, $file_or_dir_absolute_url));
    }

    /**
     * 创建软连接
     * @param $source_file_absolute_url
     * @param $target_file_absolute_url
     * @return string
     */
    public function lns($source_file_absolute_url, $target_file_absolute_url)
    {
        return $this->exec(sprintf('ln -s %s %s', $source_file_absolute_url, $target_file_absolute_url));
    }

    /**
     * 查看当前路径
     * @return string
     */
    public function pwd(){
        return $this->exec('pwd');
    }

    /**
     * 获取文件内容
     * @param $file_absolute_url
     * @return string
     */
    public function cat($file_absolute_url){
        return $this->exec(sprintf('cat %s', $file_absolute_url));
    }

    /**
     * 合并文件
     * @param $new_file
     * @param array $files
     * @return string
     */
    public function catCombine($new_file, $files = []){
        if(is_array($files)){
            $files = implode(' ', $files);
        }
        return $this->exec(sprintf('cat %s > %s', $files, $new_file));
    }

    /**
     * 创建压缩文件
     * @param $saved_tar_gz_file_absolute_url
     * @param array $files
     * @return string
     */
    public function tarCreate($saved_tar_gz_file_absolute_url, $files = []){
        return $this->exec(sprintf('tar -czf %s %s', $saved_tar_gz_file_absolute_url, is_array($files) ? implode(' ', $files) : $files));
    }

    /**
     * 解压文件
     * @param $tar_gz_file_absolute_url
     * @param string $to_dir 解压目的文件夹
     * @return string
     */
    public function tarExtract($tar_gz_file_absolute_url, $to_dir = ''){
        if(!empty($to_dir)){
            $this->mkDir($to_dir);
            $this->cd($to_dir);
        }
        return $this->exec(sprintf('tar -xzf %s', $tar_gz_file_absolute_url));
    }

    /**
     * 查看磁盘使用率
     * @return string
     */
    public function df(){
        return $this->exec('df -h');
    }

    /**
     * 查看文件夹使用情况
     * @param $dir_absolute_url
     * @return string
     */
    public function du($dir_absolute_url)
    {
        return $this->exec(sprintf('du -ah --max-depth=1 -c %s | sort -nr', $dir_absolute_url));
    }

    /**
     * 获取时间
     * @param string $format
     * @param string $file_absolute_url 如果不为空，则返回该文件的最后修改时间
     * @return false|string
     */
    public function date($format = 'Y-m-d H:i:s', $file_absolute_url = ''){
        if(empty($file_absolute_url)){
            $timestamp = $this->exec('date +%s');
        }else{
            $timestamp = $this->exec(sprintf('date +%s -r', $file_absolute_url));
        }
        return date($format, intval($timestamp));
    }

    /**
     * 复制文件
     * @param $source_file_absolute_url
     * @param $target_file_absolute_url
     * @return false|string
     */
    public function cp($source_file_absolute_url, $target_file_absolute_url){
        return $this->exec(sprintf('cp -rfd %s %s', $source_file_absolute_url, $target_file_absolute_url));
    }

    /**
     * 创建文件夹
     * @param $file_absolute_url
     * @return string
     */
    public function touch($file_absolute_url){
        //先创建文件夹
        $this->mkDir(pathinfo($file_absolute_url)['dirname']);
        return $this->exec(sprintf('touch %s', $file_absolute_url));
    }

    public function grep(){

    }

    public function ps(){

    }

    public function top(){

    }

    public function kill(){

    }

    public function free(){
        $info = $this->exec('free');

    }

    public function __destruct()
    {
        log_info($this->commands_array);
    }

}