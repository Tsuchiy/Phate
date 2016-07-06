<?php
namespace Phate;

/**
 * ControllerExecuterクラス
 *
 * Controllerを実行する手順について記載してあります。
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/11/13
 **/
class ControllerExecuter
{
    /**
     * コントローラを実行する側
     * 
     * @access public
     * @param ControllerBase $controllerClass
     * @return void
     */
    public static function execute(ControllerBase $controllerClass)
    {
        if (($controllerClass->initialize()) === false) {
            throw new KillException();
        }
        $validateResult = $controllerClass->validate();
        if (is_array($validateResult)) {
            $result = true;
            foreach ($validateResult as $line) {
                foreach ($line as $v) {
                    if ($v['result'] == false) {
                        $result = false;
                        break 2;
                    }
                }
            }
            if (!$result) {
                $controllerClass->validatorError($validateResult);
                return;
            }
        }
        $controllerClass->action();
        return;
    }
}
