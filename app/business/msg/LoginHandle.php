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
use App\business\model\LoginModel;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class LoginHandle extends MsgHandleBase {

    static public function handle($client_id, $json) {
        //登录
        if (isset($json->sock_id) && !empty($json->sock_id) && isset($json->session_id) && !empty($json->session_id)) {
            //查找用户id并返回
            $login_model = new LoginModel();
            $session_user_id = $login_model->uid($json->session_id);
            $msg['sock_id'] = $json->sock_id;
            if (empty($session_user_id)) {
                //没有登录信息
                $msg['msg'] = 'login info empty!';
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_LOGIN, 0, $msg)));
            } else {
                $msg['uid'] = $session_user_id;
                $msg['userData'] = $login_model->getUser($session_user_id);
                $to_uids = $login_model->getAllFriends($session_user_id);
                $msg['to_uids'] = !empty($to_uids)?$to_uids:[];
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_LOGIN, 1, $msg)));
            }
            
        } else {
            //错误了
            $msg['msg'] = 'login request err!';
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_LOGIN, 0, $msg)));
        }
    }

}
