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
    public function sendGroup($uid,$to_user_ids,$message) {
        $msgIds=[];
        foreach($to_user_ids as $k=> $v){
            $msgIds[$v]=$this->insert('en_chat_messages')
                ->cols([
                    'uid'=>$uid,
                    'to_uid'=>$v,
                    'message'=>htmlspecialchars($message)
                ])
                ->query();
        }
        if(empty($msgIds)){
            return false;
        }else{
            $new_messages=[];
            foreach ($msgIds as $k2=>$mid){
                $new_messages[$k2]=[
                    'result'=>true,
                    'params'=>['uid'=>$uid,'to_user_id'=>$k2,'message'=>$message],
                    'msg'=>'群发消息成功!',
                    'data'=>$this->select('*')->from('en_chat_messages')->where('id= :id')->bindValues(['id'=>$mid])->row(),
                ];
            }
            return $new_messages;
        }
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
    public function getIndexMessage($uid,$to_uid,$last_time) {
        $messages=$this
            ->select("*")
            ->from("en_chat_messages")
            ->where("uid=".$uid." AND to_uid=".$to_uid." AND create_time<'".$last_time."'")
            ->orWhere("uid=".$to_uid." AND to_uid=".$uid." AND create_time<'".$last_time."'")
            ->orderByDesc(array(0=>'create_time'))
            ->limit(10)
            ->query();
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
        $read_time=date('Y-m-d H:i:s',time());
        foreach($messageIds as $msgId){
            $this->update('en_chat_messages')->cols(array('is_read'=>1,'read_time'=>$read_time))->where('to_uid='.$uid.' AND id='.$msgId)->query();
        }
        return true;
    }
}
