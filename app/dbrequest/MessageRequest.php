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
class MessageRequest extends DbRequestBase {

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求发送单人信息
     */
    static public function requestSendMessage(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_SEND_MESSAGE,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'to_uid' => $obj->to_uid,
            'message' => $obj->message,
        );
        $chat_server->sendMessageToGateway($data);
    }


    /**
     * @param ChatServer $chat_server
     * @param \stdClass $json
     * @method 响应发送单人信息
     */
    static public function responseSendMessage(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功,返回给发信人
            $chat_server->sendMessage($json->uid, 'send_message', $json->data);
            //推送给收信人
            $chat_server->sendMessage($json->to_uid, 'pick_message', $json->data);
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'send_message', $json->data);
        }
    }


}
