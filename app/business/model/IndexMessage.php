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

class IndexMessage extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 获取当前好友的当天聊天记录
     */
    public function getIndexMessage($uid,$to_uid) {
        $today=date('Y-m-d',time());
        $send_messages=$this
            ->select('c.id as messageId,c.uid as senderID,c.to_uid as pickerID,c.message as content,c.is_read,c.create_time,u.nickname as userName')
            ->from('en_chat_messages AS c')
            ->leftJoin('en_users AS u','c.to_uid=u.id')
            ->where('c.uid= :uid AND c.to_uid= :to_uid AND c.create_time>= :today')
            ->bindValues(array('uid'=>$uid,'to_uid'=>$to_uid,'today'=>$today))
            ->orderByDesc(array(0=>'c.create_time'))
            ->query();
        $pick_messages=$this
            ->select('c.id as messageId,c.uid as senderID,c.to_uid as pickerID,c.message as content,c.is_read,c.create_time,u.nickname as userName')
            ->from('en_chat_messages AS c')
            ->leftJoin('en_users AS u','c.uid=u.id')
            ->where('c.uid= :uid AND c.to_uid= :to_uid AND c.create_time>= :today')
            ->bindValues(array('uid'=>$to_uid,'to_uid'=>$uid,'today'=>$today))
            ->orderByDesc(array(0=>'c.create_time'))
            ->query();
        $messages=array_merge($send_messages,$pick_messages);
        $tempArr=[];
        foreach($messages as $k=>$v){
            $tempArr[$k]=$v['create_time'];
        }
        array_multisort($tempArr,SORT_DESC,$messages);
        return $messages;
    }
}
