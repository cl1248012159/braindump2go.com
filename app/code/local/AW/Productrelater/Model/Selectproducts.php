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
class AW_Productrelater_Model_Selectproducts {
    /** Code of select product condition 'Random' */
    const SELPROD_ITEM_RANDOM = 1;
    /** Code of select product condition 'Last added' */
    const SELPROD_ITEM_LAST =   2;
    /** Code of select product condition 'Lexically similar' */
    const SELPROD_ITEM_LEXICAL = 3;

    public function toOptionArray() {
        return array(
            array('value' => self::SELPROD_ITEM_RANDOM, 'label' => Mage::helper('productrelater')->__('Random')),
            array('value' => self::SELPROD_ITEM_LAST, 'label' => Mage::helper('productrelater')->__('Last added')),
            array('value' => self::SELPROD_ITEM_LEXICAL, 'label' => Mage::helper('productrelater')->__('Lexically similar (can slow down page loading on huge inventories)')),
        );
    }
}
