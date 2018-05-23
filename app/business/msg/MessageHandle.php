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
                    'result'=>'true',
                    'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid,'message'=>$json->message],
                    'msg'=>'发送消息成功！',
                    'data'=>$new_row,
                    'to_uid'=>$json->to_uid,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 1, $return_data)));
            }else{
                $data=[
                    'result'=>'false',
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
                'result'=>'false',
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
        if (isset($json->uid) && !empty($json->uid) && isset($json->group_id) && !empty($json->group_id) && isset($json->message) && !empty($json->message)) {
            $message_model = new Message();
            $return_data['uid'] = $json->uid;
            $return_data['group_id'] = $json->group_id;
            $return_data['sock_id'] = $json->sock_id;
            $return_data['messages']=$json->message;
            $to_ids=$message_model->sendGroup($json->uid,$json->group_id,$json->message);
            if(!empty($to_ids)){
                $return_data['to_ids']=$to_ids;
                $data=[
                    'result'=>'true',
                    'params'=>['uid'=>$json->uid,'group_id'=>$json->group_id,'message'=>$json->message],
                    'msg'=>'群发消息成功！',
                    'content'=>$json->message,
                    'to_ids'=>$to_ids,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 1, $return_data)));
            }else{
                $data=[
                    'result'=>'false',
                    'params'=>['uid'=>$json->uid,'group_id'=>$json->group_id,'message'=>$json->message],
                    'msg'=>'群发消息失败！',
                    'content'=>$json->message,
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 0, $return_data)));
            }

        } else {
            //错误了
            $return_data['uid'] = $json->uid;
            $return_data['group_id'] = $json->group_id;
            $return_data['sock_id'] = $json->sock_id;
            $data=[
                'result'=>'false',
                'params'=>'参数错误！',
                'msg'=>'群发消息失败！',
                'content'=>$json->message,
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
        if(!empty($history_msg)){
            $data=[
                'result'=>'true',
                'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid,'pageSize'=>$json->pageSize,'indexPage'=>$json->indexPage],
                'msg'=>'获取历史记录成功！',
                'data'=>$history_msg,
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HISTORY_MESSAGE, 1, $return_data)));
        } else {
            $data=[
                'result'=>'false',
                'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid,'pageSize'=>$json->pageSize,'indexPage'=>$json->indexPage],
                'msg'=>'获取历史记录失败！',
                'data'=>[],
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HISTORY_MESSAGE, 0, $return_data)));
        }
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
        $index_msg = $message_model->getIndexMessage($json->uid,$json->to_uid);
        if(!empty($index_msg)){
            $data=[
                'result'=>'true',
                'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid],
                'msg'=>'获取当前聊天记录成功！',
                'data'=>$index_msg,
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_INDEX_MESSAGE, 1, $return_data)));
        } else {
            $data=[
                'result'=>'false',
                'params'=>['uid'=>$json->uid,'to_uid'=>$json->to_uid],
                'msg'=>'获取当前聊天记录失败！',
                'data'=>[],
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_INDEX_MESSAGE, 0, $return_data)));
        }
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
        if(!empty($unread_msg)){
            $data=[
                'result'=>'true',
                'params'=>['uid'=>$json->uid],
                'msg'=>'获取未读消息成功！',
                'data'=>$unread_msg,
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_MESSAGES, 1, $return_data)));
        } else {
            $data=[
                'result'=>'false',
                'params'=>['uid'=>$json->uid],
                'msg'=>'获取未读消息失败！',
                'data'=>[],
            ];
            $return_data['data']  = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_MESSAGES, 0, $return_data)));
        }
    }

    /**
     * @param $client_id
     * @param $json
     * @method 处理未读变已读
     */
    static public function unreadToRead($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid)&& !empty($json->messageIds)) {
            $message_model = new Message();
            if($message_model->unreadToRead($json->uid,$json->messageIds)){
                $msg['messages']  = 'success unread to read';
            }else{
                $msg['messages']  = 'fail unread to read';
            }
            $msg['uid'] = $json->uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_TO_READ, 1, $msg)));
        } else {
            //错误了
            $msg['messages']  = 'error unread to read';
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_TO_READ, 0, $msg)));
        }
    }

    /**
     * @param $client_id
     * @param $json
     * @method 处理接收信息
     */
    static public function pickMessage($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->message_id) && !empty($json->message_id)) {
            $message_model = new Message();
            $message=$message_model->pick($json->uid,$json->message_id);
            if(!empty($message)){
                $msg['messages']=$message;
            }
            $msg['uid'] = $json->uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_PICK_MESSAGE, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_PICK_MESSAGE, 0, 'pick message err!')));
        }

    }

}
