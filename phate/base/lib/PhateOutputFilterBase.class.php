<?php
/**
 * PhateOutputFilterBaseクラス
 *
 * OutputFilterを作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
abstract class PhateOutputFilterBase
{

    /**
     * フィルタの実行
     *
     * @abstract
     * @param mixed &$contents
     * @return void
     */
    abstract public function execute(&$contents);

}