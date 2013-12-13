<?php
/**
 * PhateFrameworkディスパッチャ
 *
 * @package PhateFramework 
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/

ini_set('display_errors', 0);
set_time_limit(0);

// アプリ名
define('PROJECT_NAME', 'sample');

// デバッグOnOff
$debug = true;
/*
 * コード開始
 */


try {
    // opcachecode対策
    if ($debug && function_exists('opcache_invalidate')) {
        opcache_invalidate();
    }
    // apc対策
    if ($debug && function_exists('apc_store')) {
        ini_set('apc.enabled', 0);
    }
    // Coreの読み込み
    include(realpath(dirname(__FILE__) . '/../../phate/base') . '/PhateCore.class.php');
    $instance = PhateCore::getInstance(PROJECT_NAME, $debug);
    $instance->dispatch();
} catch (Exception $e) {
    if ($debug) {
        var_dump($e);
    }
}
