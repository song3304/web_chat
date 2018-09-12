<?php
namespace App;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\dbrequest\AuthCheckRequest;
use App\dbrequest\CompanyFriendsRequest;
use App\business\MsgIds;
use App\dbrequest\GroupRequest;
use App\dbrequest\HistoryMessageRequest;
use App\dbrequest\IndexMessageRequest;
use App\dbrequest\LogoutRequest;
use App\dbrequest\MessageRequest;
use App\dbrequest\SendGroupMessageRequest;
use App\dbrequest\CmsRequest;
use App\message\JoinGroup;
use App\ChatServer;
use App\dbrequest\LoginRequest;
use App\dbrequest\FriendVerifyRequest;
use App\message\GlobalOnline;

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
            case MsgIds::EVENT_AUTH_CHECK :
                AuthCheckRequest::response($chat_server, $json);
                break;
            case MsgIds::EVENT_LOGOUT :
                LogoutRequest::response($chat_server, $json, 'offline_notice');
                break;
            case MsgIds::EVENT_COMPANY_FRIENDS :
                CompanyFriendsRequest::response($chat_server, $json);
                break;
            case MsgIds::EVENT_UNREAD_MESSAGES :
                IndexMessageRequest::response($chat_server, $json, 'unread_messages');
                break;
            case MsgIds::EVENT_UNREAD_TO_READ :
                IndexMessageRequest::response($chat_server, $json, 'unread_to_read');
                break;
            case MsgIds::EVENT_INDEX_MESSAGE :
                IndexMessageRequest::response($chat_server, $json, 'index_message');
                break;
            case MsgIds::EVENT_HISTORY_MESSAGE :
                HistoryMessageRequest::response($chat_server, $json);
                break;
            case MsgIds::EVENT_SEND_MESSAGE :
                MessageRequest::responseSendMessage($chat_server, $json);
                break;
            case MsgIds::EVENT_SEND_GROUP_MESSAGE :
                SendGroupMessageRequest::response($chat_server, $json);
                break;
            case MsgIds::EVENT_CREATE_GROUP :
                GroupRequest::response($chat_server, $json, 'create_group');
                break;
            case MsgIds::EVENT_DELETE_GROUP :
                GroupRequest::response($chat_server, $json, 'delete_group');
                break;
            case MsgIds::EVENT_MODIFY_GROUP :
                GroupRequest::response($chat_server, $json, 'modify_group');
                break;
            case MsgIds::EVENT_DELETE_GROUP_FRIEND :
                GroupRequest::responseDeleteFriend($chat_server, $json);
                break;
            /*************新加***********************/
            case MsgIds::EVENT_TRANSFER_GROUP :
                GroupRequest::response($chat_server, $json, 'transfer_group');
                break;
            case MsgIds::EVENT_FRIEND_VERIFY :
                FriendVerifyRequest::verifyResponse($chat_server, $json);
                break;
            case MsgIds::EVENT_FRIEND_VERIFY_HANDLE :
                FriendVerifyRequest::verifyHandle($chat_server, $json);
                break;
            case MsgIds::EVENT_FRIEND_VERIFY_HANDLE :
                GlobalOnline::onlineMemberResponse($chat_server, $json);
                break;
            case MsgIds::EVENT_SEND_QUN_MESSAGE :
                MessageRequest::responseSendQunMessage($chat_server, $json);
                break;
            case MsgIds::EVENT_HALL_MEMBER:
                GlobalOnline::onlineMemberResponse($chat_server, $json);
                break;
            case MsgIds::EVENT_MODIFY_FRIEND_NAME :
                GroupRequest::response($chat_server, $json, 'change_group_friend_name');
                break;
            case MsgIds::EVENT_SEND_HOT_CMS:
                CmsRequest::pick_response($chat_server, $json);
                break;
            case MsgIds::EVENT_GET_HOT_CMS_DETAIL:
                CmsRequest::get_detail_response($chat_server, $json);
                break;
            default :
                //未定义的消息，不做处理
                break;
        }
    }
}
