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
class AW_Productrelater_Helper_Data extends Mage_Core_Helper_Abstract	{
    /** Magento 1.3.* version code */
    const MAGENTO_VERSION_CE_1_3 = 'CE13';
    /** Magento 1.4.* version code */
    const MAGENTO_VERSION_CE_1_4 = 'CE14';
    /** Magento 1.8.* version code */
    const MAGENTO_VERSION_EE_1_8 = 'EE18';
    /** Magento 1.9.* version code */
    const MAGENTO_VERSION_EE_1_9 = 'EE19';

    /**
     * Returns Magento version code
     * @return string
     */
    public function getMagentoVersionCode() {
        if(preg_match('|1\.9.*|',Mage::getVersion())) return self::MAGENTO_VERSION_EE_1_9;
        if(preg_match('|1\.8.*|',Mage::getVersion())) return self::MAGENTO_VERSION_EE_1_8;
        if(preg_match('|1\.4.*|',Mage::getVersion())) return self::MAGENTO_VERSION_CE_1_4;

        return self::MAGENTO_VERSION_CE_1_3;
    }
}
