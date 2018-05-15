<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\business\model;

/**
 * Description of Model
 *
 * @author Xp
 */
class Model {
    //数据库连接
    static protected $db = null;

    public function __construct() {
        //初始化数据库连接类
        if (is_null(self::$db)) {
            $config = include(__DIR__ . '/../../conf/config.php');
            $database = $config['database'];
            self::$db = new \Workerman\MySQL\Connection($database['hostname'], $database['hostport'], $database['username'], $database['password'], $database['database'], $database['charset']);
        }
    }
    
    //获取用户属于哪个系统
    public function systemType($uid) {
        //1.admin, 2.match 3.trade 4.index
        $result = $this->select('system_type')->from('en_users')->where('id= :id')->bindValues(array('id'=>$uid))->row();
        return intval($result['system_type']);
    }
    
    public function __call($method, $args)
    {
        if (method_exists(self::$db, $method)) {
            return call_user_func_array([self::$db, $method], $args);
        } else {
            throw new \Exception('call to undefined method');
        }
    }
}
