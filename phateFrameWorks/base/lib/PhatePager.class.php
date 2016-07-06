<?php
namespace Phate;

/**
 * Pagerクラス
 *
 * ページャ処理クラス
 *
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <n_develop@m.tsuchi99.net>
 * @create  2014/01/01
 **/
class Pager
{
    protected $_items;
    protected $_pageSize = 10;
    protected $_nowPage = 1;
    
    /**
     * コンストラクタ
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->_items = $items;
    }
    
    /**
     * 1ページあたりの件数を設定する
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        if (!is_numeric($pageSize) || ($pageSize < 1)){
            throw new CommonException('illegal page size');
        }
        $this->_pageSize = $pageSize;
    }
    
    /**
     * 最初のページ番号を取得する
     * @return int
     */
    public function getFirstPage()
    {
        return count($this->_items) ? 1 : 0;
    }
    
    /**
     * 最後のページ数を取得する
     * @return int
     */
    public function getLastPage()
    {
        return count($this->_items) ? ceil(count($this->_items) / $this->_pageSize) : 0;
    }
    
    /**
     * 現在のページのデータを抽出する
     * @param int $pageNo
     * @return array
     */
    public function getPageData($pageNo = null)
    {
        if (is_null($pageNo)) {
            $pageNo = $this->_nowPage;
        } else {
            $this->_nowPage = $pageNo;
        }
        
        $rtn = [];
        if (count($this->_items) == 0) {
            return $rtn;
        }
        $i = 0;
        foreach ($this->_items as $key => $value) {
            ++$i;
            if (ceil($i / $this->_pageSize) == $pageNo) {
                $rtn[$key] = $value;
            } elseif (ceil($i / $this->_pageSize) > $pageNo) {
                break;
            }
        }
        return $rtn;
    }
    
    /**
     * 現在のページ番号
     * @param int $pageNo
     */
    public function setNowPage($pageNo)
    {
        if (!is_numeric($pageNo) || ($pageNo < $this->getFirstPage())){
            throw new CommonException('illegal page number');
        }
        if ($pageNo > $this->getLastPage()) {
            $pageNo = $this->getLastPage();
        }
        $this->_nowPage = $pageNo;
    }

    /**
     * 現在のページ番号を取得する
     * @return int
     */
    public function getNowPage()
    {
        return count($this->_items) ? $this->_nowPage : 0;
    }

    /**
     * 現在の次のページ番号を取得する
     * @return int
     */
    public function getNextPage()
    {
        if (($this->getNowPage() + 1) > $this->getLastPage()) {
            return 0;
        }
        return $this->getNowPage() + 1;
    }
    
    /**
     * 現在の前のページ番号を取得する
     * @return int
     */
    public function getPrevPage()
    {
        if ($this->getNowPage() <= 1) {
            return 0;
        }
        return $this->getNowPage() - 1;
    }
    
    /**
     * 総アイテム数を返す
     * @return int
     */
    public function getAllCount()
    {
        return count($this->_items);
    }
    
}
