<?php
/**
 * PhateControllerBaseクラス
 *
 * コントローラファイル作る際の継承元クラス
 *
 * @package PhateFramework
 * @abstract
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
abstract class PhateControllerBase
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
