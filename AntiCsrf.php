<?php

/**
 * Anti Csrf 
 * 
 * @uses Cookie
 * @package 
 * @version $id$
 * @license 
 */
class AntiCsrf extends Cookie {
    /**
     * cookie domain 
     * 
     * @var mixed
     * @access private
     */
    private $cookie_domain;
    /**
     * expire time 
     * 
     * @var int
     * @access private
     */
    private $expire;
    /**
     * token name 
     * 
     * @var mixed
     * @access private
     */
    private $token_name;

    /**
     * __construct 
     * 
     * @param mixed $domain 
     * @param mixed $expire 
     * @param mixed $token_name 
     * @access public
     * @return void
     */
    public function __construct($domain, $expire, $token_name){
        $this->cookie_domain = $domain;
        $this->expire = $expire;
        $this->token_name = $token_name;
    }

    /**
     * CSRFのチェックを開始します。（キーの発行とクッキーのセット）
     *
     * @param  無し
     * @return クッキー発行成功時、キーを返す。失敗時はfalse
     * @access public
         * @Example: 
         *     $key = $this->startCsrfCheck();
     *
     *     ....hiddenでキーを渡す....
     *
     *     if ($this->isCsrf()) //trueだったらcsrf
     */
    public function startCsrfCheck() {
        $csrfToken = $this->_generateCsrfToken();
        return ($this->_setCookie($this->token_name, $csrfToken)) ? $csrfToken : false;
    }

    /**
     * CSRFかのチェックをします。
     *
     * @param  キー
     * @return CSRFである:true CSRFではない:false
     * @access public
     */
    public function isCsrf($csrfSiteKey) {
        $csrfToken = $this->_getCsrfToken();
        $this->_deleteCookie($this->token_name);
        return ($csrfToken && $csrfSiteKey === $csrfToken) ? false : true;
    }

    /**
     * CSRFチェック用のキーを生成します。
     *
     * @param  無し
     * @return 32文字半角英数字のキー
     * @access private
     */
    private function _generateCsrfToken() {
        $key = preg_replace("/[^[:alnum:]]/", "", base64_encode(hash("sha384", mt_rand(),true)));
        return substr($key, 1, 32);
    }

    /**
     * クッキーからCSRFチェック用のキーを取得します。
     *
     * @param  無し
     * @return 32文字半角英数字のキー
     * @access private
     */
    private function _getCsrfToken() {
        return preg_replace('/[^a-zA-Z0-9]/', '', $_COOKIE[$this->token_name]);
    }
}
