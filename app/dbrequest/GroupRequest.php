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
     * @method 请求创建分组
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
     * @method 请求删除分组
     */
    static public function requestDeleteGroup(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_DELETE_GROUP,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'group_id' => $obj->group_id,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求修改分组名
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
     * @method 请求删除分组中的好友
     */
    static public function requestDeleteFriend(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_DELETE_GROUP_FRIEND,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
            'group_id' => $obj->group_id,
            'userIds' => $obj->userIds,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 响应创建分组
     */
    static public function responseCreate(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'create_group', $json->messages);
        } else {
            //失败
        }
    }

    /**
 * @param ChatServer $chat_server
 * @param XObject $obj
 * @method 响应删除分组
 */
    static public function responseDeleteGroup(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'delete_group', $json->messages);
        } else {
            //失败
        }
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 响应修改分组名
     */
    static public function responseModify(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'modify_group', $json->messages);
        } else {
            //失败
        }
    }

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 响应删除分组中的好友
     */
    static public function responseDeleteFriend(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'delete_group_friend', $json->messages);
        } else {
            //失败
        }
    }

}
