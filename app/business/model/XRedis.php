<?php

namespace App\business\model;

class XRedis
{
    private $redis = null;
    private $config = [
        'hostname' => '127.0.0.1',
        'hostport' => 6379,
        'password' => '',
        'session_prefix' => '',
    ];

    public function __construct()
    {
        $config = include(__DIR__ . '/../../conf/config.php');
        $this->config = array_merge($this->config, $config['redis']);
        try {
            $redis = new \Redis();
            $redis->connect($this->config['hostname'], $this->config['hostport']);
            $redis->auth($this->config['password']);
            $this->redis = $redis;
        } catch (Exception $e) {

        }

    }

    /**
     * get_session
     *
     * @param mixed $session_id
     * @return void
     */
    public function get_session($session_id)
    {
        if ($this->redis) {
            try {
                $session = $this->redis->get($session_id);
                if ($session) {
                    //去掉前缀进行反序列化
                    $session_prefix = $this->config['session_prefix'];
                    if (!empty($session_prefix)) {
                        $session_prefix .= '|';
                    }
                    if (!strpos($session, ($session_prefix))) {
                        return false;
                    } else {
                        $str = strstr($session, $session_prefix);
                        return unserialize(substr($str, strlen($session_prefix)));
                    }
                    $session = unserialize(strstr($session, ($session_prefix.'|')));
                }
                return $session;
            } catch(\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * get_uid
     *
     * @param mixed $session_id
     * @return void
     */
    public function get_uid($session_id)
    {
        $session = $this->get_session($session_id);
        if ($session) {
            return isset($session[$this->config['session_prefix'].'user']['uid'])
                ?$session[$this->config['session_prefix'].'user']['uid']
                :false;
        } else {
            return false;
        }
    }

    public function __call($name, $arguments)
    {
        if ($this->redis && method_exists($this->redis, $name)) {
            try {
                return call_user_func_array([$this->redis, $name], $arguments);
            } catch(\Exception $e) {
                return false;
            }
        } else {
            return false;
        }

    }
}