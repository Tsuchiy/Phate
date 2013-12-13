<?php
/**
 * PhateValidatorクラス
 *
 * Validatorクラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateValidator
{

    private static $_instance;

    private $_validatorList = array(
        'noblank'          => '_typeNoBlank',           // 必須項目
        'number'           => '_typeNumber',            // 数字項目
        'numberminmax'     => '_typeNumberMinMax',      // 数字項目（上限下限）
        'alphabet'         => '_typeAlphabet',          // アルファベットのみ
        'alphabetornumber' => '_typeAlphabetOrNumber',  // 英数アルファベットのみ
        'lenminmax'        => '_typeLenMinMax',         // byte数（上限下限）
        'widthminmax'      => '_typeWidthMinMax',       // 文字数（上限下限）
        'enum'             => '_typeEnum',              // 配列列挙にマッチするか
        'array'            => '_typeArray',             // 配列
        'notarray'         => '_typeNotArray',          // 配列以外
        'arraycountminmax' => '_typeArrayCountMinMax',  // 配列の大きさ（上限下限）
        'preg'             => '_typePreg',              // preg_match
        'mbEreg'           => '_typeMbEreg',            // mb_ereg_match
    );

    private $_registeredValidators = array();
    private $_resultValidation = array();

    /**
     * コンストラクタ
     * 
     * @access private
     */
    private function __construct()
    {
    }
    
    
    /**
     * シングルトン生成
     * 
     * @access public
     * @param void
     * @return PhateValidator 
     */
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            self::$_instance = new PhateValidator();
        }
        return self::$_instance;
    }

    /**
     * validatorにルールをセット
     * 
     * @access public
     * @param string $paramName
     * @param string $validatorName
     * @param array $param(範囲指定ご具体的なデータ等)
     * @param boolean $isChain 違反時処理継続するか
     * @throws PhateCommonException 
     */
    public function setValidator($paramName, $validatorName, $param = array(), $isChain = false)
    {
        if (!in_array($validatorName, array_keys($this->_validatorList))) {
            throw new PhateCommonException('validator error');
        }
        if (!array_key_exists($paramName, $this->_registeredValidators)) {
            $this->_registeredValidators[$paramName] = array();
        }
        $this->_registeredValidators[$paramName][] = array('name' => $validatorName, 'param' => $param, 'isChain' => $isChain);
    }

    /**
     * validatorを実行
     * 
     * @access public
     * @param void
     * @return array 結果セット
     */
    public function execute()
    {
        foreach ($this->_registeredValidators as $paramName => $validationArray) {
            $result = true;
            $requestParam = PhateHttpRequest::getRequestParam($paramName);
            foreach ($validationArray as $validation) {
                if (!$result && !$validation['isChain']) {
                    break;
                }
                $function = $this->_validatorList[$validation['name']];
                $result = $this->$function($requestParam, $validation['param']);
                $this->_resultValidation[$paramName][] = array('name' => $validation['name'], 'param' => $validation['param'], 'result' => $result);
                
            }
        }
        return $this->_resultValidation;
        
    }
    
    /**
     * 空白(null)は許さない
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeNoBlank ($requestParam, $validationParam)
    {
        if (is_null($requestParam)) {
            return false;
        }
        return true;
    }
    /**
     * 数字のみ許す
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return type
     */
    protected function _typeNumber ($requestParam, $validationParam)
    {
        return is_numeric($requestParam);
    }
    /**
     * 数値、ある数字以上ある数字以下
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeNumberMinMax ($requestParam, $validationParam)
    {
        if (!is_numeric($requestParam)) {
            return false;
        }
        if (!is_array($validationParam) || (count($validationParam) != 2)) {
            return false;
        }
        if (!is_numeric($min = array_shift($validationParam))) {
            return false;
        }
        if (!is_numeric($max = array_shift($validationParam))) {
            return false;
        }
        if (($min > $requestParam) || ($max < $requestParam)) {
            return false;
        } 
        return true;
    }

    /**
     * アルファベットのみ
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeAlphabet ($requestParam, $validationParam)
    {
        if (!is_string($requestParam)) {
            return false;
        }
        return ctype_alpha($requestParam);
    }
    /**
     * アルファベットか数字によって構成された文字列
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeAlphabetOrNumber ($requestParam, $validationParam)
    {
        if (!is_string($requestParam)) {
            return false;
        }
        return ctype_alnum($requestParam);
    }
    
    /**
     * バイト数の長さをチェック
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    
    protected function _typeLenMinMax  ($requestParam, $validationParam)
    {
        if (!is_string($requestParam)) {
            return false;
        }
        if (!is_array($validationParam) || (count($validationParam) != 2)) {
            return false;
        }
        if (!is_numeric($min = array_shift($validationParam))) {
            return false;
        }
        if (!is_numeric($max = array_shift($validationParam))) {
            return false;
        }
        if (($min > strlen($requestParam)) || ($max < strlen($requestParam))) {
            return false;
        } 
        return true;
    }
    
    /**
     * 文字数の長さチェック（全角も一文字）
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeWidthMinMax  ($requestParam, $validationParam)
    {
        if (!is_string($requestParam)) {
            return false;
        }
        if (!is_array($validationParam) || (count($validationParam) != 2)) {
            return false;
        }
        if (!is_numeric($min = array_shift($validationParam))) {
            return false;
        }
        if (!is_numeric($max = array_shift($validationParam))) {
            return false;
        }
        if (($min > mb_strlen($requestParam)) || ($max < mb_strlen($requestParam))) {
            return false;
        } 
        return true;
    }
    /**
     * 入力されたものが指定された配列の要素か
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeEnum ($requestParam, $validationParam)
    {
        if (!is_array($validationParam)) {
            return false;
        }
        return in_array($requestParam, $validationParam);
    }
    
    /**
     * 配列か
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return type
     */
    protected function _typeArray ($requestParam, $validationParam)
    {
        return is_array($requestParam);
    }
    
    /**
     * 配列ではないか
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return type
     */
    protected function _typeNotArray ($requestParam, $validationParam)
    {
        return !is_array($requestParam);
    }
    
    /**
     * 配列の要素数が範囲内か
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeArrayCountMinMax ($requestParam, $validationParam)
    {
        if (!is_array($requestParam)) {
            return false;
        }
        if (!is_array($validationParam) || (count($validationParam) != 2)) {
            return false;
        }
        if (!is_numeric($min = array_shift($validationParam))) {
            return false;
        }
        if (!is_numeric($max = array_shift($validationParam))) {
            return false;
        }
        $cnt = count($requestParam);
        if (($min > $cnt) || ($max < $cnt)) {
            return false;
        } 
        return true;
    }
    /**
     * 正規表現にマッチするか
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typePreg ($requestParam, $validationParam) 
    {
        if (!is_string($requestParam) || !is_string($validationParam)) {
            return false;
        }
        return preg_match($validationParam, $requestParam) !== false;
    }

    /**
     * 全角使用正規表現にマッチするか
     * 
     * @param type $requestParam
     * @param type $validationParam
     * @return boolean
     */
    protected function _typeMbEreg ($requestParam, $validationParam) 
    {
        if (!is_string($requestParam) || !is_string($validationParam)) {
            return false;
        }
        return mb_ereg($validationParam, $requestParam) !== false;
    }
}
