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

class Message extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @param $message
     * @return bool
     * @method 发送单条信息
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
        }else{
            return $this->select('*')->from('en_chat_messages')->where('id= :id')->bindValues(['id'=>$insert_id])->row();
        }
    }

    /**
     * @param $uid
     * @param $group_id
     * @param $message
     * @return bool
     * @method 群发消息
     */
    public function sendGroup($uid,$group_id,$message) {
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
        return $to_ids;
    }

    /**
     * @param $uid
     * @param $message_id
     * @return mixed
     * @method 接收信息
     */
    public function pick($uid,$message_id) {
        return $this->select('id,uid,message,is_read')->from('en_chat_messages')->where('id='.$message_id.' AND to_uid='.$uid)->query();
    }

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
        //可简化为下面查询方法，但是bug，create_time条件未生效
//        $messages=$this
//            ->select('*')
//            ->from('en_chat_messages')
//            ->where('uid='.$uid.' AND to_uid='.$to_uid.' AND create_time>='.$today)
//            ->orWhere('uid='.$to_uid.' AND to_uid='.$uid.' AND create_time>='.$today)
//            ->orderByDesc(array(0=>'create_time'))
//            ->query();
        return $messages;
    }

    /**
     * @param $uid
     * @return mixed
     * @method 获取所有未读信息
     */
    public function getUnreadMessages($uid)
    {
        $messages=$this->select('*')->from('en_chat_messages')->where('to_uid= :to_uid AND is_read=0')->bindValues(array('to_uid'=>$uid))->orderByDesc(array(0=>'create_time'))->query();
        return $messages;
    }

    /**
     * @param $uid
     * @param $to_uid
     * @param $pageSize
     * @param $indexPage
     * @return mixed
     * @method 获取当前好友的历史聊天记录
     */
    public function getHistoryMessage($uid,$to_uid,$pageSize,$indexPage) {
        $history_messages=$this
            ->select('*')
            ->from('en_chat_messages')
            ->where('uid='.$uid.' AND to_uid='.$to_uid)
            ->orWhere('uid='.$to_uid.' AND to_uid='.$uid)
            ->limit($pageSize)
            ->offset(($indexPage-1)*$pageSize)
            ->orderByDesc(array(0=>'create_time'))
            ->query();
        return $history_messages;
    }

    /**
     * @param $uid
     * @param $messageIds
     * @method 未读变已读
     */
    public function unreadToRead($uid,array $messageIds)
    {
        if(empty($messageIds))return false;
        foreach($messageIds as $msgId){
            $this->update('en_chat_messages')->cols(array('is_read'=>1))->where('to_uid='.$uid.' AND id='.$msgId)->query();
        }
        return true;
    }
}