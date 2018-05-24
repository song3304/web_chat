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

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-群发信息
     */
    static public function request(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_SEND_GROUP_MESSAGE,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'to_user_ids' => $obj->to_user_ids,
            'message' => $obj->message,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param \stdClass $json
     * @method 响应-群发信息
     */
    static public function response(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'send_group_message', $json->data);
            $new_messages=$json->data->data;
            foreach($new_messages as $to_uid=>$msg){
                $chat_server->sendMessage($to_uid, 'pick_message', $msg);
            }
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'send_group_message', $json->data);
        }
    }

}
