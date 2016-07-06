<?php
namespace Phate;
/**
 * PhateAfterFilterBaseクラス
 *
 * AfterFilterを作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
abstract class PhateAfterFilterBase
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
