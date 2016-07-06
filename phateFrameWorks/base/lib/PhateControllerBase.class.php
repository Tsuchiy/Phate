<?php
namespace Phate;
/**
 * ControllerBaseクラス
 *
 * コントローラファイル作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
abstract class ControllerBase
{
    /**
     * 一番初めに呼ばれる、メソッド
     *
     * @abstract
     * @return boolean falseを返すと、そこで処理が止まります。
     */
    abstract public function initialize();

    /**
     * 実処理
     * 
     * @abstract
     * @see validate()
     */
    abstract public function action();

    /**
     * バリデートする
     *
     * @abstract
     * @return true/false boolean
     */
    abstract public function validate();

    /**
     * validate()でfalseが返った場合の処理。
     *
     * @abstract
     * @see validate()
     */
    abstract public function validatorError($resultArray);
}
