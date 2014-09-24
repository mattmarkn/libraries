<?php
/**
 * class Mobile
 *
 * This is for cell phone class
 *
 * @version    Release: 1.2 (update cidr 2011-03-17)
 */
class Mobile {
    /**
     * agent 
     * 
     * @var mixed
     * @access private
     */
    private $agent;
    /**
     * ip 
     * 
     * @var mixed
     * @access private
     */
    private $ip;
    /**
     * IP check 
     * If you need IP check, set true. 
     */
    private $ip_check = false;

    /** 
     * Default settings
     * if you need other CIDR lists, you can set new list.
     * ex. $obj->setCIDRList(array);
    **/

    // DoCoMo
    // http://www.nttdocomo.co.jp/service/imode/make/content/ip/#ip
    private $docomo_cidr = array(
        '210.153.84.0/24',
        '210.136.161.0/24',
        '210.153.86.0/24',
        '124.146.174.0/24',
        '124.146.175.0/24',
        '202.229.176.0/24', // 2009/10
        '202.229.177.0/24', // 2009/10
         '202.229.178.0/24'  // 2009/10
    );
    private $docomo_full_blowser = array(
        '210.153.87.0/24'
    );

    // SOFTBANK
    // http://creation.mb.softbank.jp/web/web_ip.html
    private $softbank_cidr = array(
        '123.108.237.0/27',
        '202.253.96.224/27',
        '210.146.7.192/26',
    );
    private $softbank_full_blowser = array(
        '123.108.237.240/28',
        '202.253.96.0/28'
    );

    // AU
    // http://www.au.kddi.com/ezfactory/tec/spec/ezsava_ip.html
    private $au_cidr = array(
        '210.230.128.224/28',
        '121.111.227.160/27',
        '61.117.1.0/28',
        '219.108.158.0/27',
        '219.125.146.0/28',
        '61.117.2.32/29',
        '61.117.2.40/29',
        '219.108.158.40/29',
        '219.125.148.0/25',
        '222.5.63.0/25',
        '222.5.63.128/25',
        '222.5.62.128/25',
        '59.135.38.128/25',
        '219.108.157.0/25',
        '219.125.145.0/25',
        '121.111.231.0/25',
        '121.111.227.0/25',
        '118.152.214.192/26',
        '118.159.131.0/25',
        '118.159.133.0/25',
        '118.159.132.160/27',
        '111.86.142.0/26',
        '111.86.141.64/26',
        '111.86.141.128/26',
        '111.86.141.192/26',
        '118.159.133.192/26',
        '111.86.143.192/27',
        '111.86.143.224/27',
        '111.86.147.0/27',
        '111.86.142.128/27',
        '111.86.142.160/27',
        '111.86.142.192/27',
        '111.86.142.224/27',
        '111.86.143.0/27',
        '111.86.143.32/27',
        '111.86.147.32/27',
        '111.86.147.64/27',
        '111.86.147.96/27',
        '111.86.147.128/27',
        '111.86.147.160/27',
        '111.86.147.192/27',
        '111.86.147.224/27',
    );

    // WILLCOM
    // http://www.willcom-inc.com/ja/service/contents_service/create/center_info/index.html
    // I will add if willcom users increase...

    // E-Mobile

    /** 
     * construct
     * @access public
     * @param $ip_check bool If you set true that isMobile method checks ip.
     */
    public function __construct($ip_check=null){
        $this->agent = $_SERVER['HTTP_USER_AGENT'];
        $this->ip    = getenv("REMOTE_ADDR");
        $this->ip_check = ($ip_check === true)? true : false;
    }

    /** 
     * isMobile - This method judges mobile phone.
     * @access public
     * @return If it is cell phone, this method returns true otherwise returns false.
     */
    public function isMobile(){
        if($this->ip_check){
            return ($this->getMobileCarrier() && $this->checkMobileIP())? true : false;
        }else{
            return ($this->getMobileCarrier())? true : false;
        }
    }

    /**
     * getMobileCarrier - You can get carrier name
     * @access public
     * @return this method return numbers. details are the following 1.DoCoMo, 2.SoftBank, 3.AU
     */
    public function getMobileCarrier(){
        if(preg_match("/^DoCoMo/", $this->agent)) return 1;
        if(preg_match("/^J-PHONE|^Vodafone|^SoftBank/", $this->agent)) return 2;
        if(preg_match("/^UP.Browser|^KDDI/", $this->agent)) return 3;
        return false;
    }

    /**
     * checkMobileIP - Check client ip that it is in Mobile phone ip cidr.
     * @access private
     * @return 1.DoCoMo, 2.SoftBank, 3.AU
     */
    private function checkMobileIP(){
        if(in_array(true, array_map(array($this,'inCIDR'), $this->docomo_cidr), true)) return 1;
        if(in_array(true, array_map(array($this,'inCIDR'), $this->docomo_full_blowser), true)) return 1;
        if(in_array(true, array_map(array($this,'inCIDR'), $this->softbank_cidr), true)) return 2;
        if(in_array(true, array_map(array($this,'inCIDR'), $this->softbank_full_blowser), true)) return 2;
        if(in_array(true, array_map(array($this,'inCIDR'), $this->au_cidr), true)) return 3;
        return false;
    }

    /**
     * getUserAgent - Get client user agent
     * @access public
     * @return user agent
     */
    public function getUserAgent(){
        return $this->agent;
    }

    /**
     * getIP - Get client ip address
     * @access public
     * @return IP address
     */
    public function getIP(){
        return $this->ip;
    }

    /**
     * getUID - get userID for cellphone.
     *  you cant get userID, if target uses SSL.
     *  this method needs "http://xxxx.com?guid=ON" in template, if target is docomo.
     * @access public
     * @return userID
     **/
    public function getUID(){
        if($this->getMobileCarrier() == 1) return $_SERVER['HTTP_X_DCMGUID'];
        if($this->getMobileCarrier() == 2) return $_SERVER['x-jphone-uid'];
        if($this->getMobileCarrier() == 3) return $_SERVER['x-up-subno'];
        if($this->getMobileCarrier() == 4) return $_SERVER['x-em-uid']; // for E-Mobile. coming soon...
        return false;
    }

    /**
     * setCIDRList - Set CIDR List
     * @access public
     * @param CIDR list ex. array( 'docomo_cidr'=>array('111.222.333.0/24', '444.555.666.0/24'), .... )
     */
    public function setCIDRList(array $list){
        if(array_key_exists('docomo_cidr', $list)) $this->docomo_cidr = $list['docomo_cidr'];
        if(array_key_exists('docomo_full_blowser', $list)) $this->docomo_full_blowser = $list['docomo_full_blowser'];
        if(array_key_exists('softbank_cidr', $list)) $this->softbank_cidr = $list['softbank_cidr'];
        if(array_key_exists('softbank_full_blowser', $list)) $this->softbank_full_blowser = $list['softbank_full_blowser'];
        if(array_key_exists('au_cidr', $list)) $this->au_cidr = $list['au_cidr'];
    }

    /**
     * inCIDR - Check client ip is in CIDR
     * @access private
     * @param  string(CIDR) - ex. 111.222.333.0/24
     * @return If ipaddress matched cidr's range, this method returns true.
     */
    private function inCIDR($cidr) {
        list($network, $mask_bit_len) = explode('/', $cidr);
        $host = 32 - $mask_bit_len;
        $net = ip2long($network) >> $host << $host;
        $ip_net = ip2long($this->ip) >> $host << $host;
        return $net === $ip_net;
    }
}
