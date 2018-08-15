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
    static public function request(ChatServer $chat_server, XObject $obj) {
        //构造登录请求
        $data = array(
            'id' => MsgIds::MESSAGE_LOGIN,
            'sock_id' => $obj->sock_id,
            'session_id' => $obj->session_id,
            'uid' => $obj->uid,
        );
        $chat_server->sendMessageToGateway($data);
    }

    //响应
    static public function response(ChatServer $chat_server, \stdClass $json) {
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
                $chat_server->sendMessage($to_uid->friend_id, 'online_notice', $json->userData);
            }
            //登录大厅
            
        } else {
            //登录失败
            $sock_id = $json->sock_id;
            $chat_server->login_sucess($sock_id, '', true);
        }
    }

}
