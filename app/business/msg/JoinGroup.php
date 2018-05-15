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
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class JoinGroup extends MsgHandleBase {

    static public function handle($client_id, $json) {
        //加入组
        if ($json->business_type == 'JoinGroup' && isset($json->group) && !empty($json->group)) {
            Gateway::joinGroup($client_id, $json->group);
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_JOIN_GROUP, 1, 'join group succ!')));
        } else {
            //错误请求
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_JOIN_GROUP, 0, 'join group err!')));
        }
    }

}
