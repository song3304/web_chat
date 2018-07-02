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
    static public function request(ChatServer $chat_server, XObject $obj) {
        //构造登出请求
        $data = array(
            'id' => MsgIds::MESSAGE_LOGOUT,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
        );
        $chat_server->sendMessageToGateway($data);
    }

    //响应
    static public function response(ChatServer $chat_server, \stdClass $json) {
        //判断是否登出成功
        if (isset($json->code) && $json->code == 1) {
            //登出成功了
            $sock_id = $json->sock_id;
            $uid = $json->uid;
            //给好友推送下线通知
            $to_uids=$json->to_uids;
            foreach($to_uids as $to_uid){
                $chat_server->sendMessage($to_uid->friend_id, 'offline_notice', $json->userData);
            }

        } else {
            //登出失败

        }
    }

}
