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

class Group extends Model{

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 创建自定义分组
     */
    public function createGroup($uid,$group_name,$group_type,$userIds) {
        if(empty($group_type) ||$group_type != 'qun'){ //创建普通分组/群发组
            $group=$this->select('*')->from('en_chat_friend_groups')->where('uid= :uid AND group_name=:group_name')
                        ->bindValues(array('uid'=>$uid,'group_name'=>$group_name))->query();
            if($group)return false;
            
            if($group_type=='group'){
                $is_group_hair=1;
            }else{
                $is_group_hair=0;
            }
            $group_id = $this->insert('en_chat_friend_groups')
                             ->cols(['uid'=>$uid,'group_name'=>htmlspecialchars($group_name),'is_group_hair'=>$is_group_hair])
                             ->query();
            if(!$group_id)return false;
            $this->update('en_chat_friend_groups')->cols(['porder'=>$group_id])->where('id='.$group_id)->query();//更新分组排序
            if(!empty($userIds) && is_array($userIds)){
                foreach($userIds as $friend_id){
                    $this->insert('en_chat_friends')
                    ->cols([
                        'group_id'=>$group_id,
                        'friend_id'=>$friend_id
                    ])
                    ->query();
                }
            }
            return $this->select('*')->from('en_chat_friend_groups')->where('id= :gid')->bindValues(array('gid'=>$group_id))->row();
        }else{ //创建群聊分组
            $group=$this->select('*')->from('en_chat_groups')->where('uid= :uid AND group_name=:group_name')
                        ->bindValues(array('uid'=>$uid,'group_name'=>$group_name))->query();
            if($group)return false;
            
            $group_id = $this->insert('en_chat_groups')->cols(['uid'=>$uid,'group_name'=>htmlspecialchars($group_name)])->query();
            if(!$group_id)return false;
            $this->update('en_chat_groups')->cols(['porder'=>$group_id])->where('id='.$group_id)->query();//更新分组排序
            if(!empty($userIds) && is_array($userIds)){
                foreach($userIds as $member_id){
                    $this->insert('en_chat_group_members')
                    ->cols([
                        'group_id'=>$group_id,
                        'member_id'=>$member_id
                    ])
                    ->query();
                }
            }
            return $this->select('*')->from('en_chat_groups')->where('id= :gid')->bindValues(array('gid'=>$group_id))->row();
        }
    }

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 删除自定义分组
     */
    public function deleteGroup($uid,$group_id,$group_type) {
        if(empty($group_type) ||$group_type != 'qun'){
            $group= $this->select('*')->from('en_chat_friend_groups')->where('id= :gid')->bindValues(array('gid'=>$group_id))->row();
            if($group['uid']!=$uid)return false;
            //此好友组是否为空组
            $friends=$this->select('*')->from('en_chat_friends')->where('group_id=:group_id')->bindValues(array('group_id'=>$group_id))->query();
            if($friends){
                $this->delete('en_chat_friends')->where('group_id=:group_id')->bindValues(array('group_id'=>$group_id))->query();
            }
            $row_count = $this->delete('en_chat_friend_groups')->where('uid= :uid AND id= :group_id')->bindValues(array('uid'=>$uid,'group_id'=>$group_id))->query();
            if(!$row_count)return false;
        }else{
            $group= $this->select('*')->from('en_chat_groups')->where('id= :gid')->bindValues(array('gid'=>$group_id))->row();
            if($group['uid']!=$uid)return false;
            //解散群
            $friends=$this->select('*')->from('en_chat_group_members')->where('group_id=:group_id')->bindValues(array('group_id'=>$group_id))->query();
            //删除消息 群组->个人消息
            $this->delete('en_chat_group_user_messages')->where('group_id=:group_id')->bindValues(array('group_id'=>$group_id))->query();
            //删除群组人员
            $this->delete('en_chat_group_members')->where('group_id=:group_id')->bindValues(array('group_id'=>$group_id))->query();
            //更新这个群id为0
            $this->update('en_chat_group_messages')->cols(['group_id'=>0,'update_time'=>date("Y-m-d H:i:s")])->where('group_id='.$group_id)->query();
            
            $row_count = $this->delete('en_chat_groups')->where('uid= :uid AND id= :group_id')->bindValues(array('uid'=>$uid,'group_id'=>$group_id))->query();
            if(!$row_count)return false;
        }
        return true;
    }

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 修改自定义分组/群聊组名
     */
    public function modifyGroup($uid,$group_id,$group_name,$group_type) {
        if(empty($group_type) || $group_type!= 'qun'){
            //是否存在此组
            $group=$this->select('*')->from('en_chat_friend_groups')->where('id=:group_id AND uid= :uid')->bindValues(array('group_id'=>$group_id,'uid'=>$uid))->row();
            if(!$group || $group['group_name']==$group_name)return false;
            //此组名是否已存在
            $same_group=$this->select('*')->from('en_chat_friend_groups')->where('uid= :uid AND group_name= :group_name')->bindValues(array('uid'=>$uid,'group_name'=>$group_name))->query();
            if($same_group)return false;
            //更改组名
            $row_count = $this->update('en_chat_friend_groups')->cols(array('group_name'))->where('id='.$group_id)->bindValue('group_name', $group_name)->query();
            if(!$row_count)return false;
            return $this->select('*')->from('en_chat_friend_groups')->where('id= :gid')->bindValues(array('gid'=>$group_id))->row();
        }else{
            //是否存在此群
            $group=$this->select('*')->from('en_chat_groups')->where('id=:group_id AND uid= :uid')->bindValues(array('group_id'=>$group_id,'uid'=>$uid))->row();
            if(!$group || $group['group_name']==$group_name)return false;
            //此组名是否已存在
            $same_group=$this->select('*')->from('en_chat_groups')->where('uid= :uid AND group_name= :group_name')->bindValues(array('uid'=>$uid,'group_name'=>$group_name))->query();
            if($same_group)return false;
            //更改组名
            $row_count = $this->update('en_chat_groups')->cols(array('group_name'))->where('id='.$group_id)->bindValue('group_name', $group_name)->query();
            if(!$row_count)return false;
            return $this->select('*')->from('en_chat_groups')->where('id= :gid')->bindValues(array('gid'=>$group_id))->row();
        }
    }
    //修改好友名
    public function modifyFriendName($uid,$friend_id,$friend_name,$group_id,$group_type) {
        if(empty($group_type) || $group_type!= 'qun'){
            //是否存在此组
            $group=$this->select('*')->from('en_chat_friend_groups')->where('id=:group_id AND uid= :uid')->bindValues(array('group_id'=>$group_id,'uid'=>$uid))->row();
            if(!$group)return false;
            //此好友是否已存在
            $same_group=$this->select('*')->from('en_chat_friends')->where('group_id= '.$group_id.' AND friend_id= '.$friend_id)->query();
            if(empty($same_group)) return false;
            //更改组名
            $row_count = $this->update('en_chat_friends')->cols(array('friend_name'))->where('group_id= '.$group_id.' AND friend_id= '.$friend_id)->bindValue('friend_name', $friend_name)->query();
            if(!$row_count)return false;
            return $this->select('*')->from('en_chat_friends')->where('group_id= '.$group_id.' AND friend_id= '.$friend_id)->row();
        }else{
            //是否存在此群
            $group=$this->select('*')->from('en_chat_groups')->where('id=:group_id AND uid= :uid')->bindValues(array('group_id'=>$group_id,'uid'=>$uid))->row();
            if(!$group)return false;
            //此好友是否已存在
            $same_group=$this->select('*')->from('en_chat_group_members')->where('group_id= '.$group_id.' AND member_id= '.$friend_id)->query();
            if(empty($same_group)) return false;
            //更改组名
            $row_count = $this->update('en_chat_group_members')->cols(array('member_name'))->where('group_id= '.$group_id.' AND member_id= '.$friend_id)->bindValue('member_name', $friend_name)->query();
            if(!$row_count)return false;
            return $this->select('*')->from('en_chat_group_members')->where('group_id= '.$group_id.' AND member_id= '.$friend_id)->row();
        }
    }
    
    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 删除好友
     */
    public function deleteGroupFriend($uid,$group_id,$userIds,$group_type) {
        if(empty($group_type) || $group_type!= 'qun'){
            //判断此组是否为当前用户所有
            $group=$this->select('*')->from('en_chat_friend_groups')->where('id='.$group_id)->row();
            if($group['uid']!=$uid)return false;
            foreach($userIds as $friend_id){
                $this->delete('en_chat_friends')->where('group_id='.$group_id.' AND friend_id='.$friend_id)->query();
            }
            //删除对方的好友关系 -- 互删好友
            if(!empty($userIds)){
                $group_ids = $this->select('id')->from('en_chat_friend_groups')->where('uid in('.join(',',$userIds).')')->column();
                if(!empty($group_ids)){
                    $del_cnt = $this->delete('en_chat_friends')->where('group_id in('.join(',',$group_ids).') AND friend_id='.$uid)->query();
                }
            }
            //如果删除好友后该组为空，则同时删除此组
            //        $group_friend=$this->select('*')->from('en_chat_friends')->where('group_id= :group_id')->bindValues(array('group_id'=>$group_id))->query();
            //        if(!$group_friend){
            //            $this->delete('en_chat_friend_groups')->where('id='.$group_id)->query();
            //        }
        }else{
            //$group=$this->select('*')->from('en_chat_groups')->where('id='.$group_id)->row();
            //if($group['uid']!=$uid)return false;
            if(in_array($uid, $userIds)) return false;//不能删除群主自己
            foreach($userIds as $member_id){
                $this->delete('en_chat_group_members')->where('group_id='.$group_id.' AND member_id='.$member_id)->query();
            }
        }
        return true;
    }
    
    public function transferGroupFriend($uid,$friend_id,$group_id,$to_group_id){
        //判断此组是否为当前用户所有
        $group=$this->select('*')->from('en_chat_friend_groups')->where('id='.$group_id)->row();
        if($group['uid']!=$uid)return false;
        //判断这个分组下有没有这个好友
        $group_friend_id = $this->select('id')->from('en_chat_friends')->where('group_id='.$group_id.' and friend_id='.$friend_id)->single();
        if(!empty($group_friend_id)){
            $group=$this->select('*')->from('en_chat_friend_groups')->where('id='.$to_group_id)->row();
            if($group['uid']!=$uid)return false;
            //判断是否已经在分组之中
            if($this->select('count(*)')->from('en_chat_friends')->where('group_id='.intval($to_group_id).' and friend_id='.intval($friend_id))->single()<1){
                $this->update('en_chat_friends')->where('id='.$group_friend_id)->cols(['group_id'=>$to_group_id])->query();
            }
            return true;
        }
        return false;
    }
}
