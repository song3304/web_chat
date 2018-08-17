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
class FriendVerifyRequest extends DbRequestBase {
    //请求
    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id'=>$message_type]);
    }
    /*****************以下代码为回复******************/    
    static public function response(ChatServer $chat_server, \stdClass $json, $event_name){
        $chat_server->sendMessage($json->uid, $event_name, $json->data);
    }
    //添加验证回复
    static public function verifyResponse(ChatServer $chat_server, \stdClass $json)
    {
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'add_friend_verification_message', $json->data);
            //对方在线接收验证消息
            $chat_server->sendMessage($json->data->params->toUid, 'pick_verify_message', ['verifyInfo'=>$json->data->data,'friend'=>$json->data->myself]);
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'add_friend_verification_message', $json->data);
        }
    }
    //同意/拒绝验证回复
    static function verifyHandle(ChatServer $chat_server, \stdClass $json)
    {
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'handle_friend_verification', $json->data);
            //对方在线接收验证消息
            $chat_server->sendMessage($json->data->data->uid, 'pick_handle__verify_message', $json->data->data);
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'handle_friend_verification', $json->data);
        }
    }
}
