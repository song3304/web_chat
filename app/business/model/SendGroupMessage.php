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

class SendGroupMessage extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 群发消息
     */
    public function send($uid,$group_id,$message) {
        $to_ids=$this
            ->select('c.friend_id')
            ->from('en_chat_friends AS c')
            ->where('c.group_id= :group_id')
            ->bindValues(array('group_id'=>$group_id))
            ->query();
        if(!$to_ids){
            return false;
        }
        foreach($to_ids as $v){
            $this->insert('en_chat_messages')
                ->cols([
                    'uid'=>$uid,
                    'to_uid'=>$v['friend_id'],
                    'message'=>htmlspecialchars($message)
                ])
                ->query();
        }
        return true;

    }
}
