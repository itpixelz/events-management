<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class DepLiteRegistry{
    
    protected static $data = array();

    static function add($var, $value){
        @self::$data[$var] = $value;
    }

    static function get($var){
        return @self::$data[$var];
    }

    static function remove($var){
        unset(self::$data[$var]);
    }
}