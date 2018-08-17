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
class HistoryMessageRequest extends DbRequestBase {

    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id'=>$message_type]);
    }
    /**
     * @param ChatServer $chat_server
     * @param \stdClass $json
     * @method 响应历史记录
     */
    static public function response(ChatServer $chat_server, \stdClass $json) {
        $chat_server->sendMessage($json->uid, 'history_message', $json->data);
    }

}
