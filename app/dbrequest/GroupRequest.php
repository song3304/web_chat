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
class GroupRequest extends DbRequestBase {

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-创建分组
     */
    static public function requestCreate(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_CREATE_GROUP,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'group_name' => $obj->group_name,
            'group_type' => $obj->group_type,
            'userIds' => $obj->userIds,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-删除分组
     */
    static public function requestDeleteGroup(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_DELETE_GROUP,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'group_id' => $obj->group_id,
            'group_type' => $obj->group_type
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-修改分组名
     */
    static public function requestModify(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_MODIFY_GROUP,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'group_id' => $obj->group_id,
            'group_name' => $obj->group_name,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-删除分组中的好友
     */
    static public function requestDeleteFriend(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_DELETE_GROUP_FRIEND,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'group_id' => $obj->group_id,
            'group_type' => $obj->group_type,
            'userIds' => $obj->userIds,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 响应-创建分组
     */
    static public function responseCreate(ChatServer $chat_server, \stdClass $json) {

        $chat_server->sendMessage($json->uid, 'create_group', $json->data);
    }

    /**
 * @param ChatServer $chat_server
 * @param XObject $obj
 * @method 响应-删除分组
 */
    static public function responseDeleteGroup(ChatServer $chat_server, \stdClass $json) {

        $chat_server->sendMessage($json->uid, 'delete_group', $json->data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 响应-修改分组名
     */
    static public function responseModify(ChatServer $chat_server, \stdClass $json) {

        $chat_server->sendMessage($json->uid, 'modify_group', $json->data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 响应-删除分组中的好友
     */
    static public function responseDeleteFriend(ChatServer $chat_server, \stdClass $json) {

        $chat_server->sendMessage($json->uid, 'delete_group_friend', $json->data);
    }

}
