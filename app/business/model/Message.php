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
use App\business\model\LoginModel;

class Message extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @param $message
     * @return bool
     * @method 发送单条信息
     */
    public function send($uid,$to_uid,$message,$is_temp=0) {
        $insert_id = $this
            ->insert('en_chat_messages')
            ->cols([
                'uid'=>$uid,
                'to_uid'=>$to_uid,
                'is_temp'=>$is_temp,
                'message'=>htmlspecialchars($message)
                ])
            ->query();
        if(!$insert_id){
            return false;
        }else{
            return $this->select('*')->from('en_chat_messages')->where('id= :id')->bindValues(['id'=>$insert_id])->row();
        }
    }
    //发送群消息--to_uid 备用参数，@用户可能会用到.
    public function sendQunMessage($uid,$qid,$to_uid,$message){
        $qun_info = $this->select('*')->from('en_chat_groups')->where('id='.$qid)->row();
        if(empty($qun_info)) return false;
        $insert_id = $this
            ->insert('en_chat_group_messages')
            ->cols([
                'uid'=>$uid,
                'group_id'=>$qid,
                'group_name'=>$qun_info['group_name'],
                'message'=>htmlspecialchars($message)
            ])
        ->query();
        if(!$insert_id){
            return false;
        }else{
            //发送给每个人都接收一下
            $member_ids = $this->select('member_id')->from('en_chat_group_members')->where('group_id='.$qid)->column();
            foreach ($member_ids as $member_id){
                $insert_data = [ 'msg_id'=>$insert_id,'to_uid'=>$member_id,'group_id'=>$qid];
                if($member_id == $uid) $insert_data+=['is_read'=>1,'read_time'=>date("Y-m-d H:i:s")];
                $this->insert('en_chat_group_user_messages')
                     ->cols($insert_data)
                     ->query();
            }
            $insert_msg = $this->select('*')->from('en_chat_group_messages')->where('id= :id')->bindValues(['id'=>$insert_id])->row();
            $user_info = $this->select('system_type,pic_url AS user_img')->from('en_users')->where('id='.$uid)->row();
            $user_member_name = $this->select('member_name')->from('en_chat_group_members')->where('group_id='.$qid.' and member_id='.$uid)->single();
            return ['member_ids'=>$member_ids,'insert_msg'=>$insert_msg+$user_info+['user_member_name'=>$user_member_name]];
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
        $user = (new LoginModel)->getUser($uid);
        foreach($to_user_ids as $k=> $v){
            //判断是否需要给这个人发，过滤发送信息
            if($user['system_type'] == 2){ //撮合群发要过滤
                $group_messages = $this->select('message')->from('en_chat_messages')->where("uid=".$v." and to_uid=".$uid." and is_group=1 and create_time>='".date("Y-m-d H:i:s",strtotime('-5 minutes'))."'")->column();
                $message_before_length = mb_strlen($message);
                if(!empty($group_messages)){
                    foreach ($group_messages as $item_msg){
                        $message = str_replace($item_msg, '', $message);
                    }
                    $message_after_length = mb_strlen($message);
                    if($message_before_length>6 && $message_after_length<5){ //去掉 发送信息中 回调信息长度<5,认为原文转发
                        continue;
                    }else{ //去掉回传消息内容，再发送
                        $msgIds[$v]= $this->_insert_chat_message($uid, $v, $message);
                    }
                }else{
                    $msgIds[$v]=$this->_insert_chat_message($uid, $v, $message);
                }
            }else{
                $msgIds[$v]=$this->_insert_chat_message($uid, $v, $message);
            }
        }
        //保存默认群发群信息     catalog_id:11为乙二醇
        $group_set_id=$this->select('id')->from('en_chat_bind_users')->where('user_id= '.$uid.' AND catalog_id=11')->single();
        if(empty($group_set_id)){
            $this->insert('en_chat_bind_users')->cols(['user_id'=>$uid,'catalog_id'=>11,'to_uids'=>join(',',$to_user_ids),'status'=>1])->query();
        }else{
            $this->update('en_chat_bind_users')->cols(array('to_uids'=>join(',',$to_user_ids),'status'=>1,'update_time'=>date("Y-m-d H:i:s")))->where('id='.$group_set_id)->query();
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

    // 插入一条群发信息
    private function _insert_chat_message($uid,$to_uid,$message){
        return $this->insert('en_chat_messages')
            ->cols([
                'uid'=>$uid,
                'to_uid'=>$to_uid,
                'is_group'=>1,
                'message'=>htmlspecialchars($message)
            ])
            ->query();
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
    public function getIndexMessage($uid,$to_uid,$last_time,$messageType) {
        $messages = [];
        if(empty($messageType) || $messageType=="single_chat"){//单聊
            $messages=$this->select("*")->from("en_chat_messages")
                ->where("uid in(".$uid.",".$to_uid.") AND to_uid in(".$uid.",".$to_uid.") AND create_time<='".$last_time."' and create_time>='".date('Y-m-d 00:00:00')."'")
                ->orderByDesc(array(0=>'create_time'))
                ->limit(10)
                ->query();
        }elseif($messageType=="group_chat"){//群聊
            $qid = $to_uid;
            $messages=$this->select("*")->from("en_chat_group_messages")
                ->where("group_id=".$qid." AND create_time<='".$last_time."' and create_time>='".date('Y-m-d H:i:s')."'")
                ->orderByDesc(['create_time'])
                ->limit(10)
                ->query();
        }
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
        foreach ($messages as &$message){
            //if($messages['is_temp']){
                $message['sender'] = (new LoginModel)->getUser($message['uid']);
            //}else{
            //    $messages['sender'] = null;
            //}
        }
        return $messages;
    }
    //获取验证消息
    public function getUnreadVerifyMessage($uid)
    {
        //查询未处理验证消息
        $verify_messages = $this->select('*')->from('en_chat_validate_messages')
                                ->where('(uid='.$uid.' or to_uid='.$uid.') and is_handle=0')
                                ->query();
        return $verify_messages;
    }
    
    //获取群未读信息
    public function getUnreadQunMessage($uid)
    {
        //查询群的未读消息
        $messages = $this->select('m.*')->from('en_chat_group_user_messages as um')
                         ->leftjoin('en_chat_group_messages AS m','um.msg_id=m.id')
                         ->where('um.to_uid='.$uid.' and um.is_read=0')
                         ->query();
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
    public function getHistoryMessage($uid,$to_uid,$pageSize,$indexPage,$messageType) {
        $history_messages = [];
        if(empty($messageType) || $messageType=="single_chat" || $messageType=="user"){//单聊
            $history_messages=$this->select('*')->from('en_chat_messages')
                                    ->where('uid='.$uid.' AND to_uid='.$to_uid)
                                    ->orWhere('uid='.$to_uid.' AND to_uid='.$uid)
                                    ->limit($pageSize)
                                    ->offset(($indexPage-1)*$pageSize)
                                    ->orderByDesc(['create_time'])
                                    ->query();
        }elseif($messageType=="group_chat" || $messageType=="qun"){//群聊
            $qun_id = $to_uid;
            $history_messages=$this->select('*')->from('en_chat_group_messages')
                                   ->where('group_id='.$qun_id)
                                   ->limit($pageSize)
                                   ->offset(($indexPage-1)*$pageSize)
                                   ->orderByDesc(['create_time'])
                                   ->query();
        }
        return $history_messages;
    }

    /**
     * @param $uid
     * @param $messageIds
     * @method 未读变已读
     */
    public function unreadToRead($uid,$messageIds,$messageType)
    {
        $read_time=date('Y-m-d H:i:s',time());
        if(empty($messageType) || $messageType=="single_chat"){//单聊
            foreach($messageIds as $msgId){
                $this->update('en_chat_messages')->cols(array('is_read'=>1,'read_time'=>$read_time))->where('to_uid='.$uid.' AND id='.$msgId)->query();
            }
        }elseif($messageType=="group_chat"){//群聊
            foreach($messageIds as $msgId){
                $this->update('en_chat_group_user_messages')->cols(array('is_read'=>1,'read_time'=>$read_time))->where('to_uid='.$uid.' AND msg_id='.$msgId)->query();
            }
        }
        return true;
    }
}
