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
use App\business\model\Cms;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class CmsHandle extends MsgHandleBase {

    //推送资讯列表
    static public function pushCmsList($client_id, $json) {
        if (!empty($json->uid) && !empty($json->cms_ids) ) {
            $cms_model = new Cms();
            $return_data['uid'] = $json->uid;
            $return_data['cms_ids'] = $json->cms_ids;
            $return_data['sock_id'] = $json->sock_id;
            $is_temp = !empty($json->is_temp)?1:0;
            $new_row = $cms_model->getCmsList($json->cms_ids);
            if($new_row != false){
                $data=[
                    'result'=>true,
                    'params'=>['uid'=>$json->uid,'cms_ids'=>$json->cms_ids],
                    'msg'=>'发送消息成功！',
                    'data'=>$new_row,
                    //'to_uid'=>$json->to_uid,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_HOT_CMS, 1, $return_data)));
            }else{
                $data=[
                    'result'=>false,
                    'params'=>['uid'=>$json->uid,'cms_ids'=>$json->cms_ids],
                    'msg'=>'发送消息失败！',
                    'data'=>[],
                    //'to_uid'=>$json->to_uid,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_HOT_CMS, 0, $return_data)));
            }
        } else {
            //错误了
            $return_data['uid'] = $json->uid;
            $data=[
                'result'=>false,
                'msg'=>'参数错误，发送消息失败！',
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_HOT_CMS, 0, $return_data)));
        }
    }
    //获取资讯详情
    static public function getCmsDetail($client_id, $json){
        if (!empty($json->uid) && isset($json->cms_id)) {
            $cms_model = new Cms();
            $return_data['uid'] = $json->uid;
            $return_data['cms_id'] = $json->cms_id;
            $return_data['sock_id'] = $json->sock_id;
            $new_row = $cms_model->getCmsDetail($json->cms_id);
            if($new_row != false){
                $data=[
                    'result'=>true,
                    'params'=>['uid'=>$json->uid,'cms_id'=>$json->cms_id],
                    'msg'=>'发送消息成功！',
                    'data'=>$new_row,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_GET_HOT_CMS_DETAIL, 1, $return_data)));
            }else{
                $data=[
                    'result'=>false,
                    'params'=>['uid'=>$json->uid,'cms_id'=>$json->cms_id],
                    'msg'=>'发送消息失败！',
                    'data'=>[],
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_GET_HOT_CMS_DETAIL, 0, $return_data)));
            }
        } else {
            //错误了
            $return_data['uid'] = $json->uid;
            $data=[
                'result'=>false,
                'msg'=>'参数错误，发送消息失败！',
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_GET_HOT_CMS_DETAIL, 0, $return_data)));
        }
    }
}
