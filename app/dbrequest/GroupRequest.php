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
            'group_type' => $obj->group_type,
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
     * @method 好友转移分组
     */
    static public function requestTransferGroup(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_TRANSFER_GROUP,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'friend_id' => $obj->friend_id,
            'group_id' => $obj->group_id
        );
        $chat_server->sendMessageToGateway($data);
    }    
    /*****************以下代码为回复******************/    
    static public function response(ChatServer $chat_server, \stdClass $json, $event_name){
        $chat_server->sendMessage($json->uid, $event_name, $json->data);
    }
}
