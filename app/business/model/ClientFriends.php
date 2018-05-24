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
use App\ChatServer;

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
        //3.查询所有自定义分组
        $define_groups=$this
            ->select('id AS group_id,group_name,is_group_hair')
            ->from('en_chat_friend_groups')
            ->where('uid= :id')
            ->bindValues(array('id'=>$uid))
            ->query();
        //4.查询自定义下的所有好友
        foreach ($define_groups as $k=>$v){
            $define_group_friends[$k]['group_name']=$v['group_name'];
            $define_group_friends[$k]['group_id']=$v['group_id'];
            $define_group_friends[$k]['is_group_hair']=$v['is_group_hair'];
            $define_group_friends[$k]['friends']=$this
                ->select('c.friend_id,u.nickname,u.realname ,u.pic_url AS img')
                ->from('en_chat_friends AS c')
                ->leftjoin('en_users AS u','c.friend_id=u.id')
                ->where('c.group_id= :id')
                ->bindValues(array('id'=>$v['group_id']))
                ->query();
        }
        return ['default_groups'=>$default_group_friends,'define_groups'=>$define_group_friends];
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
        //3.查询所有自定义分组
        $define_groups=$this
            ->select('id AS group_id,group_name,is_group_hair')
            ->from('en_chat_friend_groups')
            ->where('uid= :id')
            ->bindValues(array('id'=>$uid))
            ->query();
        //4.查询自定义下的所有好友
        foreach ($define_groups as $k=>$v){
            $define_group_friends[$k]['group_name']=$v['group_name'];
            $define_group_friends[$k]['group_id']=$v['group_id'];
            $define_group_friends[$k]['is_group_hair']=$v['is_group_hair'];
            $define_group_friends[$k]['friends']=$this
                ->select('c.friend_id,u.nickname,u.realname,u.pic_url AS img')
                ->from('en_chat_friends AS c')
                ->leftjoin('en_users AS u','c.friend_id=u.id')
                ->where('c.group_id= :id')
                ->bindValues(array('id'=>$v['group_id']))
                ->query();
        }
        return ['default_groups'=>$default_group_friends,'define_groups'=>$define_group_friends];
    }

}
