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
use App\business\model\SendGroupMessage;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class SendGroupMessageHandle extends MsgHandleBase {

    static public function handle($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->group_id) && !empty($json->group_id) && isset($json->message) && !empty($json->message)) {
            $message_model = new SendGroupMessage();
            if($message_model->send($json->uid,$json->group_id,$json->message)){
                $msg['messages']='send success';
            }
            $msg['uid'] = $json->uid;
            $msg['group_id'] = $json->group_id;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 0, 'send group message err!')));
        }

    }

}
