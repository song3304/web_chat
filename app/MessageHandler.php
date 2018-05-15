<?php
namespace App;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\business\MsgIds;
use App\message\JoinGroup;
use App\ChatServer;
use App\dbrequest\LoginRequest;

/**
 * Description of MsgHandler
 *
 * @author Xp
 */
class MessageHandler
{
    
    public static function dispatch(ChatServer $chat_server, $message) {
        //判断消息类型，并分发给相应的消息处理类
        $json = json_decode($message);
        //必须字段的校验
        if (!$json || !isset($json->event) || !isset($json->code)) {
            //消息错误
            return;
        }
        
        switch ($json->event) {
            case MsgIds::EVENT_JOIN_GROUP :
                JoinGroup::handle($chat_server, $json);
                break;
            case MsgIds::EVENT_LOGIN :
                LoginRequest::response($chat_server, $json);
                break;
            default :
                //未定义的消息，不做处理
                break;
        }
    }
}
