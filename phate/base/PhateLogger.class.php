<?php
/**
 * PhateLoggerクラス
 *
 * Logに記録するクラス。記録レベルや対象ファイルは設定ファイルにて設定されます。
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateLogger
{
    
    const LEVEL_DEBUG = 1;
    const LEVEL_INFO = 2;
    const LEVEL_WARNING = 4;
    const LEVEL_ERROR = 8;
    const LEVEL_CRITICAL = 16;
    const LEVEL_FATAL = 128;
    
    const DEFAULT_PREFIX = '%s [%s] ';
    
    private static $_config;
    
    /**
     * ロガーの初期化
     * 
     * @access public
     * @param void
     * @return void
     */
    public static function init()
    {
        $sysConf = PhateCore::getConfigure();
        if (!isset($sysConf['LOGGER']['load_yaml_file'])) {
            throw new PhateCommonException('no logger configure');
        }
        $filename = PHATE_CONFIG_DIR . $sysConf['LOGGER']['load_yaml_file'];
        self::$_config = PhateCommon::parseConfigYaml($filename);
    }
    
    /**
     * debugレベルログ出力
     * 
     * @access public
     * @param string
     * @return void
     */
    public static function debug($string)
    {
        $name = strtoupper(__FUNCTION__);
        $loggingLevel = PhateCore::isDebug() ? self::$_config['debug_logging_level'] : self::$_config['normal_logging_level'];
        if (!(self::LEVEL_DEBUG & $loggingLevel)) {
            return false;
        }
        $outputPath = self::$_config[$name]['log_file_path'];
        $outputFilename = self::$_config[$name]['log_file_name'];
        $message  = sprintf(self::DEFAULT_PREFIX, PhateTimer::getDateTime(), $name);
        $message .= $string . "\n";
        error_log($message, 3, $outputPath . $outputFilename);
        if (substr(sprintf('%o', fileperms($outputPath . $outputFilename)), -4) !=='0666') {
            chmod($outputPath . $outputFilename, 0666);
        }
        return true;
    }

    /**
     * infoレベルログ出力
     * 
     * @access public
     * @param string
     * @return void
     */
    public static function info($string)
    {
        $name = strtoupper(__FUNCTION__);
        $loggingLevel = PhateCore::isDebug() ? self::$_config['debug_logging_level'] : self::$_config['normal_logging_level'];
        if (!(self::LEVEL_INFO & $loggingLevel)) {
            return false;
        }
        $outputPath = self::$_config[$name]['log_file_path'];
        $outputFilename = self::$_config[$name]['log_file_name'];
        $message  = sprintf(self::DEFAULT_PREFIX, PhateTimer::getDateTime(), $name);
        $message .= $string . "\n";
        error_log($message, 3, $outputPath . $outputFilename);
        if (substr(sprintf('%o', fileperms($outputPath . $outputFilename)), -4) !=='0666') {
            chmod($outputPath . $outputFilename, 0666);
        }
        return true;
    }

    /**
     * warningレベルログ出力
     * 
     * @access public
     * @param string
     * @return void
     */
    public static function warning($string)
    {
        $name = strtoupper(__FUNCTION__);
        $loggingLevel = PhateCore::isDebug() ? self::$_config['debug_logging_level'] : self::$_config['normal_logging_level'];
        if (!(self::LEVEL_WARNING & $loggingLevel)) {
            return false;
        }
        $outputPath = self::$_config[$name]['log_file_path'];
        $outputFilename = self::$_config[$name]['log_file_name'];
        $message  = sprintf(self::DEFAULT_PREFIX, PhateTimer::getDateTime(), $name);
        $message .= $string . "\n";
        error_log($message, 3, $outputPath . $outputFilename);
        if (substr(sprintf('%o', fileperms($outputPath . $outputFilename)), -4) !=='0666') {
            chmod($outputPath . $outputFilename, 0666);
        }
        return true;
    }
    
    /**
     * errorレベルログ出力
     * 
     * @access public
     * @param string
     * @return void
     */
    public static function error($string)
    {
        $name = strtoupper(__FUNCTION__);
        $loggingLevel = PhateCore::isDebug() ? self::$_config['debug_logging_level'] : self::$_config['normal_logging_level'];
        if (!(self::LEVEL_ERROR & $loggingLevel)) {
            return false;
        }
        $outputPath = self::$_config[$name]['log_file_path'];
        $outputFilename = self::$_config[$name]['log_file_name'];
        $message  = sprintf(self::DEFAULT_PREFIX, PhateTimer::getDateTime(), $name);
        $message .= $string . "\n";
        error_log($message, 3, $outputPath . $outputFilename);
        if (substr(sprintf('%o', fileperms($outputPath . $outputFilename)), -4) !=='0666') {
            chmod($outputPath . $outputFilename, 0666);
        }
        return true;
    }
    
    /**
     * criticalレベルログ出力
     * 
     * @access public
     * @param string
     * @return void
     */
    public static function critical($string)
    {
        $name = strtoupper(__FUNCTION__);
        $loggingLevel = PhateCore::isDebug() ? self::$_config['debug_logging_level'] : self::$_config['normal_logging_level'];
        if (!(self::LEVEL_CRITICAL & $loggingLevel)) {
            return false;
        }
        $outputPath = self::$_config[$name]['log_file_path'];
        $outputFilename = self::$_config[$name]['log_file_name'];
        $message  = sprintf(self::DEFAULT_PREFIX, PhateTimer::getDateTime(), $name);
        $message .= $string . "\n";
        error_log($message, 3, $outputPath . $outputFilename);
        if (substr(sprintf('%o', fileperms($outputPath . $outputFilename)), -4) !=='0666') {
            chmod($outputPath . $outputFilename, 0666);
        }
        return true;
    }
    
    /**
     * fatalレベルログ出力
     * 
     * @access public
     * @param string
     * @return void
     */
    public static function fatal($errno, $errstr, $errfile, $errline)
    {
        $name = strtoupper(__FUNCTION__);
        $outputPath = self::$_config[$name]['log_file_path'];
        $outputFilename = self::$_config[$name]['log_file_name'];
        $message  = sprintf(self::DEFAULT_PREFIX, PhateTimer::getDateTime(), $name);
        $message .= "error_no:" . $errno . " " . $errstr ." ";
        $message .= "(" . $errfile ." , line:" . $errline . ")\n";
        
        error_log($message, 3, $outputPath . $outputFilename);
        if (substr(sprintf('%o', fileperms($outputPath . $outputFilename)), -4) !=='0666') {
            chmod($outputPath . $outputFilename, 0666);
        }
        return true;
    }

    /**
     * fluentd拡張
     * 
     * @access public
     * @param string
     * @return void
     */
    public static function fluentdPost($tag, array $data)
    {
        if (!class_exists('PhateFluentd')) {
            return false;
        }
        PhateFluentd::post($tag, $data);
    }
    
    /**
     * カスタムログ出力(マジックメソッド)
     * 適宜の名前のログ出力を行う
     *  
     * @param string $name
     * @param array $arguments
     * @throws PhateCommonException
     */
    public static function __callStatic($name, $arguments)
    {
        $name = strtoupper($name);
        if (!isset(self::$_config[$name]['log_file_path'])) {
            return false;
        }
        $outputPath = self::$_config[$name]['log_file_path'];
        $message = array_shift($arguments) . "\n";
        if (($filename = array_shift($arguments))) {
            $outputFilename = $filename;
        } else {
            if (!isset(self::$_config[$name]['log_file_name'])) {
                return false;
            }
            $outputFilename = self::$_config[$name]['log_file_name'];
        }
        
        error_log($message, 3, $outputPath . $outputFilename);
        if (substr(sprintf('%o', fileperms($outputPath . $outputFilename)), -4) !=='0666') {
            chmod($outputPath . $outputFilename, 0666);
        }
        return true;
    }
}