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
class IndexMessageRequest extends DbRequestBase {
    
    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id'=>$message_type]);
    }
    
    static public function response(ChatServer $chat_server, \stdClass $json, $event_name){
        if ( $json->code == 1) {
            foreach ($json->data->data as &$msg){
                !empty($msg->sender) && $msg->sender->isOnline = $chat_server->isOnline($msg->uid)?1:0;
            }
            $chat_server->sendMessage($json->uid, $event_name, $json->data);
        }else{
            $chat_server->sendMessage($json->uid, $event_name, $json->data);
        }
    }
}
