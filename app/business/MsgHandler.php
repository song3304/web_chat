<?php
namespace App\business;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\business\msg\AuthCheckHandle;
use App\business\msg\CompanyFriendsHandle;
use App\business\msg\GroupHandle;
use App\business\msg\LogoutHandle;
use App\business\msg\MessageHandle;
use App\business\MsgIds;
use App\business\msg\JoinGroup;
use App\business\msg\ErrorMsg;
use App\business\msg\LoginHandle;
use App\business\msg\FriendVerifyHandle;
use App\business\msg\GlobalOnlineHandle;
use App\business\msg\CmsHandle;
use App\business\msg\RecentChatListHandle;
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
            case MsgIds::MESSAGE_AUTH_CHECK :
                AuthCheckHandle::handle($client_id, $json);
                break;
            case MsgIds::MESSAGE_LOGOUT :
                LogoutHandle::handle($client_id, $json);
                break;
            case MsgIds::MESSAGE_COMPANY_FRIENDS:
                CompanyFriendsHandle::handle($client_id,$json);
                break;
            case MsgIds::MESSAGE_RECENT_LIST:
                RecentChatListHandle::handle($client_id,$json);
                break;
            case MsgIds::MESSAGE_UNREAD_MESSAGES:
                MessageHandle::unreadMessages($client_id,$json);
                break;
            case MsgIds::MESSAGE_UNREAD_TO_READ:
                MessageHandle::unreadToRead($client_id,$json);
                break;
            case MsgIds::MESSAGE_INDEX_MESSAGE:
                MessageHandle::indexMessage($client_id,$json);
                break;
            case MsgIds::MESSAGE_HISTORY_MESSAGE:
                MessageHandle::historyMessage($client_id,$json);
                break;
            case MsgIds::MESSAGE_SEND_MESSAGE:
                MessageHandle::sendMessage($client_id,$json);
                break;
            case MsgIds::MESSAGE_SEND_GROUP_MESSAGE:
                MessageHandle::sendGroupMessage($client_id,$json);
                break;
            case MsgIds::MESSAGE_CREATE_GROUP:
                GroupHandle::handleCreate($client_id,$json);
                break;
            case MsgIds::MESSAGE_DELETE_GROUP:
                GroupHandle::handleDeleteGroup($client_id,$json);
                break;
            case MsgIds::MESSAGE_MODIFY_GROUP:
                GroupHandle::handleModify($client_id,$json);
                break;
            case MsgIds::MESSAGE_MODIFY_FRIEND_NAME:
                GroupHandle::handleModifyFriendName($client_id,$json);
                break;
            case MsgIds::MESSAGE_DELETE_GROUP_FRIEND:
                GroupHandle::handleDeleteFriend($client_id,$json);
                break;
            case MsgIds::MESSAGE_SAVE_GROUP_FRIEND:
                GroupHandle::handleSaveFriend($client_id,$json);
                break;
            /***新加***/
            case MsgIds::MESSAGE_TRANSFER_GROUP:
                GroupHandle::handleTransferGroup($client_id,$json);
                break;
            case MsgIds::MESSAGE_FRIEND_VERIFY:
                FriendVerifyHandle::addFriendVerify($client_id,$json);
                break;
            case MsgIds::MESSAGE_FRIEND_VERIFY_HANDLE:
                FriendVerifyHandle::handleFriendVerify($client_id,$json);
                break;
            case MsgIds::MESSAGE_HALL_MEMBER:
                GlobalOnlineHandle::getOnlineMembers($client_id, $json);
                break;
            case MsgIds::MESSAGE_SEND_QUN_MESSAGE:
                MessageHandle::sendQunMessage($client_id,$json);
                break;
            //发送热点资讯
            case MsgIds::MESSAGE_SEND_HOT_CMS:
                CmsHandle::pushCmsList($client_id,$json);
                break;
            case MsgIds::MESSAGE_GET_CMS_DETAIL:
                CmsHandle::getCmsDetail($client_id,$json);
                break;
            default :
                //未定义的消息，不做处理
                ErrorMsg::handle($client_id, MsgIds::MSG_FORMAT_ERROR);
                break;
        }
    }
}
