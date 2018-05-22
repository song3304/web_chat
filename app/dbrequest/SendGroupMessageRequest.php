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
class SendGroupMessageRequest extends DbRequestBase {

    //请求
    static public function request(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_SEND_GROUP_MESSAGE,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'group_id' => $obj->group_id,
            'message' => $obj->message,
        );
        $chat_server->sendMessageToGateway($data);
    }

    //响应
    static public function response(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'send_group_message', $json->messages);
            $chat_server->sendMessage($json->to_ids, 'pick_message', $json->messages);
        } else {
            //失败
        }
    }

}
