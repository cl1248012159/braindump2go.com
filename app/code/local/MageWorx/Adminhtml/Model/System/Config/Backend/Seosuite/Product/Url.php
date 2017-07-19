<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @copyright  Copyright (c) 2010 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * SEO Suite extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Adminhtml_Model_System_Config_Backend_Seosuite_Product_Url extends Mage_Core_Model_Config_Data
{
    private $_product;

    protected function _beforeSave() {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();

        $select = $connection->select()->from($tablePrefix.'eav_entity_type')->where("entity_type_code = 'catalog_product'");
        $productTypeId = $connection->fetchOne($select);

        $select = $connection->select()->from($tablePrefix.'eav_attribute')->where("entity_type_id = $productTypeId AND (attribute_code = 'url_path')");
        $urlPathId = $connection->fetchOne($select);
        $select = $connection->select()->from($tablePrefix.'eav_attribute')->where("entity_type_id = $productTypeId AND (attribute_code = 'url_key')");
        $urlKeyId = $connection->fetchOne($select);

        $select = $connection->select()->from($tablePrefix.'catalog_product_entity');
        $products = $connection->fetchAll($select);

        $stores = Mage::getModel('core/store')->getCollection()->load()->getAllIds();
        array_unshift($stores, 0);
        $template = Mage::getModel('seosuite/catalog_product_template_url');
        foreach ($products as $_product) {
            foreach ($stores as $storeId){
                $this->_product = Mage::getSingleton('catalog/product')->setStoreId($storeId)->load($_product['entity_id']);
                if ($this->_product){
                    $urlKeyTemplate = (string) Mage::getStoreConfig('mageworx_seo/seosuite/product_url_key', $storeId);

                    $template->setTemplate($urlKeyTemplate)
                        ->setProduct($this->_product);

                    $urlKey = $template->process();

                    if ($urlKey == '') {
                        $urlKey = $this->_product->getName();
                    }

                    $urlKey = $this->_product->formatUrlKey($urlKey);

                    $urlSuffix = Mage::getStoreConfig('catalog/seo/product_url_suffix', $storeId);

                    $connection->update($tablePrefix.'catalog_product_entity_varchar', array('value' => $urlKey), "entity_type_id = $productTypeId AND attribute_id = $urlKeyId AND entity_id = {$this->_product->getId()} AND store_id = {$storeId}");
                    $connection->update($tablePrefix.'catalog_product_entity_varchar', array('value' => $urlKey . $urlSuffix), "entity_type_id = $productTypeId AND attribute_id = $urlPathId AND entity_id = {$this->_product->getId()} AND store_id = {$storeId}");
                }
            }
        }

        Mage::getModel('catalog/url')->refreshRewrites();
        /*Mage::getSingleton('index/indexer')->processEntityAction(
            $this, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );*/

        return $this;
    }
}