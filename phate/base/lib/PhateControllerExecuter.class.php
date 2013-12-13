<?php
/**
 * PhateControllerExecuterクラス
 *
 * Controllerを実行する手順について記載してあります。
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateControllerExecuter
{
    /**
     * コントローラを実行する側
     * 
     * @access public
     * @param PhateControllerBase $controllerClass
     * @return void
     */
    public static function execute(PhateControllerBase $controllerClass)
    {
        if (($controllerClass->initialize()) === false) {
            throw new PhateKillException();
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
