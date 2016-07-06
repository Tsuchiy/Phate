<?php
namespace Phate;
/**
 * PhateFrameworkディスパッチャ
 *
 * @package PhateFramework 
 * @access  public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/

// アプリ名
define('PROJECT_NAME', '%%projectName%%');

if (getenv('DEBUG_MODE')){
    // デバッグOnOff
    ini_set('display_errors', 1);
    set_time_limit(30);
    $debug = (bool)getenv('DEBUG_MODE');
} else {
    ini_set('display_errors', 0);
    set_time_limit(0);
    $debug = false;
}

/*
 * コード開始
 */


try {
    // opcachecode対策
    if ($debug && function_exists('opcache_invalidate')) {
        opcache_invalidate(realpath(dirname(__FILE__) . '/../..') . '/project/%%project_name%%');
    }
    // apc対策
    if ($debug && function_exists('apc_store')) {
        ini_set('apc.enabled', 0);
    }
    // Coreの読み込み
    include(realpath(dirname(__FILE__) . '/../../phateFrameWorks/base') . '/PhateCore.class.php');
    $instance = Core::getInstance(PROJECT_NAME, $debug);
    $instance->dispatch();
} catch (Exception $e) {
    if ($debug) {
        var_dump($e);
    }
}
