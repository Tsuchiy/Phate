<?php
/**
 * PhateMbgaPaymentEntryObjectクラス
 *
 * PhateMbgaのPaymentAPIを利用する際に必要なオブジェクトクラス
 * 
 * @package PhateFramework
 * @access public
 * @author  Nobuo Tsuchiya <develop@m.tsuchi99.net>
 * @create  2013/10/15
 **/

class PhateMbgaPaymentEntryObject
{
    private $_itemId = '';
    private $_name = '';
    private $_unitPrice = 0;
    private $_amount = 0;
    private $_imageUrl = '';
    private $_description = '';

    /**
     * getter
     */

    
    public function getItemId()
    {
        return $this->_itemId;
    }
    public function getName()
    {
        return $this->_name;
    }
    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }
    public function getAmount()
    {
        return $this->_amount;
    }
    public function getImageUrl()
    {
        return $this->_imageUrl;
    }
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * setter
     */
    
    public function setItemId($value)
    {
        $this->_itemId = $value;
    }
    public function setName($value)
    {
        $this->_name = $value;
    }
    public function setUnitPrice($value)
    {
        $this->_unitPrice = $value;
    }
    public function setAmount($value)
    {
        $this->_amount = $value;
    }
    public function setImageUrl($value)
    {
        $this->_imageUrl = $value;
    }
    public function setDescription($value)
    {
        $this->_description = $value;
    }
}
