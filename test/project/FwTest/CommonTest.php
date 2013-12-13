<?php

class CommonTest extends PHPUnit_Framework_TestCase
{
    protected $_urlBase = 'http://dev.localserver/';
    protected $_authToken;
    protected $_userId = 30;
    protected $_userPasswd = 'hogehoge';
    protected $_dbHost = 'dev.localserver';
    protected $_dbPort = 3306;
    protected $_dbUser = 'pain';
    protected $_dbPasswd = 'Kurop6ZF';
    protected $_memcacheHost = 'pain.localserver';
    protected $_memcachePort = 11211;

    public function __construct()
    {
        $this->authorization();
    }

    public function __destruct()
    {
        
    }

    /**
     * 認証作業を行う
     * @param void
     * @return void
     */
    public function authorization()
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
        // URI
        curl_setopt($ch, CURLOPT_URL, $this->_urlBase . 'login/Test');
        // header
        $header = array('User-Id: ' . $this->_userId);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // post
        $post  = 'userId=' . $this->_userId;
        $post .= '&password=' . $this->_userPasswd;
        curl_setopt ($ch,CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post);
        // URL を取得し、ブラウザに渡します
        $r = curl_exec($ch);
        // cURL リソースを閉じ、システムリソースを解放します
        curl_close($ch);
        print_r($r);
        //$rtn = msgpack_unserialize($r);
        //$this->_authToken = $rtn['authToken'];
    }

    /**
     * http通信を行う
     * @param string $url
     * @param array $post
     * @return array
     */
    public function httpAccess($url, $post)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        // URI
        curl_setopt($ch, CURLOPT_URL, $this->_urlBase . $url);
        // header
        $header = array('User-Id: ' . $this->_userId, 'Auth-Token: ' . $this->_authToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // post
        $postField = '';
        foreach ($post as $k => $v) {
            $postField .= urlencode($k) . '=' . urlencode($v) . '&';
        }
        $postField = substr($postField, 0, -1);
        curl_setopt ($ch,CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post);
        // URL を取得し、ブラウザに渡します
        $r = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // cURL リソースを閉じ、システムリソースを解放します
        curl_close($ch);
        if ($httpStatus != 200) {
            return $r;
        }
        $header = substr($r, 0, $headerSize);
        $body = substr($r, $headerSize);
        // echo $header;
        // echo $body;
        return msgpack_unserialize($body);
    }

    /**
     * http通信を行う
     * @param string $url
     * @param array $post
     * @return string
     */
    public function htmlAccess($url, $post)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        // URI
        curl_setopt($ch, CURLOPT_URL, $this->_urlBase . $url);
        // header
        $header = array('User-Id: ' . $this->_userId, 'Auth-Token: ' . $this->_authToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // post
        $postField = '';
        foreach ($post as $k => $v) {
            $postField .= urlencode($k) . '=' . urlencode($v) . '&';
        }
        $postField = substr($postField, 0, -1);
        curl_setopt ($ch,CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post);
        // URL を取得し、ブラウザに渡します
        $r = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // cURL リソースを閉じ、システムリソースを解放します
        curl_close($ch);
        if ($httpStatus != 200) {
            return $r;
        }
        $header = substr($r, 0, $headerSize);
        $body = substr($r, $headerSize);
        return $body;
    }
    
    
    /**
     * PDOの取得
     * @param string $dbName
     * @return \PDO
     */
    public function dbGetInstance($dbName)
    {
        $dsn  = 'mysql:';
        $dsn .= 'host=' . $this->_dbHost . ';';
        $dsn .= 'port=' . $this->_dbPort . ';';
        $dsn .= 'dbname=' . $dbName . ';';
        $dsn .= 'charset=utf8';
        $user = $this->_dbUser;
        $password = $this->_dbPasswd;
        $instance = new PDO($dsn, $user, $password);
        $instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $instance;
    }
    /**
     * memcacheのクリア
     * @param void
     * @return void
     */
    public function flushMemcached()
    {
        $m = new Memcache;
        $m->connect($this->_memcacheHost, $this->_memcachePort);
        $m->flush();
    }
}
