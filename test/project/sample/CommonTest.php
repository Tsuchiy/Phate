<?php
/**
 * PhateFrameworkPHPUnitテスト基底クラス
 *
 * @package PhateFramework 
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class CommonTest extends PHPUnit_Framework_TestCase
{
    protected $_urlBase = 'http://dev.sample.localserver/';
    protected $_authToken;
    protected $_userId = 1;
    protected $_userPasswd = 'hogehoge';
    protected $_dbHost = 'dev.sample.localserver';
    protected $_dbPort = 3306;
    protected $_dbUser = 'sample';
    protected $_dbPasswd = 'sample';
    protected $_memcacheHost = 'dev.sample.localserver';
    protected $_memcachePort = 11211;

    public function __construct()
    {
        $this->authorization();
    }

    public function __destruct()
    {
        
    }

    /**
     * Http認証用リクエスト
     * 
     * @access public
     * @param void
     * @return void
     */
    public function authorization()
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        // URI
        curl_setopt($ch, CURLOPT_URL, $this->_urlBase . 'login/auth/');
        // header
        $header = array(
            'User-Id: ' . $this->_userId,
            'Platform-Id: 1',
            'Client-Version: 1.00.00.00',
            );
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
        $rtn = msgpack_unserialize($r);
        $this->_authToken = $rtn['authToken'];
        return $rtn;
    }

    /**
     * Httpリクエスト
     * 
     * @access public
     * @param string $url
     * @param mixed $post
     * @return void
     */
    public function httpAccess($url, $post)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        // URI
        curl_setopt($ch, CURLOPT_URL, $this->_urlBase . $url);
        // header
        $header = array(
            'User-Id: ' . $this->_userId,
            'Platform-Id: 1',
            'Client-Version: 1.00.00.00',
            'Auth-Token: ' . $this->_authToken
                );
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
        $rtnHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // cURL リソースを閉じ、システムリソースを解放します
        curl_close($ch);
        if ($httpStatus != 200) {
            return $r;
        }
        $rtnHeader = substr($r, 0, $rtnHeaderSize);
        $rtnBody = substr($r, $rtnHeaderSize);
        echo $rtnHeader;
        echo $rtnBody;
        return msgpack_unserialize($rtnBody);
    }

    /**
     * DBインスタンス取得リクエスト
     * 
     * @access public
     * @param string $dbName mysqlのデータベースネーム
     * @return PDO
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
     * memcache揮発メソッド
     * 
     * @access public
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
