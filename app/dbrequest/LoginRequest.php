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
 * Description of LoginRequest
 *
 * @author Xp
 */
class LoginRequest extends DbRequestBase {

    //请求
    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id' => $message_type]);
    }

    //响应
    static public function response(ChatServer $chat_server, \stdClass $json, $event_type='') {
        //判断是否登录成功
        if (isset($json->code) && $json->code == 1) {
            //登录成功了
            $sock_id = $json->sock_id;
            $uid = $json->uid;
            //登录成功
            $chat_server->login_sucess($sock_id, $uid);
            //给好友推送上线通知
            $to_uids=$json->to_uids;
            foreach($to_uids as $to_uid){
                if($to_uid == $uid) continue;
                $chat_server->sendMessage($to_uid, 'online_notice', $json->userData);//好友上线
            }
            //登录大厅
            foreach ($chat_server->uidConnectionMap as $online_uid => $sockets){
                if($to_uid == $uid) continue;
                $chat_server->sendMessage($online_uid, 'online_hall_notice', $json->userData);//通知所有人上线大厅
            }
                
        } else {
            //登录失败
            if(!empty($sock_id)){
                $sock_id = $json->sock_id;
                $chat_server->login_sucess($sock_id, '', true);
            }
        }
    }

}
