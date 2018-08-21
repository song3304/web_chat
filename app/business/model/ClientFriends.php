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

class ClientFriends extends Model{

    /**
     * @param $uid
     * @return mixed
     * @method 获取用户好友信息,根据公司进行分组
     */
    public function companyFriends($uid) {
        $system_type = $this->systemType($uid);
        //判断系统(1.admin, 2.match 3.trade 4.index)，并获取好友
        switch ($system_type){
            case 1:
                break;
            case 2:
                return $this->matchHasFriend($uid);
                break;
            case 3:
                return $this->tradeHasFriend($uid);
                break;
            case 4:
                break;
            default:
                break;
        }
    }

    /**
     * @param $uid
     * @return mixed
     * @method 撮合员的好友
     */
    public function matchHasFriend($uid){
        //所有好友 SELECT c.user_id,c.match_id,c.user_org_id,o.name AS trade_org_name,u.nickname AS trader_nickname,u.realname as trader_realname  from en_collection AS c LEFT JOIN en_orgs AS o on c.user_org_id=o.id LEFT JOIN en_users AS u on u.id=c.user_id where c.match_id='35';
        $default_group_friends=$define_group_friends=[];
        //step1.查询所有默认分组
        //SELECT DISTINCT c.user_org_id,o.name AS trade_org_name  from en_collection AS c LEFT JOIN en_orgs AS o on c.user_org_id=o.id  where c.match_id='35';
        $default_groups=$this
            ->select('c.user_org_id AS org_id,o.name AS org_name')
            ->distinct()
            ->from('en_collection AS c')
            ->leftjoin('en_orgs AS o','c.user_org_id=o.id')
            ->where('c.match_id= :id')
            ->bindValues(array('id'=>$uid))
            ->query();
        //step2.查询默认分组下的好友
        //SELECT c.user_id,u.nickname,u.realname from en_collection AS c LEFT JOIN en_users AS u on c.user_id=u.id  where c.match_id='35' AND c.user_org_id='7';
        foreach($default_groups as $k=>$v){
            $default_group_friends[$k]['group_name']=$v['org_name'];
            $default_group_friends[$k]['group_id']=$v['org_id'];
            $default_group_friends[$k]['friends']=$this
                ->select('c.user_id AS friend_id,u.nickname,u.realname,u.pic_url AS img')
                ->from('en_collection AS c')
                ->leftjoin('en_users AS u','c.user_id=u.id')
                ->where('c.match_id= :id AND c.user_org_id= :user_org_id')
                ->bindValues(array('id'=>$uid,'user_org_id'=>$v['org_id']))
                ->query();
        }
        return ['default_groups'=>$default_group_friends,'define_groups'=>$this->_getGroupFriendsList($uid)];
    }

    /**
     * @param $uid
     * @return mixed
     * @method 交易员的好友
     */
    public function tradeHasFriend($uid){
        //获取所有好友  SELECT c.user_id,c.match_id,c.match_org_id,o.name AS match_org_name,u.nickname AS matcher_nickname,u.realname as matcher_realname  from en_collection AS c LEFT JOIN en_orgs AS o on c.match_org_id=o.id LEFT JOIN en_users AS u on u.id=c.match_id where c.user_id='54';
        $default_group_friends=$define_group_friends=[];
        //step1.查询所有默认分组
        //SELECT DISTINCT c.match_org_id,o.name AS match_org_name  from en_collection AS c LEFT JOIN en_orgs AS o on c.match_org_id=o.id  where c.user_id='54';
        $default_groups=$this
            ->select('c.match_org_id AS org_id,o.name AS org_name')
            ->distinct()
            ->from('en_collection AS c')
            ->leftjoin('en_orgs AS o','c.match_org_id=o.id')
            ->where('c.user_id= :id')
            ->bindValues(array('id'=>$uid))
            ->query();
        //step2.查询默认分组下的好友
        //SELECT c.match_id,u.nickname AS matcher_nickname,u.realname AS matcher_realname from en_collection AS c LEFT JOIN en_users AS u on c.match_id=u.id  where c.user_id='54' AND c.match_org_id='32';
        foreach($default_groups as $k=>$v){
            $default_group_friends[$k]['group_name']=$v['org_name'];
            $default_group_friends[$k]['group_id']=$v['org_id'];
            $default_group_friends[$k]['friends'] = $this
                ->select('c.match_id AS friend_id,u.nickname,u.realname,u.pic_url AS img')
                ->from('en_collection AS c')
                ->leftjoin('en_users AS u','c.match_id=u.id')
                ->where('c.user_id= :id AND c.match_org_id= :match_org_id')
                ->bindValues(array('id'=>$uid,'match_org_id'=>$v['org_id']))
                ->query();
        }
        return ['default_groups'=>$default_group_friends,'define_groups'=>$this->_getGroupFriendsList($uid)];
    }
    //查询验证加好友 信息
    public function getValidateFriendsMsg($uid)
    {
        //未处理验证消息
        $verify_messages = $this->select("*")->from('en_chat_validate_messages')->where('(uid='.$uid.' or to_uid='.$uid.') and is_handle=0')->query();
        $user_model = new LoginModel;
        foreach ($verify_messages as &$msg){
            $msg['user_info'] = $user_model->getUser($msg['uid']);
            $msg['to_user_info'] = $user_model->getUser($msg['to_uid']);
        }
        return $verify_messages;
    }
    //查询群及好友 信息
    public function getQunFriendsList($uid)
    {
        $qun_ids = $this->select('group_id')->from('en_chat_group_members')->where('member_id='.$uid)->column();
        $define_quns=$this->select('id AS group_id,group_name,uid as owner_id')
                          ->from('en_chat_groups')
                          ->where('id in ('.join($qun_ids, ',').')')
                          ->orderByASC(['porder'])
                          ->query();
        //4.查询自定义下的所有好友
        $user_model = new LoginModel;
        foreach ($define_quns as &$v){
            $members = $this->select('member_id,member_name')->from('en_chat_group_members')->where('group_id='.$v['group_id'])->query();
            if(empty($members)){
                $v['members'] = [];
            }else{
                $members = array_column($members, 'member_name','member_id');
                $members_info = $user_model->getMembers(array_keys($members));
                foreach ($members_info as &$member_info){
                    $member_info['member_name'] = isset($members[$friend_info['id']])?$members[$member_info['id']]:$member_info['nickname'];
                }
                $v['members'] = $members_info;
            }
        }
        
        return $define_quns;
    }
    //查询自定义分组及好友
    public function _getGroupFriendsList($uid){
        $define_groups=$this->select('id AS group_id,group_name,is_group_hair')
                            ->from('en_chat_friend_groups')
                            ->where('uid= :id')->bindValues(array('id'=>$uid))
                            ->orderByASC(['porder'])
                            ->query();
        //4.查询自定义下的所有好友
        $user_model = new LoginModel;
        foreach ($define_groups as &$v){
            $friends = $this->select('friend_id,friend_name')->from('en_chat_friends')->where('group_id='.$v['group_id'])->query();
            if(empty($friends)){
               $v['friends'] = [];
            }else{
                $friends = array_column($friends, 'friend_name','friend_id');
                $friends_info = $user_model->getMembers(array_keys($friends));
                foreach ($friends_info as &$friend_info){
                    $friend_info['friend_name'] = isset($friends[$friend_info['id']])?$friends[$friend_info['id']]:$friend_info['nickname'];
                }
                $v['friends'] = $friends_info;
            }
        }
        
        return $define_groups;
    }
}
