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
use App\business\model\SendMessage;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class SendMessageHandle extends MsgHandleBase {

    static public function handle($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->to_uid) && !empty($json->to_uid) && isset($json->message) && !empty($json->message)) {
            $message_model = new SendMessage();
            if($message_model->send($json->uid,$json->to_uid,$json->message)){
                $msg['messages']='send success';
            }
            $msg['uid'] = $json->uid;
            $msg['to_uid'] = $json->to_uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 0, 'send message err!')));
        }

    }

}
