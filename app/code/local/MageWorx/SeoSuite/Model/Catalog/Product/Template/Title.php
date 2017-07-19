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

class MageWorx_SeoSuite_Model_Catalog_Product_Template_Title extends MageWorx_SeoSuite_Model_Catalog_Product_Template_Abstract
{
    protected $_useDefault = array();
    protected $_defaultProduct = null;

    public function process()
    {
        if (!$this->_product instanceof Mage_Catalog_Model_Product){
            return;
        }

        try {
            $string = $this->__compile($this->getTemplate());
        } catch (Exception $e){}

        return $string;
    }

    protected function __compile($template)
    {
        $vars = $this->__parse($template);
        foreach ($vars as $key => $params) {
            foreach ($params['attributes'] as $n => $attribute) {
                $value = '';
                switch ($attribute) {
                    case 'category':
                        $category = $this->_product->getCategory();
                        if ($category) {
                            $value = $this->_product->getCategory()->getName();
                        } else {
                            $categoryItems = $this->_product->getCategoryCollection()->load()->getIterator();
                            $category = current($categoryItems);
                            $category = Mage::getModel('catalog/category')->load($category->getId());
                            $value = $category->getName();
                        }
                        break;
                    case 'categories':
                        $separator = (string)Mage::getStoreConfig('catalog/seo/title_separator');
                        $separator = ' ' . $separator . ' ';
                        $title = array();
                        $path  = Mage::helper('catalog')->getBreadcrumbPath();
                        foreach ($path as $name => $breadcrumb) {
                            $title[] = $breadcrumb['label'];
                        }
                        array_pop($title);
                        $value = join($separator, array_reverse($title));
                        break;
                    case 'store_view_name':
                        $value = Mage::app()->getStore($this->_product->getStoreId())->getName();
                        break;
                    case 'store_name':
                        $value = Mage::app()->getStore($this->_product->getStoreId())->getGroup()->getName();
                        break;
                    case 'website_name':
                        $value = Mage::app()->getStore($this->_product->getStoreId())->getWebsite()->getName();
                        break;
                    case 'price':
                        $value = Mage::app()->getStore()->convertPrice($this->_product->getPrice(), true, false);
                        break;
                    case 'special_price':
                        $value = Mage::app()->getStore()->formatPrice($this->_product->getData($attribute), false);
                        break;
                    default:
                        if ($_attr = $this->_product->getResource()->getAttribute($attribute)) {
                            $value = $_attr->getSource()->getOptionText($this->_product->getData($attribute));
                        }
                        if (!$value) {
                            $value = $this->_product->getData($attribute);
                        }
                }
                if ($value) {
                    $value = $params['prefix'] . $value . $params['suffix'];
                    break;
                }
            }
            $template = str_replace($key, $value, $template);
        }
        return $template;
    }
}