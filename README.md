# swoft-tars-rpc
在swoft框架中遵循tars协议的rpc服务


# 如何使用
在swoft项目中

1.composer 安装包
composer require yancjie/tars-rpc dev-master

2.
config\server.php添加配置
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
config\properties\app.php配置

'bootScan' => [
 ....
 'Swoft\TarsRpc\Server\Command',
 'Swoft\TarsRpc\Server\Bootstrap',
 'Swoft\TarsRpc\Server\Packer',
 'Swoft\TarsRpc\Server\Middleware',
 'Swoft\TarsRpc\Server\Validator',
]

3.启动命令：

bin/swoft start

可以看到启动信息：

                              Tars TCP Information
********************************************************************
* TCP | host: 0.0.0.0, port: 9000, type: 1, worker: 1 (Enabled)


4.demo，执行命令：

php test/tcp_tars.php
