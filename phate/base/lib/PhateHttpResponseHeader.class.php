<?php
/**
 * PhateHttpResponseHeaderクラス
 *
 * Httpレスポンスを行う際、必要な処理を格納しておくクラス
 * 主にはレスポンスヘッダ・HTTPステータスの設定
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateHttpResponseHeader
{
    const HTTP_OK = 200;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    
    private static $_httpStatus = self::HTTP_OK;
    private static $_contentType;
    private static $_redirectUrl;
    private static $_logicalErrorCode = 0;
    private static $_headerParam = array();
    
    /**
     * レスポンスのヘッダを送信する
     * 
     * @access public
     * @param void
     * @return void
     * @throws PhateKillException
     */
    public static function sendResponseHeader()
    {
        // リダイレクト設定がある場合はリダイレクトして終了
        if (isset(self::$_redirectUrl)) {
            header("Location: " . self::$_redirectUrl);
            throw new PhateKillException();
            return;
        }
        // それ以外のレスポンス
        http_response_code(self::$_httpStatus);
        if (self::$_httpStatus != self::HTTP_OK) {
            return;
        }
        if (isset(self::$_contentType)) {
            header("Content-Type: " . self::$_contentType);
        }
        header("Response-Code: " . self::$_logicalErrorCode);
        foreach (self::$_headerParam as $key => $value) {
            header($key . ": " . $value);
        }
        
        return;
    }
    
    /**
     * レスポンス時に返すHTTPStatusをセットする
     * 
     * @access public
     * @param string $statusCode
     * @return void
     */
    public static function setHttpStatus($statusCode)
    {
        self::$_httpStatus = $statusCode;
        return;
    }
    /**
     * ロジックエラー（未実装）
     * 
     * @access public
     * @param void
     * @return string
     */
    public static function getLogicalErrorCode()
    {
        return self::$_logicalErrorCode;
    }
    
    /**
     * ロジックエラー(未実装)
     * 
     * @access public
     * @param string $statusCode
     * @return void
     */
    public static function setLogicalErrorCode($statusCode)
    {
        self::$_logicalErrorCode = $statusCode;
        return;
    }
    
    /**
     * content-typeヘッダの設定
     * 
     * @access public
     * @param string $contentType
     * @return void
     */
    public static function setContentType($contentType)
    {
        self::$_contentType = $contentType;
        return;
    }

    /**
     * リダイレクトヘッダの設定(設定されているとリダイレクトが優先される)
     * 
     * @access public
     * @param string $url
     * @return void
     */
    public static function setRedirectUrl($url)
    {
        self::$_redirectUrl = $url;
        return;
    }
    
    /**
     * レスポンス時のHTTPヘッダの設定
     * 
     * @access public
     * @param string $key
     * @param string $value
     * @return void
     */
    public static function setResponseHeader($key, $value)
    {
        self::$_headerParam[$key] = $value;
        return;
    }

    
}