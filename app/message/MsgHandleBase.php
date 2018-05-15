<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\message;
use App\ChatServer;

/**
 * Description of MsgHandleBase
 *
 * @author Xp
 */
abstract class MsgHandleBase
{
    static function handle(ChatServer $chat_server, $json = null, $socket = null){}
    
}
