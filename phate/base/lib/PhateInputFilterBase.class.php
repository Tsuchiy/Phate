<?php
/**
 * PhateInputFilterBaseクラス
 *
 * InputFilterを作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
abstract class PhateInputFilterBase
{

    /**
     * フィルタの実行
     *
     * @abstract
     * @return void
     */
    abstract public function execute();

}