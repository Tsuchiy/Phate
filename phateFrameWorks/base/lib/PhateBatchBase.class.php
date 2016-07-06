<?php
namespace Phate;

/**
 * BatchBaseクラス
 *
 * バッチファイル作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
abstract class BatchBase
{
    abstract public function initialize();

    abstract public function execute();

}
