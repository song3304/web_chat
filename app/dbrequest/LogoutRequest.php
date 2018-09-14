<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\dbrequest;

use App\dbrequest\DbRequestBase;
use App\business\MsgIds;
use App\XObject;
use App\ChatServer;

/**
 * Description of LogoutRequest
 *
 * @author Xp
 */
class LogoutRequest extends DbRequestBase {

    //请求
    static public function request(ChatServer $chat_server, $data,$message_type= MsgIds::MESSAGE_LOGOUT) {
        $chat_server->sendMessageToGateway($data+['id'=>$message_type]);
    }

    //响应
    static public function response(ChatServer $chat_server, \stdClass $json, $event_type) {
        //判断是否登出成功
        if (isset($json->code) && $json->code == 1) {
            //登出成功了
            $sock_id = $json->sock_id;
            $uid = $json->uid;
            //给好友推送下线通知
            $to_uids=$json->to_uids;
            foreach($to_uids as $to_uid){
                if($to_uid == $uid) continue;
                $chat_server->sendMessage($to_uid, 'offline_notice', $json->userData);
            }
            //大厅踢出
            foreach ($chat_server->uidConnectionMap as $online_uid => $sockets){
                if($online_uid == $uid) continue;
                $chat_server->sendMessage($online_uid, 'offline_hall_notice', $json->userData);//通知所有人下线大厅
            }
        } else {
            //登出失败

        }
    }

}
