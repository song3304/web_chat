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
    
    /*
     * 这里定义business逻辑生成的消息回复时候用的消息编码
     */
    ////返回给客户端的错误业务代码
    const EVENT_ERROR = 50000;
    //返回给客户端的业务代码
    const EVENT_JOIN_GROUP = 50001;
    //返回登录信息
    const EVENT_LOGIN = 50004;
    
    //------------------------------------
}
