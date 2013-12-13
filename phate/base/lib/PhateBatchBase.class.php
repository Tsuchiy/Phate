<?php
/**
 * PhateBatchBaseクラス
 *
 * バッチファイル作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
abstract class PhateBatchBase
{
    abstract public function initialize();

    abstract public function execute();

}
