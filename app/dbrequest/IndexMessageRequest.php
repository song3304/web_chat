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

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-当前聊天记录
     */
    static public function request(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_INDEX_MESSAGE,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'to_uid' => $obj->to_uid,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-未读记录
     */
    static public function requestUnread(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_UNREAD_MESSAGES,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-未读变已读
     */
    static public function requestUnreadToRead(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_UNREAD_TO_READ,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'toUid' => $obj->toUid,
            'messageIds' => $obj->messageIds,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param \stdClass $json
     * @method 响应-当前聊天记录
     */
    static public function responseIndexMessage(ChatServer $chat_server, \stdClass $json) {

        $chat_server->sendMessage($json->uid, 'index_message', $json->data);
    }

    /**
     * @param ChatServer $chat_server
     * @param \stdClass $json
     * @method 响应-未读记录
     */
    static public function responseUnread(ChatServer $chat_server, \stdClass $json) {

        $chat_server->sendMessage($json->uid, 'unread_messages', $json->data);

    }

    /**
     * @param ChatServer $chat_server
     * @param \stdClass $json
     * @method 响应-未读变已读
     */
    static public function responseUnreadToRead(ChatServer $chat_server, \stdClass $json) {

        $chat_server->sendMessage($json->uid, 'unread_to_read', $json->data);
    }

}
