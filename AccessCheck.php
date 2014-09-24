<?php
/**
 * class AccessCheck
 * This class get and check HTTP header of target sites.
 *
 * @version Release 1.0
 *
 * how to use
 * $access_check = new AccessCheck($url);
 * $access_check->check('200') // true means 200 ok
 **/
class AccessCheck{

    /**
     * check URL 
     * 
     * @var string
     * @access private
     */
    private $url;

    /**
     * @access public
     **/
    public function __construct($url=null){
        if($url) $this->setURL($url);
    }

    /**
     * set URL
     * @access public
     * @param $url URL
     **/
    public function setURL($url){
        $this->url = $url;
    }

    /**
     * check and get result
     * @access public
     * @param $check_word
     * @param $header_name target header name. if null, this param get status automatically.
     * @return bool true or false
     **/
    public function check($check_word, $header_name=null){
        if(!$header_name) $header_name = '0';
        $header = $this->getHeaders();
        return (strpos($header[$header_name], $check_word) === false)? false : true;
    }

    /**
     * get header
     * @return array headers in array
     **/
    public function getHeaders(){
        return get_headers($this->url, 1);
    }

}
