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
class AW_Productrelater_Model_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection {
    /**
     * Selecting products from multiple categories
     * @param string $categories categories list separated by commas
     * @return AW_Productrelater_Model_Product_Collection
     */
    public function addCategoriesFilter($categories) {
        $alias = 'cat_index';
        $categoryCondition = $this->getConnection()->quoteInto(
            $alias.'.product_id=e.entity_id AND '.$alias.'.store_id=? AND ',
            $this->getStoreId());
        $categoryCondition.= $alias.'.category_id IN ('.$categories.')';
        $this->getSelect()->joinInner(
            array($alias => $this->getTable('catalog/category_product_index')),
                $categoryCondition,
                array('position'=>'position')
        );
        $this->_categoryIndexJoined = true;
        $this->_joinFields['position'] = array('table'=>$alias, 'field'=>'position' );
        $this->groupByAttribute('entity_id');
        return $this;
    }
}
