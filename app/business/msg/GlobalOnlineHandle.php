<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\business\msg;

use App\business\model\LoginModel;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

class GlobalOnlineHandle extends MsgHandleBase {
    
    static public function getOnlineMembers($client_id, $json) {
        $return_data['uid'] = $json->uid;
        $return_data['sock_id'] = $json->sock_id;
        if (!empty($json->uids) && is_array($json->uids)) {
            $user_model = new LoginModel();
            $members = $user_model->getMembers($json->uids);
            if($members != false){
                $data = [
                    'result'=>true,
                    'params'=>['userId'=>$json->uid],
                    'msg'=>'发送验证消息成功!',
                    'data'=>$members
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HALL_MEMBER, 1, $return_data)));
            }else{
                $data = [
                    'result'=>false,
                    'params'=>['userId'=>$json->uid],
                    'msg'=>'发送验证消息失败，请重新输入!',
                    'data'=>null
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HALL_MEMBER, 0, $return_data)));
            }

        } else {
            //错误了
            $data = [
               'result'=>false,
               'params'=>$json,
               'msg'=>'参数错误!',
               'data'=>null
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HALL_MEMBER, 0, $return_data)));
        }
    }
}
