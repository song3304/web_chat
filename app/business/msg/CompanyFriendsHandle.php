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
use App\business\model\ClientFriends;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class CompanyFriendsHandle extends MsgHandleBase {

    static public function handle($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) ) {
            //查找好友分组并返回
            $system_model = new ClientFriends();
            $msg['company_friends']  = $system_model->companyFriends($json->uid);
            $msg['uid'] = $json->uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_COMPANY_FRIENDS, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_COMPANY_FRIENDS, 0, 'get company_friends err!')));
        }

    }

}
