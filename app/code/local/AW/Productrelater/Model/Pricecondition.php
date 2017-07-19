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
class AW_Productrelater_Model_Pricecondition {
    /** Code of price condition 'Any' */
    const PRICECONDITION_ITEM_ANY =         1;
    /** Code of price condition 'Equal or less than' */
    const PRICECONDITION_ITEM_LESSTHAN =    2;
    /** Code of price condition 'Less than' */
    const PRICECONDITION_ITEM_NOTLESSTHAN = 3;
    /** Code of price condition 'Equal or greater than' */
    const PRICECONDITION_ITEM_MORETHAN =    4;
    /** Code of price condition 'Greater than' */
    const PRICECONDITION_ITEM_NOTMORETHAN = 5;

    /**
     * Return option array which contain all of class constants
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => self::PRICECONDITION_ITEM_ANY, 'label' => Mage::helper('productrelater')->__('Any')),
            array('value' => self::PRICECONDITION_ITEM_NOTMORETHAN, 'label' => Mage::helper('productrelater')->__('Equal or less than')),
            array('value' => self::PRICECONDITION_ITEM_LESSTHAN, 'label' => Mage::helper('productrelater')->__('Less than')),
            array('value' => self::PRICECONDITION_ITEM_NOTLESSTHAN, 'label' => Mage::helper('productrelater')->__('Equal or greater than')),
            array('value' => self::PRICECONDITION_ITEM_MORETHAN, 'label' => Mage::helper('productrelater')->__('Greater than')),
        );
    }
}
