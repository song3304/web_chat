<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\business\msg;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
use App\business\model\Message;
use App\business\model\LoginModel;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class RecentChatListHandle extends MsgHandleBase {
    // 获取最近聊天列表    
    static function handle($client_id, $json) {
        $message_model = new Message();
        $return_data['uid'] = $json->uid;
        $return_data['sock_id'] = $json->sock_id;
        $data=[
            'result'=>true,
            'params'=>['uid'=>$json->uid],
            'msg'=>'获取最近聊天列表成功！',
            'data'=>[
                'recent_users'=>$message_model->getRecentUsers($json->uid),//最近聊天用户列表
                'recent_quns'=>$message_model->getRecentQuns($json->uid) //最近聊天群列表
            ]
        ];
        $return_data['data']  = $data;
        Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_RECENT_LIST, 1, $return_data)));
    }
}
