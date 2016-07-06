<?php
namespace Phate;
/**
 * BeforeFilterBaseクラス
 *
 * BeforeFilterを作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
abstract class BeforeFilterBase
{

    /**
     * フィルタの実行
     *
     * @abstract
     * @return void
     */
    abstract public function execute();

}
