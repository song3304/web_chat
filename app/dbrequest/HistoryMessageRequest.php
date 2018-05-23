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

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求历史记录
     */
    static public function request(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_HISTORY_MESSAGE,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'to_uid' => $obj->to_uid,
            'pageSize' => $obj->pageSize?$obj->pageSize:50,
            'indexPage' => $obj->indexPage?$obj->indexPage:1,
        );
        $chat_server->sendMessageToGateway($data);
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
