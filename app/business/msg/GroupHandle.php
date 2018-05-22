<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\business\msg;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
use App\business\model\Group;
use App\business\model\SendMessage;
use \GatewayWorker\Lib\Gateway;
use App\business\msg\MsgHandleBase;
use App\business\MsgIds;

/**
 * Description of JoinGroup
 *
 * @author Xp
 */
class GroupHandle extends MsgHandleBase {

    /**
     * @param $client_id
     * @param $json
     * @method 创建自定义分组
     */
    static public function handleCreate($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->group_name) && !empty($json->group_name)) {
            $group_model = new Group();
            $msg['uid'] = $json->uid;
            $msg['group_name'] = $json->group_name;
            $msg['group_type'] = $json->group_type;
            $msg['userIds'] = $json->userIds;
            $msg['sock_id'] = $json->sock_id;
            if($group_model->createGroup($json->uid,$json->group_name,$json->group_type,$json->userIds)){
                $msg['messages']='create group success';
                //个人逻辑：新建分组成功后，应返回给客户端EVENT_COMPANY_FRIENDS
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_CREATE_GROUP, 1, $msg)));
            }else{
                $msg['messages']='create group false';
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_CREATE_GROUP, 0, $msg)));
            }

        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_CREATE_GROUP, 0, 'create group err!')));
        }
    }

    /**
     * @param $client_id
     * @param $json
     * @method 删除自定义分组
     */
    static public function handleDeleteGroup($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->group_id) && !empty($json->group_id)) {
            $group_model = new Group();
            $msg['uid'] = $json->uid;
            $msg['group_id'] = $json->group_id;
            $msg['sock_id'] = $json->sock_id;
            if($group_model->deleteGroup($json->uid,$json->group_id)){
                $msg['messages']='delete group success';
                //个人逻辑：新建分组成功后，应返回给客户端EVENT_COMPANY_FRIENDS
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP, 1, $msg)));
            }else{
                $msg['messages']='delete group false';
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP, 0, $msg)));
            }

        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP, 0, 'delete group err!')));
        }

    }

    /**
     * @param $client_id
     * @param $json
     * @method 修改自定义分组
     */
    static public function handleModify($client_id, $json) {
        if (isset($json->uid) && !empty($json->uid) && isset($json->group_id) && !empty($json->group_id)) {
            $group_model = new Group();
            $msg['uid'] = $json->uid;
            $msg['group_id'] = $json->group_id;
            $msg['sock_id'] = $json->sock_id;
            if($group_model->modifyGroup($json->uid,$json->group_id,$json->group_name)){
                $msg['messages']='modify group success';
                //个人逻辑：新建分组成功后，应返回给客户端EVENT_COMPANY_FRIENDS
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_MODIFY_GROUP, 1, $msg)));
            }else{
                $msg['messages']='modify group false';
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_MODIFY_GROUP, 0, $msg)));
            }

        } else {
            //错误了
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_MODIFY_GROUP, 0, 'modify group err!')));
        }

    }

    /**
     * @param $client_id
     * @param $json
     * @method 删除自定义分组中的好友
     */
    static public function handleDeleteFriend($client_id, $json)
    {
        if (isset($json->uid) && !empty($json->uid) && isset($json->group_id) && !empty($json->group_id) && !empty($json->userIds)) {
            $model=new Group();
            if($model->deleteGroupFriend($json->uid,$json->group_id,$json->userIds)){
                $msg['messages']='delete friend success!';
            }else{
                $msg['messages']='delete friend fail!';
            }
            $msg['uid'] = $json->uid;
            $msg['sock_id'] = $json->sock_id;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP_FRIEND, 1, $msg)));
        }else{
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP_FRIEND, 0, 'delete friend err!')));
        }

    }

}
