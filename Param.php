<?php

/**
 * * get GET and POST parameter
 * * store values
 * 
 * @package 
 * @version $id$
 * @license 
 */
class Param{
    /**
     * request 
     * 
     * @var mixed
     * @access private
     */
    private $request;
    /**
     * param 
     * 
     * @var mixed
     * @access private
     */
    private $param;
    /**
     * store myself 
     */
    private static $singleton;
    /**
     * values 
     * 
     * @var array
     * @access private
     */
    private $values = array();

    /**
     * Do not use 
     * 
     * @access private
     * @return void
     */
    private function __construct(){
    }

    /**
     * Use this method instead of constructor 
     * 
     * @static
     * @access public
     * @return object
     */
    public static function getInstance(){
        if (!is_object(Param::$singleton)) {
            Param::$singleton = new Param();
        }
        return Param::$singleton;
    }

    /**
     * getRequest 
     * 
     * @param POST or GET or COOKIE 
     * @param mixed $key 
     * @access public
     * @return void
     */
    public function getRequest($type=null, $key=null){
        if($type == 'POST'){
            $request = $_POST;
        }elseif($type == 'GET'){
            $request = $_GET;
        }else{
            $request = $_REQUEST;
        }
        return ($key)? ((array_key_exists($key, $request))? $request["$key"] : null) : $request;
    }

    /**
     * set 
     * 
     * @param mixed $name 
     * @param mixed $values 
     * @access public
     * @return void
     */
    public function set($name, $values=null){
        $this->values["$name"] = $values;
    }

    /**
     * get 
     * 
     * @param mixed $name 
     * @access public
     * @return stored values
     */
    public function get($name=null){
        return ($name)? ((array_key_exists($name, $this->values))? $this->values["$name"] : null) : $this->values;
    }
}    
