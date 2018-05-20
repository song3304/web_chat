<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClientFriends
 *
 * @author Xp
 */
namespace App\business\model;
use App\business\model\Model;

class SendMessage extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 发送好友消息
     */
    public function send($uid,$to_uid,$message) {
        $insert_id = $this
            ->insert('en_chat_messages')
            ->cols([
                'uid'=>$uid,
                'to_uid'=>$to_uid,
                'message'=>htmlspecialchars($message)
                ])
            ->query();
        if(!$insert_id){
            return false;
        }
        return true;
    }
}
