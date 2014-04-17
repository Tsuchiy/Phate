<?php
/**
 * PhateTimerクラス
 *
 * 実行開始時刻の記録・取得と、時間に対する各メソッド群
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/08/01
 **/
class PhateTimer
{
    private static $_now;
    private static $_timezone;
    private static $_applicationResetTime;
    
    const DEFAULT_TIMEZONE = 'Asia/Tokyo';
    const DEFAULT_RESET_TIME = '00:00:00';
    
    /**
     * 初期化
     * 
     * @access public
     * @param void
     * @return void
     */
    public static function init()
    {
        self::$_now = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        $sysConfig = PhateCore::getConfigure();
        self::$_timezone = isset($sysConfig['TIMER']['TIMEZONE']) ? $sysConfig['TIMER']['TIMEZONE'] : self::DEFAULT_TIMEZONE;
        self::$_applicationResetTime = isset($sysConfig['TIMER']['APPLICATION_RESET_TIME']) ? $sysConfig['TIMER']['APPLICATION_RESET_TIME'] : self::DEFAULT_RESET_TIME;
        ini_set('date.timezone', self::$_timezone);
    }

    /**
     * TimeZone設定済みDateTimeクラスを取得する
     * 
     * @access private
     * @param integer UnixTimeStamp(省略は生成時刻)
     * @return DateTime
     */
    private static function getDateTimeClass($timestamp = NULL)
    {
        $timestamp = is_null($timestamp) ? self::$_now : $timestamp;
        $datetimeClass = new DateTime();
        $datetimeClass->setTimeZone(new DateTimeZone(self::$_timezone));
        $datetimeClass->setTimeStamp($timestamp);
        return $datetimeClass;
    }
    
    /**
     * TimeZone設定済みDateTimeクラスを取得する
     * 
     * @access private
     * @param string $string "Y-m-d H:i:s"型
     * @return DateTime
     */
    private static function getDateTimeClassByString($string = NULL)
    {
        if (is_null($string)) {
            return self::getDateTimeClass();
        }
        $datetimeClass = new DateTime();
        $datetimeClass->setTimeZone(new DateTimeZone(self::$_timezone));
        $arr = array();
        preg_match('/^([0-9]+)\-([0-9]+)\-([0-9]+)\s([0-9]+):([0-9]+):([0-9]+)$/', $string, $arr);
        $datetimeClass->setDate($arr[1], $arr[2], $arr[3]);
        $datetimeClass->setTime($arr[4], $arr[5], $arr[6]);
        return $datetimeClass;
    }
    
    /**
     * 生成時のUnixTimeStampを得る
     * 
     * @access public
     * @param string $dateString "Y-m-d H:i:s"型(省略は生成時刻)
     * @return integer
     */
    public static function getTimeStamp($dateString = NULL)
    {
        if (is_null($dateString)) {
            return self::$_now;
        }
        $datetimeClass = self::getDateTimeClassByString($dateString);
        return $datetimeClass->getTimestamp();
        
    }

    /**
     * フォーマットされた日時を得る
     * 
     * @access public
     * @param integer $timestamp UnixTimeStamp(省略は生成時刻)
     * @return string
     */
    public static function getDateTime($timestamp = NULL)
    {
        $datetimeClass = self::getDateTimeClass($timestamp);
        return $datetimeClass->format('Y-m-d H:i:s');
    }
    
    /**
     * フォーマットされた時刻を得る
     * 
     * @access public
     * @param integer $timestamp UnixTimeStamp(省略は生成時刻)
     * @return string
     */
    public static function getTime($timestamp = NULL)
    {
        $datetimeClass = self::getDateTimeClass($timestamp);
        return $datetimeClass->format('H:i:s');
    }
    
    
    /**
     * フォーマットされた日を得る
     * 
     * @access public
     * @param integer $timestamp UnixTimeStamp(省略は生成時刻)
     * @return string
     */
    public static function getDate($timestamp = NULL)
    {
        $datetimeClass = self::getDateTimeClass($timestamp);
        return $datetimeClass->format('Y-m-d');
    }

    /**
     * 曜日を得る
     * 
     * @access public
     * @param integer $timestamp UnixTimeStamp(省略は生成時刻)
     * @return string 0(Sunday)-6(Saturday)
     */
    public static function getWeekDate($timestamp = NULL)
    {
        $datetimeClass = self::getDateTimeClass($timestamp);
        return $datetimeClass->format('w');
    }

    /**
     * DateTimeフォーマットに従った文字列を返す
     * 
     * @access public
     * 
     * @param string $format
     * @param int $timestamp
     * @return string
     */
    public static function format($format, $timestamp = NULL)
    {
        $datetimeClass = self::getDateTimeClass($timestamp);
        return $datetimeClass->format($format);
    }

    
    /**
     * アプリ内リセット時間を考慮したフォーマットされた日を得る
     * 
     * @access public
     * @param integer UnixTimeStamp(省略は生成時)
     * @return string
     */
    public static function getApplicationDate($timestamp = NULL)
    {
        $datetimeClass = self::getDateTimeClass($timestamp);
        if ($datetimeClass->format('H:i:s') < self::$_applicationResetTime) {
            $datetimeClass->add(new DateInterval('P-1D'));
        }
        return $datetimeClass->format('Y-m-d');
    }
    
    /**
     * String形式の日付の間隔を取得する
     * 
     * @access public
     * @param string $toTimeString 目的の"Y-m-d H:i:s"型
     * @param string $fromTimeString "Y-m-d H:i:s"型(省略は生成時刻)
     * @return array  ('day','hour,'minute','second')
     */
    public static function getDateTimeDiff($toTimeString, $fromTimeString = NULL)
    {
        $fromDateTimeClass = self::getDateTimeClassByString($fromTimeString);
        $toDateTimeClass = self::getDateTimeClassByString($toTimeString);
        $dateInterval = $fromDateTimeClass->diff($toDateTimeClass);
        $rtn['day'] = $dateInterval->format('%a');
        $rtn['hour'] = $dateInterval->format('%h');
        $rtn['minute'] = $dateInterval->format('%i');
        $rtn['second'] = $dateInterval->format('%s');
        return $rtn;
    }
    
    /**
     * String形式の日付の間隔を秒単位で取得する
     * 
     * @access public
     * @param string $toTimeString 目的の"Y-m-d H:i:s"型
     * @param string $fromTimeString "Y-m-d H:i:s"型(省略は生成時刻)
     * @return int
     */
    public static function getDateTimeDiffSecond($toTimeString, $fromTimeString = NULL)
    {
        $arr = self::getDateTimeDiff($toTimeString, $fromTimeString);
        return  ($arr['day'] * 24 * 60 * 60) +
                ($arr['hour'] * 60 * 60) +
                ($arr['minute'] * 60) +
                ($arr['second']);
    }
}
