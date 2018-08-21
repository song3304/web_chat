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
        $return_data['uid'] = $json->uid;
        if (isset($json->uid) && !empty($json->uid) && isset($json->group_name) && !empty($json->group_name)) {
            $group_model = new Group();
            $group_new = $group_model->createGroup($json->uid,$json->group_name,$json->group_type,$json->userIds);
            if($group_new != false){
                $data = [
                    'result'=>true,
                    'params'=>['group_name'=>$json->group_name,'group_type'=>$json->group_type,'userIds'=>$json->userIds],
                    'msg'=>'创建分组成功!',
                    'data'=>$group_new
                ];
                $return_data['data']=$data;
                //个人逻辑：新建分组成功后，应返回给客户端EVENT_COMPANY_FRIENDS
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_CREATE_GROUP, 1, $return_data)));
            }else{
                $data = [
                    'result'=>false,
                    'params'=>['group_name'=>$json->group_name,'group_type'=>$json->group_type,'userIds'=>$json->userIds],
                    'msg'=>'分组名重复，请重新输入!',
                    'data'=>null
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_CREATE_GROUP, 0, $return_data)));
            }

        } else {
            //错误了
            $data = [
               'result'=>false,
               'params'=>$json,
               'msg'=>'参数错误!',
               'data'=>null
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_CREATE_GROUP, 0, $return_data)));
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
            $return_data['uid'] = $json->uid;
            $return_data['group_id'] = $json->group_id;
            $return_data['sock_id'] = $json->sock_id;
            $data = [
                'result'=>true,
                'params'=>['userId'=>$json->uid,'groupId'=>$json->group_id,'groupType'=>$json->group_type],
                'msg'=>'删除分组成功!',
                'data'=>[]
            ];
            if($group_model->deleteGroup($json->uid,$json->group_id,$json->group_type)){
                $return_data['data'] = $data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP, 1, $return_data)));
            }else{
                $data['result'] = false;
                $data['msg']='删除分组失败!';
                $return_data['data'] = $data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP, 0, $return_data)));
            }

        } else {
            //错误了
            $return_data['uid'] = $json->uid;
            $data = [
                'result'=>false,
                'params'=>$json,
                'msg'=>'删除分组参数错误!',
                'data'=>[]
            ];
            $return_data['data'] = $data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP, 0, $return_data)));
        }

    }

    /**
     * @param $client_id
     * @param $json
     * @method 修改自定义分组/群聊组名
     */
    static public function handleModify($client_id, $json) {
        if (!empty($json->uid) && !empty($json->group_id)&& !empty($json->group_name)) {
            $group_model = new Group();
            $return_data['uid'] = $json->uid;
            $return_data['group_id'] = $json->group_id;
            $return_data['sock_id'] = $json->sock_id;
            $group_mdf = $group_model->modifyGroup($json->uid,$json->group_id,$json->group_name,$json->group_type);
            if($group_mdf != false){
                $data = [
                    'result'=>true,
                    'params'=>['groupId'=>$json->group_id,'groupType'=>$json->group_type,'groupName'=>$json->group_name],
                    'msg'=>'修改分群组名成功!',
                    'data'=>$group_mdf
                ];
                $return_data['data']=$data;
                //个人逻辑：新建分组成功后，应返回给客户端EVENT_COMPANY_FRIENDS
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_MODIFY_GROUP, 1, $return_data)));
            }else{
                $data = [
                    'result'=>false,
                    'params'=>['groupId'=>$json->group_id,'groupType'=>$json->group_type,'groupName'=>$json->group_name],
                    'msg'=>'群组名不能重复，请重新输入!',
                    'data'=>null
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_MODIFY_GROUP, 0, $return_data)));
            }

        } else {
            //错误了
            $return_data['uid'] = $json->uid;
            $data = [
                'result'=>false,
                'params'=>$json,
                'msg'=>'参数错误!',
                'data'=>null
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_MODIFY_GROUP, 0, $return_data)));
        }

    }

    /**
     * @param $client_id
     * @param $json
     * @method 删除自定义分组中的好友
     */
    static public function handleDeleteFriend($client_id, $json)
    {
        if (!empty($json->uid) && !empty($json->group_id) && !empty($json->userIds) && is_array($json->userIds)) {
            $model=new Group();
            $return_data['uid'] = $json->uid;
            $return_data['sock_id'] = $json->sock_id;
            $return_data['group_id'] = $json->group_id;
            $return_data['userIds'] = $json->userIds;
            if($model->deleteGroupFriend($json->uid,$json->group_id,$json->userIds,$json->group_type)){
                $data = [
                    'result'=>true,
                    'params'=>['userId'=>$json->uid,'groupId'=>$json->group_id,'groupType'=>$json->group_type,'userIds'=>$json->userIds],
                    'msg'=>'删除好友成功!',
                    'data'=>$json->userIds
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP_FRIEND, 1, $return_data)));
            }else{
                $data = [
                    'result'=>false,
                    'params'=>['userId'=>$json->uid,'groupId'=>$json->group_id,'groupType'=>$json->group_type,'userIds'=>$json->userIds],
                    'msg'=>'删除好友失败!',
                    'data'=>null
                ];
                $return_data['data']=$data;
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP_FRIEND, 0, $return_data)));
            }

        }else{
            $return_data['uid'] = $json->uid;
            $data = [
                'result'=>false,
                'params'=>$json,
                'msg'=>'参数错误!',
                'data'=>null
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_DELETE_GROUP_FRIEND, 0, $return_data)));
        }

    }
    /**
     * @param $client_id
     * @param $json
     * @method 转移好友到其他分组
     */
    static public function handleTransferGroup($client_id, $json){
        if (!empty($json->uid) && !empty($json->group_id) && !empty($json->friend_id)) {
            $model=new Group();
            $return_data['uid'] = $json->uid;
            $return_data['sock_id'] = $json->sock_id;
            $return_data['data'] = [
                'result'=>null,
                'params'=>['userId'=>$json->uid,'groupId'=>$json->group_id,'friendId'=>$json->friend_id,'toGroupId'=>$json->to_group_id],
                'msg'=>'',
                'data'=>null
            ];
            if($model->transferGroupFriend($json->uid,$json->friend_id,$json->group_id,$json->to_group_id)){
                $return_data['data']['result'] = true;
                $return_data['data']['msg'] = '移动好友成功!';
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_TRANSFER_GROUP, 1, $return_data)));
            }else{
                $return_data['data']['result'] = false;
                $return_data['data']['msg'] = '转移好友失败!';
                Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_TRANSFER_GROUP, 0, $return_data)));
            }
        
        }else{
            $return_data['uid'] = $json->uid;
            $data = [
                'result'=>false,
                'params'=>$json,
                'msg'=>'参数错误!',
                'data'=>null
            ];
            $return_data['data']=$data;
            Gateway::sendToClient($client_id, self::output(self::business(MsgIds::EVENT_TRANSFER_GROUP, 0, $return_data)));
        }
    }
}
