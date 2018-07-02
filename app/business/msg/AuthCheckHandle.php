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
use App\business\model\XRedis;

/**
 * Description of AuthCheckHandle
 *
 * @author Xp
 */
class AuthCheckHandle extends MsgHandleBase {

    static public function handle($client_id, $json) {
        //登录
        if (isset($json->sock_id) && !empty($json->sock_id) && isset($json->session_id) && !empty($json->session_id) && isset($json->uid) && !empty($json->uid)) {
            //查找用户是否失效　
            $xredis = new XRedis();
            $uid = $xredis->get_uid($json->session_id);
            if (empty($uid) || $uid != $json->uid) {
                //登陆信息失效了
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_AUTH_CHECK, 0, '')));
            } else {
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_AUTH_CHECK, 1, '')));
            }
            
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_AUTH_CHECK,0, 'authcheck request err!')));
        }
    }

}
