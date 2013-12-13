<?php
class CheckController extends CommonController
{
    public function action()
    {
        $loginBonusData = LoginBonusManager::add();

        $rtn = array(
            'loginBonusData' => $loginBonusData,
        );

        $this->_renderer->render($rtn);
    }
    
    
    
}