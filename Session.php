<?php
/**
 * Class for Session 
 * 
 * @abstract
 * @package 
 * @version $id$
 * @license 
 */
abstract class Session {

    /**
     * session_id 
     * 
     * @var mixed
     * @access protected
     */
	protected $session_id;
    /**
     * session_values 
     * 
     * @var mixed
     * @access protected
     */
	protected $session_values;
    /**
     * session_key 
     * 
     * @var mixed
     * @access protected
     */
	protected $session_key;
    /**
     *  store myself 
     */
	public static $singleton;

	/**
	 * construct
	 * start session
	 * @param $session_key session key for create session ID
	 * @param $data_object
	 */
	protected function __construct($session_key){
		$this->session_key = $session_key;
	}

	/**
	 * get instance
	 * getter for instance
	 * @param $session_key session key for create session ID
	 * @param $data_object
	 */
	public static function getInstance($session_key){
		if (!is_object(Session::$singleton)) {
			Session::$singleton = new Session($session_key);
		}
		return Session::$singleton;
	}

	/**
	 * setter for session ID
	 * @param $session_id session id
	 */
	public function set($val1, $val2=null){
		if(is_array($val1)){
			$this->session_values = $val1;
		}else{
			$this->session_values["$val1"] = $val2;
		}
	}

	/**
	 * getter for session value
	 * @param $key target session value's key 
	 * @return array session value or false
	 */
	public function get($key=null){
		$data = $this->getData($this->session_id);
		if($data) return ($key)? $data[$key] : $data;
		return false;
	}

	/**
	 * get session ID
	 * @return session ID
	 */
	public function getSessionID($seed=null){
		if(!$this->session_id){
			$this->session_id = ($this->getSessionIDFromUser())?
				$this->getSessionIDFromUser() : $this->createSessionID($seed);
		}
		return $this->session_id;
	}

	/**
	 * cookie or get prameters?
	 */
	abstract protected function getSessionIDFromUser();

	/**
	 * save
	 * save session value 
	 */
	public function save(){
		$this->setData($this->session_values);
	}
	/**
	 * create session ID
	 * @return session ID
	 */
	protected function createSessionID($seed){
		return md5($this->session_key.$seed);
	}
	/**
	 * get data
	 * override this
	 * @param session ID
	 */
	abstract protected function getData();

	/**
	 * set data
	 * override this
	 * @param $val1 key name or array
	 * @param $val2 if exists val1, you can set value.
	 */
	abstract protected function setData($val1, $val2=null);

	/**
	 * Delete data
	 * override this
	 * @param array target keys
	 */
	abstract protected function deleteData();

	/**
	 * garbage collect
	 */
	abstract protected function garbageCollect();
}
