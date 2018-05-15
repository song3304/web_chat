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

class ClientFriends extends Model{
    
    //获取用户好友信息,根据公司进行分组
    public function companyFriends($uid) {
        $system_type = $this->systemType($uid);
    }
}
