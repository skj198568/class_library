# class_library
tp5快速开发常用类库
### composer使用
修改配置
```
"minimum-stability":"dev"
```
添加执行脚本
```
"scripts":{
    "post-install-cmd": [
        "php vendor/skj198568/class_library/create_files.php"
    ],
    "post-update-cmd": [
        "php vendor/skj198568/class_library/create_files.php"
    ]
},
```
#### 执行命令
```
composer require "skj198568/class_library"
```