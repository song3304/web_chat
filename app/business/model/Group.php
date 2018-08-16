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
        $group=$this
            ->select('*')
            ->from('en_chat_friend_groups')
            ->where('uid= :uid AND group_name=:group_name')
            ->bindValues(array('uid'=>$uid,'group_name'=>$group_name))
            ->query();
        if($group)return false;

        if($group_type=='group'){
            $is_group_hair=1;
        }else{
            $is_group_hair=0;
        }
        $group_id = $this->insert('en_chat_friend_groups')
            ->cols([
                'uid'=>$uid,
                'group_name'=>htmlspecialchars($group_name),
                'is_group_hair'=>$is_group_hair,
            ])
            ->query();
        if(!$group_id)return false;
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
    }

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 删除自定义分组
     */
    public function deleteGroup($uid,$group_id) {
        $group= $this->select('*')->from('en_chat_friend_groups')->where('id= :gid')->bindValues(array('gid'=>$group_id))->row();
        if($group['uid']!=$uid)return false;
        //此好友组是否为空组
        $friends=$this->select('*')->from('en_chat_friends')->where('group_id=:group_id')->bindValues(array('group_id'=>$group_id))->query();
        if($friends){
            $this->delete('en_chat_friends')->where('group_id=:group_id')->bindValues(array('group_id'=>$group_id))->query();
        }
        $row_count = $this->delete('en_chat_friend_groups')->where('uid= :uid AND id= :group_id')->bindValues(array('uid'=>$uid,'group_id'=>$group_id))->query();
        if(!$row_count)return false;
        return true;
    }

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 修改自定义分组
     */
    public function modifyGroup($uid,$group_id,$group_name) {
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
    }

    /**
     * @param $uid
     * @param $to_uid
     * @return array
     * @method 删除好友
     */
    public function deleteGroupFriend($uid,$group_id,$userIds) {
        //判断此组是否为当前用户所有
        $group=$this->select('*')->from('en_chat_friend_groups')->where('id='.$group_id)->row();
        if($group['uid']!=$uid)return false;
        foreach($userIds as $friend_id){
            $this->delete('en_chat_friends')->where('group_id='.$group_id.' AND friend_id='.$friend_id)->query();
        }
        //如果删除好友后该组为空，则同时删除此组
//        $group_friend=$this->select('*')->from('en_chat_friends')->where('group_id= :group_id')->bindValues(array('group_id'=>$group_id))->query();
//        if(!$group_friend){
//            $this->delete('en_chat_friend_groups')->where('id='.$group_id)->query();
//        }
        return true;
    }
    
    public function 
}
