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
    
    public function uid($session_id) {
        $result = $this->select('uid')->from('session')->where(['session_id'=>$json->session_id])->row();
        if (empty($result)) {
            return false;
        } else {
            return $result['uid'];
        }
    }
}
