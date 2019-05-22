<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

class EpLite_Custom_Error{
    protected static $message = array();

    static function add($entity, $source, $message){
        self::$message[$entity][$source][] = $message;
    }

    static function get_all(){
        return self::$message;
    }

    static function get_by_entity($entity){
        return @self::$message[$entity];
    }

    static function get_by_source($entity, $source){
        return @self::$message[$entity][$source];
    }

    
}