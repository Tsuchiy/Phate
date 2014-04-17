<?php
/**
 * PhateCsvRendererクラス
 *
 * csvとして出力するレンダラ
 *
 * @package PhateFramework
 * @access  public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/03/01
 **/
class PhateCsvRenderer
{
    
    private $_columnNames = array();
    
    public function __construct()
    {
    }

    public function setColumnNames(array $columnNameArray)
    {
        $this->_columnNames = $columnNameArray;
    }
    
    /**
     * 描画
     * 
     * @param mixed $value
     */
    public function render(array $listArray, $filename = null)
    {
        if (is_null($filename)) {
            $filename = str_replace(' ', '_', PhateTimer::getDateTime());
        }
        if (!preg_match('/^.*\.csv$/',$filename)) {
            $filename .= '.csv';
        }
        PhateHttpResponseHeader::setContentType('text/csv');
        PhateHttpResponseHeader::setResponseHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        ob_start();
        $fp = fopen('php://output', 'w');
        if ($this->_columnNames) {
            fputcsv($fp, $this->_columnNames);
        }
        foreach ($listArray as $row) {
            fputcsv($fp, $row);
        }
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }
}
