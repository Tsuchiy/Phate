<?php
/**
 * PhateThrownExceptionBaseクラス
 *
 * Exceptionが投げられた際ののプロジェクト別処理用クラスの継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
abstract class PhateThrownExceptionBase
{
    /**
     * 実処理
     * 
     * @abstract
     */
    abstract public function execute(Exception $e);
}
