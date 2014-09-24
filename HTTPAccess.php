<?php
/**
 * class HTTPAccess 
 * This class access tool for web.
 *
 * @version Release:1.2
 *
 * how to use
 *  $web_contents = new HTTPAccess($url);
 *  $web_contents->get();
 **/
class HTTPAccess{

    /**
     * User Agents 
     * This is default settings.
     * If you need more user agents, you can set it on constructor or setter.
     */
    private $user_agent = array(
        'IE6' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1);',
        'IE7' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)',
        'IE8' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)',
        'Chrome' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13',
        'LunaScape' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; Lunascape 5.0 alpha2)',
        'Firefox3'=> 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ja; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6',
        'Safari4' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_6; ja-jp) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16',
        'Opera9' => 'Opera/9.62 (Windows NT 5.1; U; ja) Presto/2.1.1',
        'GoogleBot' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        'iPhone3G' => 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_1 like Mac OS X; ja-jp) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5F136 Safari/525.20',
        'AU' => 'KDDI-HI31 UP.Browser/6.2.0.5 (GUI) MMP/2.0',
        'DoCoMo' => 'DoCoMo/2.0 F900i(c100;TB;W22H12)',
    );

    // default values
    private $language = 'ja,en';
    private $timeout = 5;
    private $url;
    private $agent;
    private $cookie;
    private $query_values;
    private $header;
    private $method = 'GET';
    private $contents;
    private $result;
    private $eol = "\r\n";

    /**
     * constructor
     * @access public
     * @param $url URL
     * @param $user_agent Your user agent
     **/
    public function __construct($url=null, array $user_agent=null){
        if($url) $this->setUrl($url);
        if($user_agent) $this->setAgent($user_agent);
    }

    /**
     * Setter for URL
     * @access public
     * @param $url URL
     */
    public function setURL($url){
        $this->url = $url;
    }

    /** 
     * Setter for querys
     * @access public
     * @param $values querys
     */
    public function setQuerys($values){
        $this->quiry_values = $values;
    }

    /**
     * Setter for method
     * @access public
     * @param GET or POST
     */
    public function setMethod($method){
        $this->method = $method;
    }

    /**
     * Setter for user agent
     * @access public
     * @param $user_agent User Agent
     */
    public function setAgent($user_agent){
        $this->agent = $this->user_agent["$user_agent"];
        $this->header .= 'User-Agent: '. $this->agent .$this->eol;
    }

    /**
     * Setter for cookies
     * @access public
     * @param $cookie cookie value
     */
    public function setCookie($cookie){
        $this->cookie = $cookie;
        $this->header .= 'Cookie: '. $this->cookie .$this->eol;
    }

    /**
     * Setter for language
     * @access public
     * @param $language language  ex. ja
     */ 
    public function setLanguage($language){
        $this->language = $language;
        $this->header .= 'Accept-language: '. $this->language .$this->eol;
    }

    /**
     * Setter for referer
     * @access public
     * @param $referer referer
     */
    public function setReferer($referer){
        $this->referer = $referer;
        $this->header .= 'Referer: '. $this->referer .$this->eol;
    }

    /**
     * Setter for contents(post values)
     * @access public
     * @param $contents 
     */
    public function setContents(array $contents){
        $this->contents = $contents;
    }

    /**
     * Setter for User Agent list
     * @access public
     * @param $list User Agent list like $user_agent member variable
     */
    public function setUserAgentList(array $list){
        $this->user_agent = $list;
    }

    /**
     * This method returns contents, that get from target site.
     * @access public
     * @param $start beginning point that you need.
     * @param $end end point that you need.
     * @return contents
     */
    public function get($start=null, $end=null){
        $this->setMethod('GET');
        $contents =  $this->access();
        if($start && $end){
            preg_match_all("/$start(.+?)$end/ms",$contents, $match);
            return $match[1];
        }
        return $contents;
    }

    /**
     * This method posts to target site.
     * @access public
     * @param $values Post values.
     * @return boolen IF success post, return true.
     */
    public function post(array $values){
        $this->setMethod('POST');
        $this->setContents($values);
        $this->result = $this->access();
        return (!is_null($this->result))? true : false;
    }

    /**
     * This method returns result of post.
     * @access public
     * @return result of post
     */
    public function getResult(){
        return $this->result;
    }
    
    /**
     * This method accesses to site and get contents.
     * @access private
     * @return contents
     */
    private function access(){
        return file_get_contents("$this->url".$this->getQuery(), false, $this->getOptions());
    }

    /**
     * This method returns options.
     * @access private
     * @return options
     */
    private function getOptions(){
        $data['method'] = $this->method;
        $data['header'] = $this->header;
        if($this->contents) $data['content'] = http_build_query($this->contents);
        $data['timeout'] = $this->timeout;

        return stream_context_create( array('http'=> $data) );
    }

    /**
     * This method returns query
     * @access private
     * @return query
     */
    private function getQuery(){
        if($this->query_values) $quiry = http_build_query($this->query_values);
        return '?' . $quiry;
    }
}
