<?php
namespace App\business\model;
use App\business\model\Model;

class VerifyMessage extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @param $msg
     * @return array/false
     * @method 创建一条好友验证消息
     */
    public function createVerifyMsg($uid,$to_uid,$msg) {
        $msg_id = $this->insert('en_chat_validate_messages')
            ->cols([
                'uid'=>$uid,
                'to_uid'=>$to_uid,
                'message'=>htmlspecialchars($msg)
            ])
            ->query();
        if(!$msg_id)return false;
        
        return $this->select('*')->from('en_chat_validate_messages')->where('id= :msg_id')->bindValues(['msg_id'=>$msg_id])->row();
    }

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 好友验证消息处理
     */
    public function HandleVerifyMsg($uid,$msg_id,$is_agree) {
        $msg = $this->select("*")->from('en_chat_validate_messages')->where('id='.$msg_id.' and to_uid='.$uid)->row();
        if(empty($msg))return false;
        if($msg['is_handle']<1){//如果验证消息未处理
            $is_handle = intval($is_agree)>0?1:2;
            $this->update('en_chat_validate_messages')->cols(['is_handle'=>$is_handle,'handle_time'=>date("Y-m-d H:i:s")])->where('id='.$msg_id)->query();
            if($is_handle == 1){//同意添加好友关系
                
                // 添加 我与他的好友关系
                $group_id = $this->select("id")->from('en_chat_friend_groups')->where('uid= :uid AND group_name=:group_name')
                                 ->bindValues(array('uid'=>$uid,'group_name'=>'我的好友'))->single();
                if(empty($group_id)){
                    $group_id =  $this->insert('en_chat_friend_groups')->cols([
                        'uid'=>$uid,
                        'group_name'=>'我的好友',
                        'is_group_hair'=>0,
                        'porder'=>0,
                        'create_time'=>date("Y-m-d H:i:s")
                    ])->query();
                }
                // 查找所有分组id
                $group_ids = $this->select('id')->from('en_chat_friend_groups')->where('uid ='.$uid)->column();
                if(!empty($group_ids)){
                    if($this->select('count(*)')->from('en_chat_friends')->where('group_id in('.join(',', $group_ids).') and friend_id='.$msg['uid'])->single()<1){
                        $friend = $this->select('nickname,realname')->from('en_users')->where('id='.$msg['uid'])->row();
                        $this->insert('en_chat_friends')->cols([
                            'group_id'=>$group_id,
                            'friend_id'=>$msg['uid'],
                            'friend_name'=>!empty($friend['nickname'])?$friend['nickname']:$friend['realname'],
                        ])->query();
                    }else{
                        return false;
                    }
                }                
                //添加他与我的好友关系
                $group_id = $this->select("id")->from('en_chat_friend_groups')->where('uid= :uid AND group_name=:group_name')
                ->bindValues(array('uid'=>$msg['uid'],'group_name'=>'我的好友'))->single();
                if(empty($group_id)){
                    $group_id =  $this->insert('en_chat_friend_groups')->cols([
                        'uid'=>$msg['uid'],
                        'group_name'=>'我的好友',
                        'is_group_hair'=>0,
                        'porder'=>0,
                        'create_time'=>date("Y-m-d H:i:s")
                    ])->query();
                }
                $group_ids = $this->select('id')->from('en_chat_friend_groups')->where('uid ='.$msg['uid'])->column();
                if(!empty($group_ids)){
                    if($this->select('count(*)')->from('en_chat_friends')->where('group_id in('.join(',', $group_ids).') and friend_id='.$uid)->single()<1){
                        $myself = $this->select('nickname,realname')->from('en_users')->where('id='.$uid)->row();
                        $this->insert('en_chat_friends')->cols([
                            'group_id'=>$group_id,
                            'friend_id'=>$uid,
                            'friend_name'=>!empty($myself['nickname'])?$myself['nickname']:$myself['realname'],
                        ])->query();
                    }else{
                        return false;
                    }
                }
            }
        }
        return $this->select('*')->from('en_chat_validate_messages')->where('id='.$msg_id)->row();
    }
}
