# class_library
tp5快速开发常用类库
### composer使用
```
composer require "skj198568/class_library"
```
### 修改框架composer.json文件
```
"scripts":{
    "post-update-cmd": [
        "php vendor/skj198568/class_library/create_files.php"
    ]
},
```
### composer更新
```
composer update
```
执行完update，会创建相关文件夹和文件。