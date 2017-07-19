<?php
class Atwix_WesternUnionPayment_Block_Form_Westernunionpayment extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('westernunionpayment/form/westernunionpayment.phtml');
    }

}
