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
 * Description of AuthCheckRequest
 *
 * @author Xp
 */
class AuthCheckRequest extends DbRequestBase {

    //请求
    static public function request(ChatServer $chat_server, XObject $obj,$message_type=MsgIds::MESSAGE_AUTH_CHECK) {
        //构造登录请求
        $data = array(
            'id' => $message_type,
            'sock_id' => $obj->sock_id,
            'session_id' => $obj->session_id,
            'uid' => $obj->uid,
        );
        $chat_server->sendMessageToGateway($data);
    }

    //响应
    static public function response(ChatServer $chat_server, \stdClass $json,$event='') {
        $obj = json_decode($json->msg);
        //判断是否登录成功
        if (isset($json->code) && $json->code == 1) {
            //登陆信息有效，不需要操作
        } else {
            //登陆信息失效
            $chat_server->disconnectByUid($obj->uid, $obj->sock_id);
        }
    }

}
