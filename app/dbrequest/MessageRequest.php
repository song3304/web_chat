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

    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id'=>$message_type]);
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
            if(!empty($json->data->sender)){//判断是否在线
                $json->data->sender->isOnline = $chat_server->isOnline($json->data->sender->id)?1:0;
            }
            $chat_server->sendMessage($json->to_uid, 'pick_message', $json->data);
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'send_message', $json->data);
        }
    }
    //响应群聊消息、
    static public function responseSendQunMessage(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功,返回给发信人
            $chat_server->sendMessage($json->uid, 'send_qun_message', $json->data);
            //推送给群内所有收信人
            if(!empty($json->data->to_uids)){
                foreach ($json->data->to_uids as $to_uid){
                    $chat_server->sendMessage($json->to_uid, 'pick_qun_message', $json->data->data);
                }
            }
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'send_message', $json->data);
        }
    }

}
