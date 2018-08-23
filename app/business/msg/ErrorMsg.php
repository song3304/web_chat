<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\business\msg;

use \GatewayWorker\Lib\Gateway;
use App\business\MsgIds;
/**
 * Description of ErrorMsg
 *
 * @author Xp
 */
class ErrorMsg
{
    static public function handle($client_id, $code, $msg = 'undefined msgid or other err！') {
        
        //todo: 根据业务需要检测相关数据
        //todo: 根据业务需要修改json数据
        Gateway::sendToClient($client_id, self::output(array('event'=>MsgIds::EVENT_ERROR, 'code' => $code, 'msg' => $msg)));
    }
    //未定output
    static public function output(array $json) {
        $json = json_encode($json);
        return "$json\r\n";
    }
}
