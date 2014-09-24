<?php

/**
 * Cookie 
 * 
 * @package 
 * @version 1.0
 * @license 
 */
class Cookie {
    /**
     * store myself 
     * 
     * @var object
     * @access public static
     */
    public static $singleton = array();
    /**
     * cookie name 
     * 
     * @var mixed
     * @access private
     */
    private $cookie_name;
    /**
     * cookie_domain 
     * 
     * @var string
     * @access private
     */
    private $cookie_domain;
    /**
     * expire 
     * 
     * @var int
     * @access private
     */
    private $expire;

    /**
     * Do not use 
     * 
     * @param mixed $domain 
     * @param mixed $expire 
     * @param mixed $name 
     * @access private
     * @return void
     */
    private function __construct($domain, $expire, $name){
        $this->cookie_domain = $domain;
        $this->expire = $expire;
        $this->setCookieName($name);
    }

    /**
     * Use this method instead of constructor
     * 
     * @param mixed $cookie_name 
     * @static
     * @access public
     * @return void
     */
    public static function getInstance($cookie_name){
        if (!is_object(Cookie::$singleton["$cookie_name"])) {
            Cookie::$singleton["$cookie_name"] = new Cookie($cookie_name);
        }
        return Cookie::$singleton["$cookie_name"];
    }

    /**
     * setCookieName 
     * 
     * @param mixed $name 
     * @access public
     * @return void
     */
    public function setCookieName($name){
        $this->cookie_name = $name;
    }

    /**
    * cookieをセットします。
    *
    * @param  クッキー名, クッキーの値
    * @return true or false
    * @access private
     * @return bool
    */
    public function set($value, $name=null) {
          if(!$name) $name = $this->cookie_name;
      $expire = time() + $this->expire;
      return setcookie($name, $value, $expire, '/', $this->cookie_domain);
    }
  
    /**
     * cookieを削除します。
     *
     * @param  クッキー名
     * @return true or false
     * @access private
     * @return bool
     */
    public function delete($name=null) {
          if(!$name) $name = $this->cookie_name;
      return setcookie($name, '', time()-1, '/', $this->cookie_domain);
    }

    /**
     * get 
     * 
     * @param mixed $name cookie name 
     * @access public
     * @return cookie value
     */
    public function get($name=null){
        if(!$name) $name = $this->cookie_name;
        return $_COOKIE[$name];
    }
}
