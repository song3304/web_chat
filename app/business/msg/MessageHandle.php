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
use App\business\model\Message;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class MessageHandle extends MsgHandleBase {

    /**
     * @param $client_id
     * @param $json
     * @method 处理发送单条信息
     */
    static public function sendMessage($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->to_uid) && !empty($json->to_uid) && isset($json->message) && !empty($json->message)) {
            $message_model = new Message();
            $return_data['uid'] = $json->uid;
            $return_data['to_uid'] = $json->to_uid;
            $return_data['sock_id'] = $json->sock_id;
            $new_row = $message_model->send($json->uid,$json->to_uid,$json->message);
            if($new_row != false){
                $data=[
                    'result'=>true,
                    'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid,'message'=>$json->message],
                    'msg'=>'发送消息成功！',
                    'data'=>$new_row,
                    'to_uid'=>$json->to_uid,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 1, $return_data)));
            }else{
                $data=[
                    'result'=>false,
                    'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid,'message'=>$json->message],
                    'msg'=>'发送消息失败！',
                    'data'=>[],
                    'to_uid'=>$json->to_uid,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 0, $return_data)));
            }
        } else {
            //错误了
            $return_data['uid'] = $json->uid;
            $data=[
                'result'=>false,
                'msg'=>'参数错误，发送消息失败！',
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 0, $return_data)));
        }
    }

    /**
     * @param $client_id
     * @param $json
     * @method 处理群发信息
     */
    static public function sendGroupMessage($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->to_user_ids) && !empty($json->to_user_ids) && is_array($json->to_user_ids) && isset($json->message) && !empty($json->message)) {
            $message_model = new Message();
            $return_data['uid'] = $json->uid;
            $return_data['sock_id'] = $json->sock_id;
            $return_data['content']=$json->message;
            $new_messages=$message_model->sendGroup($json->uid,$json->to_user_ids,$json->message);
            if($new_messages!=false){
                $return_data['to_user_ids']=$json->to_user_ids;
                $data=[
                    'result'=>true,
                    'params'=>['uid'=>$json->uid,'to_user_ids'=>$json->to_user_ids,'message'=>$json->message],
                    'msg'=>'群发消息成功!',
                    'data'=>$new_messages,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 1, $return_data)));
            }else{
                $data=[
                    'result'=>false,
                    'params'=>['uid'=>$json->uid,'to_user_ids'=>$json->to_user_ids,'message'=>$json->message],
                    'msg'=>'群发消息失败!',
                    'data'=>null,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 0, $return_data)));
            }

        } else {
            //错误了
            $return_data['uid'] = $json->uid;
            $return_data['sock_id'] = $json->sock_id;
            $data=[
                'result'=>false,
                'params'=>$json,
                'msg'=>'群发消息失败:参数错误!',
                'data'=>null,
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 0, $return_data)));
        }

    }

    /**
     * @param $client_id
     * @param $json
     * @method 处理历史聊天记录
     */
    static public function historyMessage($client_id, $json) {
        $message_model = new Message();
        $return_data['uid'] = $json->uid;
        $return_data['to_uid'] = $json->to_uid;
        $return_data['sock_id'] = $json->sock_id;
        $history_msg=$message_model->getHistoryMessage($json->uid,$json->to_uid,$json->pageSize,$json->indexPage);
        $data=[
            'result'=>true,
            'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid,'pageSize'=>$json->pageSize,'indexPage'=>$json->indexPage],
            'msg'=>!empty($history_msg)?'获取历史记录成功！':'无数据',
            'data'=>!empty($history_msg)?$history_msg:[],
        ];
        $return_data['data']  = $data;
        Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HISTORY_MESSAGE, 1, $return_data)));
    }

    /**
     * @param $client_id
     * @param $json
     * @method 处理当前聊天记录
     */
    static public function indexMessage($client_id, $json) {
        $message_model = new Message();
        $return_data['uid'] = $json->uid;
        $return_data['to_uid'] = $json->to_uid;
        $return_data['sock_id'] = $json->sock_id;
        $return_data['last_time'] = $json->last_time;
        $index_msg = $message_model->getIndexMessage($json->uid,$json->to_uid,$json->last_time);
        $data=[
            'result'=>true,
            'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid,'last_time'=>$json->last_time],
            'msg'=>!empty($index_msg)?'获取当前聊天记录成功！':'无数据',
            'data'=>!empty($index_msg)?$index_msg:[],
        ];
        $return_data['data']  = $data;
        Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_INDEX_MESSAGE, 1, $return_data)));
    }

    /**
     * @param $client_id
     * @param $json
     * @method 处理未读信息
     */
    static public function unreadMessages($client_id, $json) {
        $message_model = new Message();
        $unread_msg = $message_model->getUnreadMessages($json->uid);
        $return_data['uid'] = $json->uid;
        $return_data['sock_id'] = $json->sock_id;
        $data=[
            'result'=>true,
            'params'=>['uid'=>$json->uid],
            'msg'=>!empty($unread_msg)?'获取未读消息成功！':'无数据',
            'data'=>!empty($unread_msg)?$unread_msg:[],
        ];
        $return_data['data']  = $data;
        Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_MESSAGES, 1, $return_data)));
    }

    /**
     * @param $client_id
     * @param $json
     * @method 处理未读变已读
     */
    static public function unreadToRead($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && !empty($json->messageIds) && is_array($json->messageIds)) {
            $return_data['uid'] = $json->uid;
            $return_data['sock_id'] = $json->sock_id;
            $message_model = new Message();
            $message_model->unreadToRead($json->uid,$json->messageIds);
            $data = [
                'result'=>true,
                'params'=>['uid'=>$json->uid,'toUid'=>$json->toUid,'messageIds'=>$json->messageIds],
                'msg'=>'已读成功',
                'data'=>$json->messageIds
            ];
            $return_data['data'] = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_TO_READ, 1, $return_data)));
        } else {
            $return_data['uid'] = $json->uid;
            $data=[
                'result'=>false,
                'params'=> $json,
                'msg'=>'已读失败:参数错误！',
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_TO_READ, 0, $return_data)));
        }
    }

}
