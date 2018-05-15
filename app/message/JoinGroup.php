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
 * Description of JoinGroup
 *
 * @author Xp
 */
class JoinGroup extends MsgHandleBase {

    static public function handle(ChatServer $chat_server, $json = null, $socket = null) {
        //此消息处理不关系
        
        if ($json->code == 0) {
            //加入组失败
            
        } else {
            //加入组成功了
            
        }
    }

}
