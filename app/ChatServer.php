<?php

/*
 * 此文件主要功能：
 * 1、监听服务器端口
 * 2、处理客户端连接
 * 3、监控客户端情况
 */

namespace App;

use App\dbrequest\HistoryMessageRequest;
use App\dbrequest\IndexMessageRequest;
use App\dbrequest\SendGroupMessageRequest;
use App\dbrequest\SendMessageRequest;
use PHPSocketIO\SocketIO;
use Workerman\MySQL\Connection;
use App\TcpClient;
use App\business\MsgIds;
use App\MessageHandler;
use App\dbrequest\LoginRequest;
use App\dbrequest\CompanyFriendsRequest;
use App\XObject;
use App\message\GlobalOnline;

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

    //初始化
    private function initSocketIO() {
        $tcp_ip = $this->config['socket']['tcp_ip'];
        $tcp_port = $this->config['socket']['tcp_port'];
        $this->socketIO = new SocketIO($tcp_ip, $tcp_port);
        $this->socketIO->on('workerStart', function($socket) {
            $this->initTcpClient();
        });
        $this->socketIO->on('connection', function($socket) {

            //客户端登录
            $socket->on('login', function ($session_id, $uid)use($socket) {
                //保留连接信息
                $this->connectionMap[$socket->id] = $socket;
                //告诉客户端，正在登录
                $socket->emit('login_ing');
                //请求登录信息
                LoginRequest::request($this, new XObject(['sock_id' => $socket->id, 'session_id' => $session_id, 'uid' => $uid]));
            });
            //客户端断开连接
            $socket->on('disconnect', function ()use($socket) {
                $this->disconnect($socket);
            });
            //获取好友列表
            $socket->on('company_friends',function ($uid)use($socket){
                CompanyFriendsRequest::request($this, new XObject(['sock_id' => $socket->id, 'uid' => $uid]));
            });
            //获取当前聊天记录
            $socket->on('index_message',function ($uid,$to_uid)use($socket){
                IndexMessageRequest::request($this, new XObject(['sock_id' => $socket->id, 'uid' => $uid, 'to_uid' => $to_uid]));
            });
            //获取历史聊天记录
            $socket->on('history_message',function ($uid,$to_uid,$page)use($socket){
                HistoryMessageRequest::request($this, new XObject(['sock_id' => $socket->id, 'uid' => $uid, 'to_uid' => $to_uid ,'page'=>$page]));
            });
            //发送单人消息
            $socket->on('send_message',function ($uid,$to_uid,$message)use($socket){
                SendMessageRequest::request($this, new XObject(['sock_id' => $socket->id, 'uid' => $uid, 'to_uid' => $to_uid ,'message'=>$message]));
            });
            //群发消息
            $socket->on('send_group_message',function ($uid,$group_id,$message)use($socket){
                SendGroupMessageRequest::request($this, new XObject(['sock_id' => $socket->id, 'uid' => $uid, 'group_id' => $group_id ,'message'=>$message]));
            });
            //获取全局在线人数
            $socket->on('global_online', function ()use($socket) {
                GlobalOnline::handle($this, null, $socket);
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

    //判断用户是否在线
    public function isOnline($uid) {
        return isset($this->uidConnectionMap[$uid]);
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
            
        } else if (is_array($msg)) {
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
