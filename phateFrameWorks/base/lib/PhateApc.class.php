<?php
namespace Phate;
/**
 * Apcクラス
 *
 * 設定ファイル読んで、Apcのストアを操作するクラス
 * 名前空間毎にprefixの処理なんかもする
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class Apc
{
    
    private static $_config;
    private static $_getDisable = false;
    
    /**
     * 設定ファイルよりapcの設定を取得
     * 
     * @access private
     * @param void
     * @return void
     */
    private static function setConfig()
    {
        if (!function_exists('apc_store')) {
            throw new CommonException('no apc module');
        }
        ini_set('apc.serializer', 'msgpack');
        if (!($fileName = Core::getConfigure('apc_config_file'))) {
            throw new CommonException('no apc configure');
        }
        if (!(self::$_config = Common::parseConfigYaml(PHATE_CONFIG_DIR . $fileName))) {
            throw new CommonException('no apc configure');
        }
    }
    
    /**
     * apcに値を格納
     * 
     * @access public
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @param string $namespace
     * @return boolean
     */
    public static function set($key, $value, $ttl = null, $namespace = 'default')
    {
        $realTtl = is_null($ttl) ? self::$_config[$namespace]['default_ttl'] : $ttl;
        $realKey = self::$_config[$namespace]['default_prefix'] . $key;
        
        return apc_store($realKey, $value, $realTtl);
    }
    
    /**
     * apcより値を取得
     * 
     * @access public
     * @param string $key
     * @param string $namespace
     * @return mixed/false
     */
    public static function get($key, $namespace = 'default')
    {
        if (self::$_getDisable) {
            return false;
        }
        $realKey = self::$_config[$namespace]['default_prefix'] . $key;
        return apc_fetch($realKey);
    }
    
    /**
     * apcより値を消去
     * 
     * @access public
     * @param string $key
     * @param string $namespace
     * @return boolean
     */
    public static function delete($key, $namespace = 'default')
    {
        $realKey = self::$_config[$namespace]['default_prefix'] . $key;
        return apc_delete($realKey);
    }
    
    
    /**
     * apcより全てのキー一覧を取得（ただし保証はされない）
     * 
     * @access public
     * @param string $namespace
     * @return array
     */
    public static function getAllKeys($namespace = null)
    {
        if (is_null($namespace)) {
            $pattern = '/^' . preg_quote(self::$_config[$namespace]['default_prefix']) . '(.*)$/';
            $apcIterator = new APCIterator('user', $pattern);
        } else {
            $apcIterator = new APCIterator('user');
        }
        
        $rtn = [];
        $apcIterator->rewind();
        if ($apcIterator->key() === false) {
            return [];
        }
        while($key = $apcIterator->key()) {
            $rtn[] = $key;
            $apcIterator->next();
        } 
        
        return $rtn;
    }
    
    /**
     * apc機能の無効化を行う
     * debug時用
     * 
     * @access public
     * @param boolean $disable
     * @return integer
     */
    public static function setGetDisable($disable = true)
    {
        self::$_getDisable = $disable;
    }
}
