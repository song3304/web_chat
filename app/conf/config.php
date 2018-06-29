<?php

/*
 * 配置项
 */
return [
    //数据库连接配置
    'database' => [
        'hostname' => '192.168.0.53',
        // 数据库名
        'database' => 'energy',
        // 用户名
        'username' => 'root',
        // 密码
        'password' => '123456',
        // 端口
        'hostport' => '3306',
        // 数据库编码默认采用utf8
        'charset' => 'utf8mb4',
    ],
    //chatserver监听ip和端口
    'socket' => [
        //'tcp_ip' => '192.168.0.53',
        'tcp_ip' => '127.0.0.1',
        'tcp_port' => 8821,
    ],
    //tcpclient配置连接到哪个gateway
    //'gateway' => '192.168.0.53:8888',
    'gateway' => '127.0.0.1:8888',
    //redis配置
    'redis' => [
        //redis服务器地址
        'hostname' => '192.168.0.53',
        //redis服务端口
        'hostport' => 6379,
        //redis密码
        'password' => 'foobared',
        //跟web服务器session前缀一致
        'session_prefix' => 'en_',
    ],
];
