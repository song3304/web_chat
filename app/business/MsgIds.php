<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\business;
/**
 * Description of MsgIds
 *
 * @author Xp
 */
class MsgIds
{
    /*
     * 消息返回code
     */
    //消息格式错误
    const MSG_FORMAT_ERROR = -100;
    //加入组别数据格式错误 JoinGroup
    const MSG_GROUP_ERROR = -101;

    /*
     * 这里定义需要business逻辑处理的消息编码
     * MESSAGE与EVENT一一对应，一个request的编码对应一个response的编码
     */
    //加入组
    const MESSAGE_JOIN_GROUP = 10003;
    //获取登录信息
    const MESSAGE_LOGIN = 10004;
    //好友分组
    const MESSAGE_COMPANY_FRIENDS = 10005;
    //当前聊天记录
    const MESSAGE_INDEX_MESSAGE = 10006;
    //历史聊天记录
    const MESSAGE_HISTORY_MESSAGE = 10007;
    //发送单人消息
    const MESSAGE_SEND_MESSAGE = 10008;
    //群发消息
    const MESSAGE_SEND_GROUP_MESSAGE = 10009;
    
    /*
     * 这里定义business逻辑生成的消息回复时候用的消息编码
     */
    ////返回给客户端的错误业务代码
    const EVENT_ERROR = 50000;
    //返回给客户端的业务代码
    const EVENT_JOIN_GROUP = 50001;
    //返回登录信息
    const EVENT_LOGIN = 50004;
    //返回给客户端的好友分组
    const EVENT_COMPANY_FRIENDS = 50005;
    //返回给客户端的当前聊天记录
    const EVENT_INDEX_MESSAGE = 50006;
    //返回给客户端的当前聊天记录
    const EVENT_HISTORY_MESSAGE = 50007;
    //客户端接收信息
    const EVENT_SEND_MESSAGE = 50008;
    //客户端接收群发信息
    const EVENT_SEND_GROUP_MESSAGE = 50009;
    
    //------------------------------------
}
