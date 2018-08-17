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

    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id'=>$message_type]);
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
