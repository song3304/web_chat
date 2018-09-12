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

class CmsRequest extends DbRequestBase {
    //请求
    static public function request(ChatServer $chat_server, $data, $message_type) {
        $chat_server->sendMessageToGateway($data+['id' => $message_type]);
    }

    //响应发送热点资讯
    static public function pick_response(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {
            //成功,返回给发信人
            $chat_server->sendMessage($json->uid, 'send_hot_cms', $json->data);
            //大厅通知大厅所有人有消息来了
            foreach ($chat_server->uidConnectionMap as $online_uid => $sockets){
                $chat_server->sendMessage($online_uid, 'pick_hot_cms', $json->data);//通知所有人下线大厅
            }
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'send_hot_cms', $json->data);
        }
    }
    //获取资讯详情处理
    static public function get_detail_response(ChatServer $chat_server, \stdClass $json){
        //判断是否成功
        if ( $json->code == 1) {
            //成功,返回给发信人
            $chat_server->sendMessage($json->uid, 'get_hot_cms_detail', $json->data);
        } else {
            //失败
            $chat_server->sendMessage($json->uid, 'get_hot_cms_detail', $json->data);
        }
    }

}
