<?php
class Idev_OneStepCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function setCustomerComment($observer)
    {
        $enable_comments = Mage::getStoreConfig('onestepcheckout/exclude_fields/enable_comments');

        if($enable_comments)	{
            $orderComment = $this->_getRequest()->getPost('onestepcheckout_comments');
            $orderComment = trim($orderComment);

            if ($orderComment != "")
            {
                $observer->getEvent()->getOrder()->setOnestepcheckoutCustomercomment($orderComment);
            }
        }
    }

    public function isRewriteCheckoutLinksEnabled()
    {
        return Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links');
    }

    /**
     * If we are using enterprise wersion or not
     * @return boolean
     */
    public function isEnterPrise(){
        return (str_replace('.', '', Mage::getVersion()) > 1600) ? true : false;
    }
}
