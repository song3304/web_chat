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

class LoginModel extends Model{
    
    public function uid($session_id)
    {
        $result = $this->select('uid')->from('session')->where(['session_id'=>$session_id])->row();
        if (empty($result)) {
            return false;
        } else {
            return $result['uid'];
        }
    }

    public function getUser($uid)
    {
        return $this->select('id,nickname,realname,system_type')->from('en_users')->where('id='.$uid)->row();
    }

    public function getFriends($uid)
    {
        $user=$this->getUser($uid);
        switch($user['system_type']){
            case 2://撮合员
                return $this->select('user_id AS friend_id')->from('en_collection')->where('match_id='.$uid)->query();
                break;
            case 3://交易商
                return $this->select('match_id AS friend_id')->from('en_collection')->where('user_id='.$uid)->query();
                break;
            default:
                break;
        }

    }
}
