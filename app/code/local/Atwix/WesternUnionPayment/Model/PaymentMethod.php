<?php
class Atwix_WesternUnionPayment_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {
    /**
     * Prevent any rendering
     *
     * @return string
     */
    protected $_code  = 'westernunionpayment';
    protected $_formBlockType = 'westernunionpayment/form_westernunionpayment';
    protected $_infoBlockType = 'westernunionpayment/info_westernunionpayment';


    public function assignData($data)
    {
        $details = array();
        if ($this->getPayableTo()) {
            $details['payable_to'] = $this->getPayableTo();
        }
        
		if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }
}
