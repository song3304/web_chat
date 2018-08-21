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
    //登陆校验
    const MESSAGE_AUTH_CHECK = 10002;
    //加入组
    const MESSAGE_JOIN_GROUP = 10003;
    //获取登录信息
    const MESSAGE_LOGIN = 10004;
    //好友分组
    const MESSAGE_COMPANY_FRIENDS = 10005;
    //未读信息
    const MESSAGE_UNREAD_MESSAGES = 10006;
    //未读变已读
    const MESSAGE_UNREAD_TO_READ = 10007;
    //当前聊天记录
    const MESSAGE_INDEX_MESSAGE = 10008;
    //历史聊天记录
    const MESSAGE_HISTORY_MESSAGE = 10009;
    //发送单人消息
    const MESSAGE_SEND_MESSAGE = 10010;
    //接收消息
    const MESSAGE_PICK_MESSAGE = 10011;
    //群发消息
    const MESSAGE_SEND_GROUP_MESSAGE = 10012;
    //创建自定义分组
    const MESSAGE_CREATE_GROUP = 10013;
    //删除自定义分组
    const MESSAGE_DELETE_GROUP = 10014;
    //修改自定义分组
    const MESSAGE_MODIFY_GROUP = 10015;
    //添加好友至自定义分组
    const MESSAGE_ADD_GROUP_FRIEND = 10016;
    //删除自定义分组中的好友
    const MESSAGE_DELETE_GROUP_FRIEND = 10017;
    //获取登出信息
    const MESSAGE_LOGOUT = 10018;
    //好友分组转移
    const MESSAGE_TRANSFER_GROUP = 10019;
    //加好友验证
    const MESSAGE_FRIEND_VERIFY = 10020;
    //加好友处理
    const MESSAGE_FRIEND_VERIFY_HANDLE = 10021;
    //获取大厅所有人员
    const MESSAGE_HALL_MEMBER = 10022;
    //发送群消息
    const MESSAGE_SEND_QUN_MESSAGE = 10023;
    /*
     * 这里定义business逻辑生成的消息回复时候用的消息编码
     */
    ////返回给客户端的错误业务代码
    const EVENT_ERROR = 50000;
    //返回给客户端的业务代码
    const EVENT_JOIN_GROUP = 50001;
    //登陆校验
    const EVENT_AUTH_CHECK = 50002;
    //返回登录信息
    const EVENT_LOGIN = 50004;
    //返回给客户端的好友分组
    const EVENT_COMPANY_FRIENDS = 50005;
    //返回给客户端的未读信息
    const EVENT_UNREAD_MESSAGES = 50006;
    //未读变已读结果
    const EVENT_UNREAD_TO_READ= 50007;
    //返回给客户端的当前聊天记录
    const EVENT_INDEX_MESSAGE = 50008;
    //返回给客户端的当前聊天记录
    const EVENT_HISTORY_MESSAGE = 50009;
    //客户端发送信息
    const EVENT_SEND_MESSAGE = 50010;
    //客户端接收信息
    const EVENT_PICK_MESSAGE = 50011;
    //客户端接收群发信息
    const EVENT_SEND_GROUP_MESSAGE = 50012;
    //返回给客户端的创建分组结果
    const EVENT_CREATE_GROUP = 50013;
    //返回给客户端的创建分组结果
    const EVENT_DELETE_GROUP = 50014;
    //返回给客户端的修改分组结果
    const EVENT_MODIFY_GROUP = 50015;
    //添加好友至自定义分组
    const EVENT_ADD_GROUP_FRIEND = 50016;
    //删除自定义分组中的好友
    const EVENT_DELETE_GROUP_FRIEND = 50017;
    //返回登出信息
    const EVENT_LOGOUT = 50018;
    //返回好友转移分组
    const EVENT_TRANSFER_GROUP = 50019;
    //返回加好友验证
    const EVENT_FRIEND_VERIFY = 50020;
    //返回加好友处理
    const EVENT_FRIEND_VERIFY_HANDLE = 50021;
    //返回大厅所有人员
    const EVENT_HALL_MEMBER = 50022;
    //返回群消息
    const EVENT_SEND_QUN_MESSAGE = 50023;
    //------------------------------------
}
