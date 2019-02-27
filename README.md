# swoft-tars-rpc
在swoft框架中遵循tars协议的rpc服务

# 依赖tars打包解包的扩展，请自行编译安装

    https://github.com/TarsPHP/tars-extension/tree/a58eca33a31f0d418401b71393a25b4e85d315bc

# 如何使用
在swoft项目中

### 1.composer 安装包
composer require yancjie/tars-rpc dev-master

###  2.0 将composer安装包中的test/TarsServerListener.php 文件放入项目文件中app\Boot

###  2.1 config\server.php添加配置

    'tcp-tars' => [
    
        'host' => env('TCP_HOST', '0.0.0.0'),
        'port' => env('TCP_PORT', 9000),
        'mode' => env('TCP_MODE', SWOOLE_PROCESS),
        'type' => env('TCP_TYPE', SWOOLE_SOCK_TCP),
        'package_max_length' => env('TCP_PACKAGE_MAX_LENGTH', 2048),
        'open_eof_check' => env('TCP_OPEN_EOF_CHECK', false),
        'open_eof_split' => env('TCP_OPEN_EOF_SPLIT', false),
        'package_eof' => "\r\n",
    ],


###  2.2 config\properties\app.php配置

    'bootScan' => [
        ....
        'Swoft\\TarsRpc\\Server\\'
    ]


### 3.启动命令：

bin/swoft start

可以看到启动信息：

                                  Tars TCP Information
    ********************************************************************
    * TCP | host: 0.0.0.0, port: 9000, type: 1, worker: 1 (Enabled)

### 4.demo，执行命令：
    php test/tcp_tars.php
