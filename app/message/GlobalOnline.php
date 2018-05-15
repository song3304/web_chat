<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\message;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
use App\message\MsgHandleBase;
use App\ChatServer;

/**
 * 获取全局目前在线人数
 *
 * @author Xp
 */
class GlobalOnline extends MsgHandleBase {

    static public function handle(ChatServer $chat_server, $json = null, $socket = null) {
        //需要登录
        if (!$chat_server->isLogin($socket)) {
            //未登录
            $socket->emit('logout');
            return;
        }

        //已登录人数
        $count_authed = count($chat_server->uidConnectionMap);
        //未登录人数
        $count_unauthed = count($chat_server->connectionMap);
        //已登录终端
        $count_terminal = 0;
        foreach ($chat_server->uidConnectionMap as $map) {
            $count_terminal += count($map);
        }
        echo($count_authed . '  ' . $count_unauthed . '  ' . $count_terminal . '  ');
        $socket->emit('global_online', $count_authed, $count_unauthed, $count_terminal);
    }

}
