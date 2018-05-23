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
            $message=$message_model->send($json->uid,$json->to_uid,$json->message);
            if(!empty($message)){
                $msg['messages']=$message;
            }
            $msg['uid'] = $json->uid;
            $msg['to_uid'] = $json->to_uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_MESSAGE, 0, 'send message err!')));
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
            $to_ids=$message_model->sendGroup($json->uid,$json->group_id,$json->message);
            if($to_ids){
                $msg['messages']=$json->message;
                $msg['to_ids']=$to_ids;
            }
            $msg['uid'] = $json->uid;
            $msg['group_id'] = $json->group_id;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_SEND_GROUP_MESSAGE, 0, 'send group message err!')));
        }

    }

    /**
     * @param $client_id
     * @param $json
     * @method 历史聊天记录
     */
    static public function historyMessage($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->to_uid) && !empty($json->to_uid) && isset($json->pageSize) && !empty($json->pageSize)) {
            $message_model = new Message();
            $msg['messages']  = $message_model->getHistoryMessage($json->uid,$json->to_uid,$json->pageSize,$json->indexPage);
            $msg['uid'] = $json->uid;
            $msg['to_uid'] = $json->to_uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HISTORY_MESSAGE, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_HISTORY_MESSAGE, 0, 'get history_message err!')));
        }
    }

    /**
     * @param $client_id
     * @param $json
     * @method 当前聊天记录
     */
    static public function indexMessage($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->to_uid) && !empty($json->to_uid) ) {
            $message_model = new Message();
            $msg['messages']  = $message_model->getIndexMessage($json->uid,$json->to_uid);
            $msg['uid'] = $json->uid;
            $msg['to_uid'] = $json->to_uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_INDEX_MESSAGE, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_INDEX_MESSAGE, 0, 'get index_message err!')));
        }
    }

    /**
     * @param $client_id
     * @param $json
     * @method 未读信息
     */
    static public function unreadMessages($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid)) {
            $message_model = new Message();
            $msg['messages']  = $message_model->getUnreadMessages($json->uid);
            $msg['uid'] = $json->uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_MESSAGES, 1, $msg)));
        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_UNREAD_MESSAGES, 0, 'get unread message err!')));
        }
    }

    /**
     * @param $client_id
     * @param $json
     * @method 未读变已读
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
