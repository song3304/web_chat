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
class CompanyFriendsRequest extends DbRequestBase {

    /**
     * @param ChatServer $chat_server
     * @param XObject $obj
     * @method 请求-好友分组
     */
    static public function request(ChatServer $chat_server, XObject $obj) {
        //构造请求
        $data = array(
            'id' => MsgIds::MESSAGE_COMPANY_FRIENDS,
            'sock_id' => $obj->sock_id,
            'uid' => $obj->uid,
        );
        $chat_server->sendMessageToGateway($data);
    }

    /**
     * @param ChatServer $chat_server
     * @param \stdClass $json
     * @method 响应-好友分组
     */
    static public function response(ChatServer $chat_server, \stdClass $json) {
        //判断是否成功
        if ( $json->code == 1) {//成功
            //判断每个好友是否在线
            $return_data=$json->data;
            $all_groups=$return_data->data;
            foreach($all_groups->default_groups as $k=>$v){
                foreach($v->friends as $k2=>$v2){
                    $v2->isOnline=$chat_server->isOnline($v2->friend_id)?1:0;
                }
            }
            foreach($all_groups->define_groups as $k=>$v){
                foreach($v->friends as $k2=>$v2){
                    $v2->isOnline=$chat_server->isOnline($v2->friend_id)?1:0;
                }
            }
	        $group_friends=$return_data;
            $chat_server->sendMessage($json->uid, 'company_friends', $group_friends, $json->sock_id);
        } else {//失败
            $return_data=$json->data;
            $chat_server->sendMessage($json->uid, 'company_friends', $return_data, $json->sock_id);
        }
    }

}
