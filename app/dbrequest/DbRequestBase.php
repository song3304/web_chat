<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\dbrequest;
use App\ChatServer;
use App\XObject;
/**
 * Description of DbRequestBase
 *
 * @author Xp
 */
abstract class DbRequestBase {
    //定义数据请求返回的数据
    static public function request(ChatServer $chat_server, $data, $message_type) {}
    static public function response(ChatServer $chat_server, \stdClass $json, $event_type) {}
}
