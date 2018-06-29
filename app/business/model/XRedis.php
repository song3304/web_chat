<?php

namespace App\business\model;

class XRedis
{
    private $redis = null;

    public function __construct()
    {
        try {
            $redis = new \Redis();
            $redis->connect('192.168.0.53', 6379);
            $redis->auth('foobared');
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
        if ($this->redis && method_exists($this->redis, $name)) {
            try {
                $session = $this->redis->get($session_id);
                if ($session) {
                    //去掉前缀进行反序列化
                    $session = unserialize(substr($session, strlen('en_|')));
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
            return isset($session['en_user']['uid'])?$session['en_user']['uid']:false;
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