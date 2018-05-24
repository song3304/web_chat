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

    /**
     * @param $client_id
     * @param $json
     * @method 处理-好友分组
     */
    static public function handle($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid)) {
            //查找好友分组并返回
            $model = new ClientFriends();
            $return_data['uid'] = $json->uid;
            $return_data['sock_id'] = $json->sock_id;
            $company_friends = $model->companyFriends($json->uid);
            $data=[
                'result'=>true,
                'params'=>['uid'=>$json->uid],
                'msg'=>'获取好友分组成功！',
                'data'=>$company_friends,
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_COMPANY_FRIENDS, 1, $return_data)));
        }else{
            $data=[
                'result'=>false,
                'params'=>['uid'=>'参数错误'],
                'msg'=>'获取好友分组失败！',
                'data'=>json_encode(['default_groups'=>[],'define_groups'=>[]]),
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_COMPANY_FRIENDS, 0, $return_data)));
        }
    }

}
