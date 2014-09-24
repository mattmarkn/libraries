<?php

/**
 * PHPSession 
 * 
 * @uses Session
 * @package 
 * @version $id$
 * @license 
 */
class PHPSession extends Session {
	
    /**
     *  store myself
     */
	public static $singleton;

    /**
     * Do not use 
     * 
     * @param mixed $session_key 
     * @access protected
     * @return void
     */
	protected function __construct($session_key){
		session_name($session_key);
		session_start();
	}

	/**
	 * Use this method instead of constructor
	 * getter for instance
	 * @param $session_key session key for create session ID
	 * @param $data_object
	 */
	public static function getInstance($session_key){
		if (!is_object(PHPSession::$singleton)) {
			PHPSession::$singleton = new PHPSession($session_key);
		}
		return PHPSession::$singleton;
	}

    /**
     * getSessionID 
     * 
     * @access public
     * @return session id
     */
	public function getSessionID(){
		return session_id();
	}

    /**
     * regenerateSessionID 
     * 
     * @param bool $delete_old_session 
     * @access public
     * @return void
     */
	public function regenerateSessionID($delete_old_session=true){
		session_regenerate_id($delete_old_session);
	}

    /**
     * sessionDestroy 
     * 
     * @access public
     * @return void
     */
	public function sessionDestroy(){
		$this->deleteData();
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
	}

    /**
     * get data 
     * 
     * @param mixed $key 
     * @access public
     * @return session value
     */
	public function getData($key=null){
		if(!$key)	return $_SESSION;
		return (array_key_exists($key, $_SESSION))? $_SESSION["$key"] : false;
	}

    /**
     * set data 
     * 
     * @param mixed $val1 
     * @param mixed $val2 
     * @access public
     * @return void
     */
	public function setData($val1, $val2=null){
		if(is_array($val1)){
			$_SESSION = $val1;
		}else{
			$_SESSION["$val1"] = $val2;
		}
	}

    /**
     * delete data 
     * 
     * @param mixed $key 
     * @access protected
     * @return void
     */
	protected function deleteData($key=null){
		if($key){
			unset($_SESSION["$key"]);
		}else{
			$_SESSION = array();
		}
	}

	protected function garbageCollect(){}
	protected function getSessionIDFromUser(){}
}
