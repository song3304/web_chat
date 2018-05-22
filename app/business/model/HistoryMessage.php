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

class HistoryMessage extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 获取当前好友的历史聊天记录
     */
    public function getHistoryMessage($uid,$to_uid,$pageSize,$indexPage) {
        $send_messages=$this
            ->select('*')
            ->from('en_chat_messages')
            ->where('uid='.$uid.' AND to_uid='.$to_uid)
            ->orWhere('uid='.$to_uid.' AND to_uid='.$uid)
            ->limit($pageSize)
            ->offset(($indexPage-1)*$pageSize)
            ->orderByDesc(array(0=>'create_time'))
            ->query();
        return $send_messages;
    }
}
