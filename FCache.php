<?php
/**
 * this method caches data in file.
 *
 * @version Release 1.0
 */
class FCache{
    private $values;
    private $is_delete = false;
    private static $filename;

    /**
     * this method returns instance like constructor.
     * @access public
     * @param $file_name save file name
     * @return instance of this class
     */
    public static function getInstance($file_name){
        self::$filename = $file_name;
        
        if(file_exists(self::$filename))
            $singleton = unserialize(file_get_contents(self::$filename));
        else
            $singleton = new FCache();

        return $singleton;
    }

    /**
     * constructor
     * If you need instance of this class, call getInstance method.
     * @access private
     */
    private function __construct(){
        $this->values = array();
    }

    /**
     * setter for file name
     * @access public
     * @param $filename file name
     */
    public function setFileName($filename){
        self::$filename = $filename;
    }

    /**
     * setter for value
     * @access public
     * @param $key key name
     * @param $val value
     */
    public function set($key,$val){
        $this->values[$key]=$val;
    }

    /**
     * getter for value
     * @access public
     * @param $key key name
     */
    public function get($key){
        return $this->values[$key];
    }

    /**
     * Execte save (write and delete)
     * @access public
     * @return true(success) or false(fail)
     */
    public function save(){
        if($this->is_delete) return unlink(self::$filename);
        return file_put_contents(self::$filename, serialize($this));
    }

    /**
     * delete target value.
     * @param $name key name of target value
     */
    public function delete($name){
        unset($this->values[$name]);
    }

    /**
     * set delete flag
     */
    public function flush(){
        $this->is_delete = true;
    }
}
