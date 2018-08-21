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
    //请求
    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id'=>$message_type]);
    }
    /*****************以下代码为回复******************/    
    static public function response(ChatServer $chat_server, \stdClass $json, $event_name){
        $chat_server->sendMessage($json->uid, $event_name, $json->data);
    }
    
    static public function responseDeleteFriend(ChatServer $chat_server, \stdClass $json)
    {
        if ( $json->code == 1) {
            //成功
            $chat_server->sendMessage($json->uid, 'delete_group_friend', $json->data);
            //对方在线接收被删除好友消息
            $userIds = $json->data->params->userIds;
            if(!empty($userIds)){
                $json_data = $json->data->params;
                foreach ($userIds as $to_uid){
                    $chat_server->sendMessage($to_uid, 'pick_delete_friend', ['userId'=>$json_data->userId,'groupId'=>$json_data->groupId,'groupType'=>$json_data->groupType]);
                }
            }
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'delete_group_friend', $json->data);
        }
    }
}
