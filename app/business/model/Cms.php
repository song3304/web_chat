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

class Cms extends Model{

    //获取推送资讯列表
    public function getCmsList($cms_ids) {
        if(empty($cms_ids)) return false;
        if(!is_array($cms_ids)){
            $cms_ids = explode(',', $cms_ids);
        }
        $cms_list = $this->select('id,title,create_time')->from('en_cms')->where('id in('.join(',', $cms_ids).')')->query();
        return !empty($cms_list)?$cms_list:false;
    }
    //获取资讯详情信息
    public function getCmsDetail($cms_id){
        if(empty($cms_id)) return false;
        $cms_info = $this->select('*')->from('en_cms')->where('id='.intval($cms_id))->row();
        return !empty($cms_info)?$cms_info:false;
    }
}
