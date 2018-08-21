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
use App\business\model\XRedis;

class LoginModel extends Model{
    
    public function uid($session_id)
    {
        $xredis = new XRedis();
        $uid = $xredis->get_uid($session_id);
        if (empty($uid)) {
            return false;
        } else {
            return $uid;
        }
    }

    public function getUser($uid)
    {
        return $this->select('u.id,u.nickname,u.realname,u.system_type,u.pic_url AS img,u.phone,u.org_id,o.name as company_name,o.shortName as company_short_name')
                    ->from('en_users as u')->leftjoin('en_org AS o','o.id=u.org_id')
                    ->where('u.id='.$uid)->row();
    }
 
    public function getFriends($uid)
    {
        $user=$this->getUser($uid);
        switch($user['system_type']){
            case 2://撮合员
                return $this->select('user_id AS friend_id')->from('en_collection')->where('match_id='.$uid)->column();
                break;
            case 3://交易商
                return $this->select('match_id AS friend_id')->from('en_collection')->where('user_id='.$uid)->column();
                break;
            default:
                break;
        }

    }
    //获取所有分组好友，群分组好友
    public function getAllFriends($uid)
    {
        //关注盘子的人
        $collection_user_ids = $this->getFriends($uid);
        //所有好友
        $group_ids = $this->select('id')->from('en_chat_friend_groups')->where('uid='.$uid)->column();
        $friend_ids = [];
        if(!empty($group_ids)){
            $friend_ids = $this->select('friend_id')->from('en_chat_friend_groups')->where('group_id in('.join(',', $group_ids).')')->column();
        }
        //所有群成员
        $qun_ids = $this->select('group_id')->from('en_chat_group_members')->where('member_id='.$uid)->column();
        $qun_member_ids =[];
        if(!empty($qun_ids)){
            $qun_member_ids = $this->select('member_id')->from('en_chat_group_members')->where('group_id in('.join(',', $qun_ids).')')->column();
        }
        return array_unique(array_merge($collection_user_ids,$friend_ids,$qun_member_ids));
    }
    
    public function getMembers($uids)
    {
       $this->select('u.id,u.nickname,u.realname,u.system_type,u.pic_url AS img,u.phone,u.org_id,o.name as company_name,o.shortName as company_short_name')->from('en_users as u')
            ->leftjoin('en_org AS o','o.id=u.org_id')
            ->where('u.id in (:ids)')
            ->bindValues(['ids'=>join(',', $uids)])
            ->query();
    }
}
