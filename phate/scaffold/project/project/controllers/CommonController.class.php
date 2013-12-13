<?php
define('PHATE_PROJECT_CONTROLLERS_DIR', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('PHATE_PROJECT_MODELS_DIR', realpath(dirname(__FILE__).'/../models/') . DIRECTORY_SEPARATOR);
define('PHATE_PROJECT_VIEWS_DIR', realpath(dirname(__FILE__).'/../views/') . DIRECTORY_SEPARATOR);

/**
 * コントローラ基底クラス
 *
 * @package PhateFramework 
 * @access  public
 **/
class CommonController extends PhateControllerBase
{
    
    public function initialize()
    {
        return true;
    }
    public function action()
    {
        return true;
    }

    public function validate()
    {
        return true;
    }

    public function validatorError($resultArray)
    {
        throw new PhateCommonException('Validator Error');
    }
}
