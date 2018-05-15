<?php
namespace App\business;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\business\MsgIds;
use App\business\msg\JoinGroup;
use App\business\msg\ErrorMsg;
use App\business\msg\LoginHandle;
/**
 * Description of MsgHandler
 *
 * @author Xp
 */
class MsgHandler
{
    
    public static function dispatch($client_id, $message) {
        //判断消息类型，并分发给相应的消息处理类
        $json = json_decode($message);

        if (!$json || !isset($json->id)) {
            //消息错误
            return ErrorMsg::handle($client_id, MsgIds::MSG_FORMAT_ERROR);
        }
        
        switch ($json->id) {
            case MsgIds::MESSAGE_JOIN_GROUP :
                JoinGroup::handle($client_id, $json);
                break;
            case MsgIds::MESSAGE_LOGIN :
                LoginHandle::handle($client_id, $json);
                break;
            default :
                //未定义的消息，不做处理
                ErrorMsg::handle($client_id, MsgIds::MSG_FORMAT_ERROR);
                break;
        }
    }
}
