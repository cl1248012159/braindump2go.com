<?php
class Atwix_WesternUnionPayment_Block_Info_Westernunionpayment extends Mage_Payment_Block_Info
{

    protected $_payableTo;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('westernunionpayment/info/westernunionpayment.phtml');
    }

    public function getPayableTo()
    {
        if (is_null($this->_payableTo)) {
            $this->_convertAdditionalData();
        }
        return $this->_payableTo;
    }

    protected function _convertAdditionalData()
    {
        $details = @unserialize($this->getInfo()->getAdditionalData());
        if (is_array($details)) {
            $this->_payableTo = isset($details['payable_to']) ? (string) $details['payable_to'] : '';
        } else {
            $this->_payableTo = '';
        }
        return $this;
    }
}
