<?php
/**
 * run with command 
 * php start.php start
 */

use Workerman\Worker;
// composer 的 autoload 文件
include __DIR__ . '/vendor/autoload.php';
// 自定义 的 autoload 文件
include __DIR__ . '/autoload.php';


// 标记是全局启动
define('GLOBAL_START', 1);

// 加载IO 和 Web
require_once __DIR__ . '/start-chat-server.php';
//require_once __DIR__ . '/start-web.php';

// 运行所有服务
Worker::runAll();
