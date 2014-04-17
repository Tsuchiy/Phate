<?php
define('PHATE_ROOT_DIR', realpath(dirname(__FILE__).'/../../') . DIRECTORY_SEPARATOR);
define('PHATE_CONFIG_DIR', PHATE_ROOT_DIR . 'config/');
define('PHATE_PROJECT_DIR', PHATE_ROOT_DIR . 'project/');

/**
 * scaffoldingProjectクラス
 *
 * projectのscaffolfolding機能実装クラス
 *
 * @package PhateFramework scaffolding
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class scaffoldingProject
{
    /**
     * scaffolding実行
     * 
     * @param string $name プロジェクト名
     */
    public function execute($name)
    {
        $scaffoldDir = PHATE_SCAFFOLD_DIR . 'project/';
        
        // put dispatcher
        $dir = PHATE_HTTPROOT_DIR . $name;
        mkdir($dir);
        chmod($dir, 0777);
        $dir .= DIRECTORY_SEPARATOR;
        mkdir($dir . 'css');
        chmod($dir . 'css', 0777);
        mkdir($dir . 'img');
        chmod($dir . 'img', 0777);
        mkdir($dir . 'js');
        chmod($dir . 'js', 0777);
        copy($scaffoldDir . 'htdocs/.htaccess', $dir . '.htaccess');
        $str = file_get_contents($scaffoldDir . 'htdocs/index.php');
        $str = str_replace('%%project_name%%', $name, $str);
        file_put_contents($dir . 'index.php', $str);

        // put main config 
        copy ($scaffoldDir . 'config/mainConfig.yml', PHATE_CONFIG_DIR . $name . '.yml');

        // make project directory
        $dir = PHATE_PROJECT_DIR . $name;
        mkdir($dir);
        $dir .= DIRECTORY_SEPARATOR;
        mkdir($dir . 'batches');
        copy($scaffoldDir . 'project/batches/CommonBatch.class.php', $dir . 'batches/CommonBatch.class.php');
        mkdir($dir . 'controllers');
        copy($scaffoldDir . 'project/controllers/CommonController.class.php', $dir . 'controllers/CommonController.class.php');
        mkdir($dir . 'controllers/index');
        copy($scaffoldDir . 'project/controllers/index/IndexController.class.php', $dir . 'controllers/index/IndexController.class.php');
        mkdir($dir . 'data');
        chmod($dir . 'data', 0777);
        mkdir($dir . 'filters');
        copy($scaffoldDir . 'project/filters/MaintenanceInputFilter.class.php', $dir . 'filters/MaintenanceInputFilter.class.php');
        mkdir($dir . 'maintenance');
        copy($scaffoldDir . 'project/maintenance/toRename.yml', $dir . 'maintenance/toRename.yml');
        mkdir($dir . 'models');
        mkdir($dir . 'exception');
        copy($scaffoldDir . 'project/exception/ThrownException.class.php', $dir . 'exception/ThrownException.class.php');
        mkdir($dir . 'views');
        
        // make testing directory
        $dir = PHATE_ROOT_DIR . 'test/project/' . $name;
        mkdir($dir);
        $dir .= DIRECTORY_SEPARATOR;
        copy($scaffoldDir . 'test/CommonTest.php', $dir . 'CommonTest.php');
        mkdir($dir . 'controllers');
    }
}
