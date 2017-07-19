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
 */
class AW_Productrelater_Model_Sources {
    /** Code of source 'Store' */
    const SOURCE_TYPE_CURRENT_STORE	= 1;
    /** Code of source 'Current category' */
    const SOURCE_TYPE_CURRENT_CATEGORY = 2;

    /**
     * Return option array which contain all of class constants
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => self::SOURCE_TYPE_CURRENT_STORE, 'label' => Mage::helper('productrelater')->__('Current store')),
            array('value' => self::SOURCE_TYPE_CURRENT_CATEGORY, 'label' => Mage::helper('productrelater')->__('Current category')),
        );
    }
}
