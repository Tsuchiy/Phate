<?php
/**
 * PhateVendorAutoLoaderクラス
 *
 * 他社提供ライブラリを無理やりオートロードするためのクラス
 * 最初にspl_registerに登録してもらう
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateVendorAutoLoader
{
    /**
     * オートローダー
     * 
     * @param type $className
     */
    public static function loader($className)
    {
        // OAuth
        $oAuthClasses = array(
            'OAuthException',
            'OAuthConsumer',
            'OAuthToken',
            'OAuthSignatureMethod',
            'OAuthSignatureMethod_HMAC_SHA1',
            'OAuthSignatureMethod_PLAINTEXT',
            'OAuthSignatureMethod_RSA_SHA1',
            'OAuthRequest',
            'OAuthServer',
            'OAuthDataStore',
            'OAuthUtil',
        );
        if (in_array($className, $oAuthClasses)) {
            include_once PHATE_LIB_VENDOR_DIR . 'OAuth.php';
        }
        // HTML_Emoji関連
        if ($className === 'HTML_Emoji') {
            include_once PHATE_LIB_VENDOR_DIR . 'HTML/Emoji.php';
        }
        
    }
}
