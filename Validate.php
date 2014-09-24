<?php
/**
 * This is simple Validate class
 *
 * how to use
 *  $validate = new Validate($pattern);
 *  $validate->setValidateType($validate_types);
 *  $validate->setValue($input_values);
 *  $is_valid = $validate->isValid();
 *  $valid_value = $validate->getValidatedValue();
 *  $input_error = $validate->getError();
 *
 * @version Release: 1.0
 **/

class Validate{
    /**
     * validate type(s)
     * @var array
     **/
    private $validate_type;

    /**
     * valid values
     * @var mixed
     **/
    private $validated;

    /**
     * key name and validate type of error
     * @var array
     **/
    private $error;

    /**
     * This is example.
     * If you want to check length, you need to write 'length:1-10' to setValidateType's arg.
     * ex. $this->setValidateType('name' => 'length:1-10')
     *
     * If you need renge check, use regular expression.
     * For example 18 to 100.
     * 'range' => "/^1[8-9]$|^[2-9][0-9]$|^100$/"
     *
     * @var array 
     **/
    private $pattern = array(
        'number' => "/^[0-9]+$/",
        'email' => "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i",
        'required' => "/.+/"
    );

    /**
     * constructor
     * @access public
     * @param array $patterns patterns for preg_match
     **/
    public function __construct(array $patterns=null){
        if($patterns) $this->pattern = $patterns;
    }

    /**
     * Setter for validate type
     * @access public
     * @param mixed $val1 value (array) or key name (string)
     *   (If you want to set key name, you have to set value on val2.)
     * @param string $val2 value
     **/
    public function setValidateType($val1, $val2=NULL){
        if(is_array($val1)){
            $this->validate_type = $val1;
        }else{
            $this->validate_type["$val1"] = $val2;
        }
        return $this;
    }

    /**
     * Getter for validate type
     * @access public
     * @param  string $valname key name
     * @return mixed valiadtae type (string) or validate types (array)
     **/
    public function getValidateType($valname=NULL){
        if($valname){
            return $this->validate_type["$valname"];
        }else{
            return $this->validate_type;
        }
    }

    /**
     * Reset validate type
     * @access public
     * @param string $valname key name
     **/
    public function clearValidateType($valname=NULL){
        if($valname){
            unset($this->validate_type["$valname"]);
        }else{
            unset($this->validate_type);
        }
    }

    /**
     * Setter for values
     * @access public
     * @param mixed $val1 value (array) or key name (string)
     *   (If you want to set key name, you have to set value on val2.)
     * @param string $val2 value
     **/
    public function setValue($val1, $val2=NULL){
        if(is_array($val1)){
            $this->validate_value = $val1;
        }elseif($val2){
            $this->validate_value["$val1"] = $val2;
        }
        return $this;
    }

    /**
     * Start validate.
     * @access public
     * @return bool true (valid) or false (not valid)
     **/
    public function isValid(){
        foreach ($this->validate_type as $key => $value){
            $is_valid = $this->checkValue($key, $value);
            if(is_array($is_valid)){
                $this->error["$key"] = $is_valid;
            }else{
                $this->validated["$key"] = $this->validate_value["$key"];
            }
        }
        return ($this->getError())? false : true;
    }

    /**
     * This method returns correct value(s) that validated by check method.
     * @access public
     * @param string $key key name that you want.
     * @return mixed value (string) or all values (array)
     **/
    public function getValidatedValue( $key=NULL ){
        return ($key)? $this->validated["$key"] : $this->validated;
    }

    /**
     * This method returns error types that made by isValid.
     * @access public
     * @param string $key key name that you want
     * @return mixed error type (string) or all error types (array)
     **/
    public function getError( $key=null ){
        return ($key)? $this->error["$key"] : $this->error;
    }

    /** 
     * This method checks whether values are correct.
     * @access private
     * @param string $name value name checked by validator
     * @param string $type validate types
     * @return bool true (match) or error types (not match)
     **/
    private function checkValue($name, $type){
        $vtype = $this->separateComma($type);
        for($i=0; $i < count($vtype); $i++){
            $is_valid = $this->doCheck($vtype[$i], $name);
            if(!$is_valid){
                $errors[] = $vtype["$i"];
            }
        }
        return (isset($errors))? $errors : true;
    }

    /**
     * At first, if validate type is length check, 
     * this method makes pattern for length check.
     * Then it check value with pattern.
     * @access private
     * @param string $type pattern for validate
     * @param string $name key name ($this->validate_value)
     * @return bool true(match) or false(not match)
     **/
    private function doCheck($type, $name){
        if(!$this->isKeyExists($name)) return false;

        $pattern = (preg_match("/^length:/", $type))?
             $this->makeLength($type) : $this->pattern["$type"];

        return (preg_match($pattern, $this->validate_value["$name"]))? true : false;
    }

    /**
     * This method checks that target key already exists in $this->valudate_value. 
     * @access private
     * @param string $key target key
     * @return bool true(key exsits) or false (not exsits)
     */
    private function isKeyExists($key){
        return array_key_exists($key, $this->validate_value);
    }

    /** 
     * This method makes patturn for length check.
     * @access private
     * @param string $value length data (ex. length:1-10)
     * @return string pattern
     **/
    private function makeLength($value){
        list($min, $max) = $this->makeMinMax($value);    
        return '/^.{' .$min. ',' .$max. '}$/';
    }

    /**
     * This method gets min and max length from $value.
     * @access private
     * @param string $value length data (ex. length:1-10)
     * @return array min and max length
     **/
    private function makeMinMax($value){
        $param = explode(":", $value);
        $min_max = explode("-", array_pop($param));
        return array_map('trim', $min_max);
    }

    /**
     * This method separates value by comma.
     * @access private
     * @param string $value string (include comma)
     * @return array separated values
     **/
    private function separateComma($value){
        $result = explode(",", $value);
        return array_map('trim', $result);
    }
}
