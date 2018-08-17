<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\business\msg;

use App\business\model\VerifyMessage;
use App\business\model\LoginModel;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

class FriendVerifyHandle extends MsgHandleBase {
    /**
     * @param $client_id
     * @param $json
     * @method 添加好友验证
     */
    static public function addFriendVerify($client_id, $json) {
        $return_data['uid'] = $json->uid;
        $return_data['sock_id'] = $json->sock_id;
        if (!empty($json->uid) && !empty($json->to_uid) && !empty($json->msg)) {
            $verify_model = new VerifyMessage();
            $user_model = new LoginModel();
            $verify_new = $verify_model->createVerifyMsg($json->uid,$json->to_uid,$json->msg);
            if($verify_new != false){
                $data = [
                    'result'=>true,
                    'params'=>['userId'=>$json->uid,'toUid'=>$json->to_uid,'msg'=>$json->msg],
                    'msg'=>'发送验证消息成功!',
                    'data'=>$verify_new,
                    'myself' => $user_model->getUser($json->uid),
                    'friend' => $user_model->getUser($json->to_uid)
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_FRIEND_VERIFY, 1, $return_data)));
            }else{
                $data = [
                    'result'=>false,
                    'params'=>['userId'=>$json->uid,'toUid'=>$json->to_uid,'msg'=>$json->msg],
                    'msg'=>'发送验证消息失败，请重新输入!',
                    'data'=>null
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_FRIEND_VERIFY, 0, $return_data)));
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
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_FRIEND_VERIFY, 0, $return_data)));
        }
    }
    /**
     * @param $client_id
     * @param $json
     * @method 处理好友验证
     */
    static public function handleFriendVerify($client_id, $json) {
        $return_data['uid'] = $json->uid;
        $return_data['sock_id'] = $json->sock_id;
        if (!empty($json->msg_id) && isset($json->is_agree)) {
            $verify_model = new VerifyMessage();
            $verify_data = $verify_model->HandleVerifyMsg($json->uid,$json->msg_id,$json->is_agree);
            if($verify_data != false){
                $data = [
                    'result'=>true,
                    'params'=>['userId'=>$json->uid,'msgId'=>$json->msg_id,'is_agree'=>$json->is_agree],
                    'msg'=>'处理验证消息成功!',
                    'data'=>$verify_data
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_FRIEND_VERIFY_HANDLE, 1, $return_data)));
            }else{
                $data = [
                    'result'=>false,
                    'params'=>['userId'=>$json->uid,'msgId'=>$json->msg_id,'is_agree'=>$json->is_agree],
                    'msg'=>'处理验证消息失败，请重新输入!',
                    'data'=>null
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_FRIEND_VERIFY_HANDLE, 0, $return_data)));
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
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_FRIEND_VERIFY_HANDLE, 0, $return_data)));
        }
    }
}
