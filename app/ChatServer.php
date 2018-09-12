<?php

/*
 * 此文件主要功能：
 * 1、监听服务器端口
 * 2、处理客户端连接
 * 3、监控客户端情况
 */

namespace App;

use App\dbrequest\AuthCheckRequest;
use App\business\model\LoginModel;
use App\dbrequest\GroupRequest;
use App\dbrequest\HistoryMessageRequest;
use App\dbrequest\IndexMessageRequest;
use App\dbrequest\LogoutRequest;
use App\dbrequest\MessageRequest;
use App\dbrequest\SendGroupMessageRequest;
use App\dbrequest\FriendVerifyRequest;
use PHPSocketIO\SocketIO;
use Workerman\MySQL\Connection;
use App\TcpClient;
use App\business\MsgIds;
use App\MessageHandler;
use App\dbrequest\LoginRequest;
use App\dbrequest\CompanyFriendsRequest;
use App\dbrequest\CmsRequest;
use App\XObject;
use App\message\GlobalOnline;
use Workerman\Worker;

class ChatServer {

    //客户端池 uid=>[socket_id=>client,socket_id2=>client2]
    public $uidConnectionMap = [];
    //客户端连接未登录
    public $connectionMap = [];
    //socketio
    private $socketIO = null;
    //tcpclient连接
    private $tcpClient = null;
    //config
    private $config = [];

    //$port:tcp端口号
    public function __construct() {
        $this->config = include(__DIR__ . '/conf/config.php');

        $this->initSocketIO();
    }

    //tcpclient所处组别信息
    private function groupInfo() {
        $data = array(
            'id' => MsgIds::MESSAGE_JOIN_GROUP,
            'business_type' => 'JoinGroup',
            'group' => 'chatServerGroup',
        );
        return $data;
    }

    //tcpclient接收到消息之后处理
    public function onGatewayMessage($connection, $message) {
        //由消息
        MessageHandler::dispatch($this, $message);
    }

    //与gateway连接客户端初始化
    private function initTcpClient() {
        $this->tcpClient = new TcpClient($this->config['gateway'], $this->groupInfo());
        $this->onMessage = array($this, 'onGatewayMessage');
        $this->tcpClient->onMessage = $this->onMessage;
    }

    // 初始化http推送
    private function initHttpPush() {
        // 监听一个http端口
        $inner_http_worker = new Worker('http://0.0.0.0:3131');
        // 当http客户端发来数据时触发
        $inner_http_worker->onMessage = function($http_connection, $data){
            $_POST = $_POST ? $_POST : $_GET;
            // 推送数据的url格式 type=publish&to=uid&content=xxxx
            switch(@$_POST['type']){
                case 'publish':
                    $to = @$_POST['to'];
                    $_POST['content'] = htmlspecialchars(@$_POST['content']);
                    // 有指定uid则向uid所在socket组发送数据
                    if($to){
                        $this->sendMessage($to, 'pick_hot_cms', $_POST['content']);
                        // 否则向所有uid推送数据
                    }else{
                        //推送给所有资讯
                        foreach ($this->uidConnectionMap as $online_uid => $sockets){
                            $this->sendMessage($online_uid, 'pick_hot_cms', $_POST['content']);//通知所有人消息推送
                        }
                    }
                    // http接口返回，如果用户离线socket返回fail
                    if($to && !isset($this->uidConnectionMap[$to])){
                        return $http_connection->send('offline');
                    }else{
                        return $http_connection->send('ok');
                    }
            }
            return $http_connection->send('fail');
        };
        // 执行监听
        $inner_http_worker->listen();
    }
    
    //初始化
    private function initSocketIO() {
        $tcp_ip = $this->config['socket']['tcp_ip'];
        $tcp_port = $this->config['socket']['tcp_port'];
        $this->socketIO = new SocketIO($tcp_ip, $tcp_port);
        $this->socketIO->on('workerStart', function($socket) {
            $this->initTcpClient();
            //初始化 http 推送 线下可以用
            if($this->config['env'] == 'offline') $this->initHttpPush();
        });
        $this->socketIO->on('connection', function($socket) {

            //客户端登录
            $socket->on('login', function ($session_id, $uid)use($socket) {
                //保留连接信息
                $socket->session_id = $session_id;
                $this->connectionMap[$socket->id] = $socket;
                //告诉客户端，正在登录
                $socket->emit('login_ing');
                //请求登录信息
                LoginRequest::request($this,['sock_id' => $socket->id, 'session_id' => $session_id, 'uid' => $uid],MsgIds::MESSAGE_LOGIN);
            });
            //客户端断开连接
            $socket->on('disconnect', function ()use($socket) {
                $this->disconnect($socket);
            });
            //获取好友列表
            $socket->on('company_friends',function ($uid)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                CompanyFriendsRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid],MsgIds::MESSAGE_COMPANY_FRIENDS);
            });
            //获取全局在线人数
            $socket->on('global_online', function ()use($socket) {
                GlobalOnline::handle($this, null, $socket);
            });
            //获取未读信息
            $socket->on('unread_messages', function ($uid)use($socket) {
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                IndexMessageRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid],MsgIds::MESSAGE_UNREAD_MESSAGES);
            });
            //未读变已读
            $socket->on('unread_to_read', function ($uid, $toUid, $messageIds, $type="")use($socket) {
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                IndexMessageRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'toUid'=>$toUid, 'messageIds'=>$messageIds, 'messageType'=>$type],MsgIds::MESSAGE_UNREAD_TO_READ);
            });
            //获取当前聊天记录--群消息纪录
            $socket->on('index_message',function ($uid,$toUid,$last_unread_msg_time,$type="")use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                IndexMessageRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'to_uid' => $toUid, 'last_time'=>!empty($last_unread_msg_time)?$last_unread_msg_time:date('Y-m-d H:i:s'),'type'=>$type],MsgIds::MESSAGE_INDEX_MESSAGE);
            });
            //获取历史聊天记录-群消息
            $socket->on('history_message',function ($uid,$to_uid,$pageSize,$indexPage,$type="")use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                HistoryMessageRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'to_uid' => $to_uid ,'pageSize'=>!empty($pageSize)?$pageSize:50,'indexPage'=>!empty($indexPage)?$indexPage:1,'type'=>$type],MsgIds::MESSAGE_HISTORY_MESSAGE);
            });
            //发送单人消息
            $socket->on('send_message',function ($uid,$to_uid,$message,$is_temp=false)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                MessageRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'to_uid' => $to_uid ,'message'=>$message,'is_temp'=>$is_temp],MsgIds::MESSAGE_SEND_MESSAGE);
            });
            //群发消息
            $socket->on('send_group_message',function ($uid,$to_user_ids,$message)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                SendGroupMessageRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'to_user_ids' => $to_user_ids ,'message'=>$message],MsgIds::MESSAGE_SEND_GROUP_MESSAGE);
            });
            //新建自定义分组/群聊组
            $socket->on('create_group',function ($uid,$group_name,$group_type,$userIds)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                GroupRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'group_name' => $group_name, 'group_type' => $group_type, 'userIds' => $userIds],MsgIds::MESSAGE_CREATE_GROUP);
            }); 
            //删除自定义分组/群聊组
            $socket->on('delete_group',function ($uid,$group_id,$group_type)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                GroupRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'group_id' => $group_id, 'group_type'=>$group_type ],MsgIds::MESSAGE_DELETE_GROUP);
            });
            //修改自定义分组名/群聊组名
            $socket->on('modify_group',function ($uid,$group_id,$group_type,$new_name)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                GroupRequest::request($this, ['sock_id' => $socket->id, 'uid' => $uid, 'group_id' => $group_id , 'group_type'=>$group_type, 'group_name' => $new_name],MsgIds::MESSAGE_MODIFY_GROUP);
            });
            //修改好友的名字
            $socket->on("change_group_friend_name",function($uid,$friend_id,$friend_name,$group_id,$group_type)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                GroupRequest::request($this, ['sock_id' => $socket->id, 'uid' => $uid, 'friend_id' => $friend_id, 'friend_name' => $friend_name, 'group_id'=>$group_id, 'group_type'=>$group_type, ],MsgIds::MESSAGE_MODIFY_FRIEND_NAME);
            });
            //删除自定义分组中的好友/群聊成员
            $socket->on('delete_group_friend',function ($uid,$group_id,$group_type,$userIds)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                GroupRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'group_id' => $group_id, 'group_type' => $group_type , 'userIds' => $userIds],MsgIds::MESSAGE_DELETE_GROUP_FRIEND);
            });
            /****** 新加 ************/
            //转移好友到其他分组
            $socket->on('transfer_group',function ($uid,$friend_id,$group_id,$to_group_id)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                GroupRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'friend_id' => $friend_id, 'group_id' => $group_id, 'to_group_id'=>$to_group_id],MsgIds::MESSAGE_TRANSFER_GROUP);
            });
            //加好友验证消息->推送给对方----给对方发送验证信息，同时给自己一个等待验证回执
            $socket->on('add_friend_verification_message',function ($uid,$to_uid,$msg)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                FriendVerifyRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'to_uid' => $to_uid, 'msg' => $msg],MsgIds::MESSAGE_FRIEND_VERIFY);
            });
            //同意，拒绝对方验证
            $socket->on('handle_friend_verification',function ($uid,$msg_id,$is_agree)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                FriendVerifyRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'msg_id' => $msg_id, 'is_agree' => $is_agree],MsgIds::MESSAGE_FRIEND_VERIFY_HANDLE);
            });
            //获取当前在线所有人信息
            $socket->on('online_list',function ($uid)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                GlobalOnline::onlineList($this, ['sock_id' => $socket->id, 'uid' => $uid], $socket);
            });
            //群聊消息
            $socket->on('send_qun_message',function ($uid,$qid,$to_uid,$message)use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                }
                MessageRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'qid'=>$qid, 'to_uid' => $to_uid ,'message'=>$message],MsgIds::MESSAGE_SEND_QUN_MESSAGE);
            });
            //发送热点资讯消息
             $socket->on('send_hot_cms',function($uid,$cms_ids) use($socket){
                 if(!$this->authCheck($socket,$uid)){
                     $socket->emit('logout');return;
                 }
                 CmsRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'cms_ids'=>$cms_ids],MsgIds::MESSAGE_SEND_HOT_CMS);
             });
             // 资讯详情信息
             $socket->on('get_hot_cms_detail',function($uid,$cms_id) use($socket){
                if(!$this->authCheck($socket,$uid)){
                    $socket->emit('logout');return;
                 }
                 CmsRequest::request($this,['sock_id' => $socket->id, 'uid' => $uid, 'cms_id'=>$cms_id],MsgIds::MESSAGE_GET_HOT_CMS_DETAIL);
             });
        });
    }

    //判断用户是否登录
    public function isLogin($socket) {
        if (!isset($socket->uid)) {
            //先给客户端发送已经登出的消息
            $socket->emit('logout');
            return false;
        } else {
            return true;
        }
    }

    //检测登录信息
    public function authCheck($socket, $uid) {
        if($this->config['env'] == 'offline') return true;//线下直接返回true
        if (!$this->isLogin($socket)){
            return false;
        } else if ($socket->uid != $uid) {
            //登录过了，检测非法
            $this->disconnect($socket);
            return false;
        } else {
            //检测是否失效
            AuthCheckRequest::request($this, new XObject(['sock_id' => $socket->id, 'session_id' => $socket->session_id, 'uid' => $socket->uid]));
            return true;
        }
    }

    //判断用户是否在线
    public function isOnline($uid) {
        return isset($this->uidConnectionMap[$uid]);
    }

    //退出某用户
    public function disconnectByUid($uid, $sock_id) {
        if (isset($this->uidConnectionMap[$uid][$sock_id])) {
            $this->disconnect($this->uidConnectionMap[$uid][$sock_id]);
        }
    }


    //客户端断开事件处理
    protected function disconnect($socket) {
        if (!$this->isLogin($socket)) {
            return;
        }
        if (isset($this->uidConnectionMap[$socket->uid][$socket->id])) {
            //释放客户端连接
            unset($this->uidConnectionMap[$socket->uid][$socket->id]);
            if (count($this->uidConnectionMap[$socket->uid]) === 0) {
                unset($this->uidConnectionMap[$socket->uid]);
                
                //给好友推送下线消息--当这个用户所有终端下线，发送下线通知
                LogoutRequest::request($this, ['sock_id' => $socket->id, 'uid' => $socket->uid]);
            }
        }
    }


    //客户端登录成功操作
    public function login_sucess($sock_id, $uid, $error = false) {
        //登录失败
        if ($error && isset($this->connectionMap[$sock_id])) {
            $this->connectionMap[$sock_id]->emit('logout');
            $this->connectionMap[$sock_id]->disconnect();
            unset($this->connectionMap[$sock_id]);
        }
        if (isset($this->connectionMap[$sock_id])) {
            //登录成功，移交
            $this->uidConnectionMap[$uid][$sock_id] = $this->connectionMap[$sock_id];
            unset($this->connectionMap[$sock_id]);
            //设置登录标志
            $this->uidConnectionMap[$uid][$sock_id]->uid = $uid;
            $this->uidConnectionMap[$uid][$sock_id]->emit('login', 'login success!');
        }
    }

    //发送给逻辑处理单元
    public function sendMessageToGateway($msg) {
        $this->tcpClient->sendToGateway($msg);
    }

    //推送消息
    private function _emitMsg($socket, $event, $msg = null) {
        if (is_null($msg)) {
            $socket->emit("$event");
        } else {
            $socket->emit("$event", $msg);
        }
    }

    //发送给指定客户端
    public function sendMessage($to_uid, $event, $msg = null, $to_sock_id = null, $except_sock_id = []) {
        //查找要发送给的客户端
        if (!isset($this->uidConnectionMap[$to_uid])) {
            //没有此客户端
            return;
        }

        //格式化消息
        if (is_string($msg)) {
            
        } else if (is_array($msg)||is_object($msg)) {
            $msg = json_encode($msg);
        } else {
            //错误，不处理
            return;
        }

        //事件判断及消息判断
        if (empty($event) || !is_string($event) || !is_string($msg)) {
            //事件名称或者消息错误
            return;
        }

        if (!empty($to_sock_id)) {
            if (isset($this->uidConnectionMap[$to_uid][$to_sock_id])) {
                $this->_emitMsg($this->uidConnectionMap[$to_uid][$to_sock_id], $event, $msg);
            } else {
                //找不到连接了
            }
        } else {

            //发送给客户端
            foreach ($this->uidConnectionMap[$to_uid] as $socket) {
                $this->_emitMsg($socket, $event, $msg);
            }
        }
    }

}
