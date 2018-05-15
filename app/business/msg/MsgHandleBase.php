<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\business\msg;

use App\business\msg\ErrorMsg;

/**
 * Description of MsgHandleBase
 *
 * @author Xp
 */
abstract class MsgHandleBase {

    static function handle($client_id, $json) {
        
    }

    //消息格式组装
    static public function output(array $json) {
        $json = json_encode($json);
        return "$json\r\n";
    }

    //错误
    static public function error($client_id, $code, $msg) {
        ErrorMsg::handle($client_id, $code, $msg);
    }

    /*
     * 返回信息组装
     * @param int $event 业务编码
     * @param int $code 0：失败 1：成功
     * @param string | array $msg 需要组装的信息
     * @return array
     */
    
    static protected function business($event, $code, $msg) {
        $data = [
            'event' => $event,
            'code' => $code,
        ];
        if (is_string($msg)) {
            $data['msg'] = $msg;
        } else if (is_array($msg)) {
            $data = array_merge($data, $msg);
        }
        return $data;
    }

}
