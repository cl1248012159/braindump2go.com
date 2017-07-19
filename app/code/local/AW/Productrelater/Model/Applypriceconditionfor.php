<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Productrelater
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */class AW_Productrelater_Model_Applypriceconditionfor {
    /** Code of price condition wich will be applied for regular price */
    const AW_ARP_REGULARPRICE = 1;
    /** Code of price condition wich will be applied for special price */
    const AW_ARP_SPECIALPRICE = 2;
    /** Code of price condition wich will be applied for final price */
    const AW_ARP_FINALPRICE =   3;

    /**
     * Return option array which contain all of class constants
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => self::AW_ARP_REGULARPRICE, 'label' => Mage::helper('productrelater')->__('Regular price')),
            array('value' => self::AW_ARP_SPECIALPRICE, 'label' => Mage::helper('productrelater')->__('Special price')),
            array('value' => self::AW_ARP_FINALPRICE, 'label' => Mage::helper('productrelater')->__('Final price'))
        );
    }
}
