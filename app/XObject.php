<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

/**
 * Description of XObject
 *
 * @author Xp
 */
class XObject extends \stdClass{

    /*
     * 使用数组初始化
     */
    public function __construct(Array $arr = []) {
        if (empty($arr)) {
            return;
        }
        foreach ($arr as $key => $value) {
            $this->$key = $value;
        }
    }
    
    public function __get($name) {
        if (isset($this->$name)) {
            return $this->$name;
        } else {
            throw new Exception('undefined property!');
        }
    }
    
    public function __set($name, $value) {
        $this->$name = $value;
    }
}
