<?php
namespace Phate;

/**
 * ThrownExceptionBaseクラス
 *
 * Exceptionが投げられた際ののプロジェクト別処理用クラスの継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
abstract class ThrownExceptionBase
{
    /**
     * 実処理
     * 
     * @abstract
     */
    abstract public function execute(\Exception $e);
}
