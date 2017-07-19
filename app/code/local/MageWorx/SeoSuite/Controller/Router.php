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
class MageWorx_SeoSuite_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard {

    public function initControllerRouters($observer) {
        $front = $observer->getEvent()->getFront();
        $router = new MageWorx_SeoSuite_Controller_Router();
        $front->addRouter('seosuite', $router);
    }

    public function match(Zend_Controller_Request_Http $request) {
        $this->_beforeModuleMatch();

        if ($this->_matchCategoryLayer($request)) {
            return true;
        }

        $identifier = trim($request->getPathInfo(), '/');

        $d = explode('/', $identifier);

        if (count($d) < 2) {
            return false;
        }

        if ('reviews' == $d[1]) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('url_key', $d[0]);
            if (!$product || !$product->getId()) {
                return false;
            }

            if (isset($d[2]) && $d[2] != 'category') {
                if (isset($d[3]) && is_numeric($d[3])) {
                    $reviewId = $d[3];
                }

                $request->setActionName('view')
                        ->setParam('id', $reviewId);
            } else {
                if (isset($d[3])) {
                    $category = Mage::getModel('seosuite/catalog_category')->loadByAttribute('url_key', $d[3]);
                    if ($category && $categoryId = $category->getId()) {
                        $request->setParam('category', $categoryId);
                    }
                }
                $request->setActionName('list')
                        ->setParam('id', $product->getId());
            }

            $request->setModuleName('review')
                    ->setControllerName('product')
                    ->setAlias(
                            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                            'reviews'
            );

            return true;
        }

        switch ($d[0]) {
            case 'tag':
                if (!isset($d[1])) {
                    return false;
                }
                if (count($d) > 2 || in_array($d[1], array('index', 'customer', 'list'))) {
                    return false;
                }
                $tag = Mage::getModel('tag/tag')->load(urldecode($d[1]), 'name');
                if (!$tag->getId()) {
                    return false;
                }
                $request->setModuleName('tag')->setControllerName('product')->setActionName('list')
                        ->setParam('tagId', $tag->getId());

                break;
            case 'rss':
                if (!isset($d[1]) || !isset($d[2])) {
                    return false;
                }
                if (count($d) > 4 || in_array($d[1], array('order'))) {
                    return false;
                }
                $storeId = Mage::app()->getStore($d[1])->getId();
                $t = null;
                if ($d[2]{0} == '@') {
                    $t = substr($d[2], 1);
                }
                switch ($t) {
                    case 'new':
                        $request->setActionName('new')
                                ->setParam('store_id', $storeId);
                        break;
                    case 'specials':
                        $request->setActionName('special')
                                ->setParam('cid', $d[3])
                                ->setParam('store_id', $storeId);
                        break;
                    case 'discounts':
                        $request->setActionName('salesrule')
                                ->setParam('cid', $d[3])
                                ->setParam('store_id', $storeId);
                        break;
                    default:
                        $category = Mage::getModel('seosuite/catalog_category')->setStoreId($storeId)->loadByAttribute('url_key', $d[2]);
                        if (!$category || !$category->getId()) {
                            return false;
                        }
                        $request->setActionName('category')
                                ->setParam('cid', $category->getId())
                                ->setParam('store_id', $storeId);
                }
                $request->setModuleName('rss')
                        ->setControllerName('catalog');
                break;
            default:
                return false;
        }

        $request->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                $identifier
        );
        return true;
    }

    protected function _matchCategoryLayer($request) {
        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        $identifier = trim(str_replace($suffix, '', $request->getPathInfo()), '/');
        $urlSplit = explode('/l/', $identifier, 2);
        if (!isset($urlSplit[1])) {
            return false;
        }
        Varien_Autoload::registerScope('catalog');
        $productUrl = Mage::getModel('catalog/product_url');
        list($cat, $params) = $urlSplit;
        $layerParams = explode('/', $params);
        $_params = array();

        $catPath = $cat . $suffix;
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $select = $connection->select()->from($tablePrefix . 'core_url_rewrite')->
                        where('request_path = ? AND store_id = ' . Mage::app()->getStore()->getId(), $catPath);
        $rewrite = $connection->fetchRow($select);
        
        $urlRewrite = Mage::getModel('core/url_rewrite')->load($rewrite['url_rewrite_id']);
        if ($urlRewrite->getId()) {
            $request->setPathInfo($catPath);
            $request->setModuleName('catalog')
                    ->setControllerName('category')
                    ->setActionName('view')
                    ->setParam('id', $urlRewrite->getCategoryId())
                    ->setAlias(
                            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                            'catalog'
            );

            if (count($layerParams)) {
                foreach ($layerParams as $params) {
                    $param = explode('-', $params, 2);
                    if (count($param) == 1 && !$request->getQuery('cat')) {
                        $cat = Mage::getModel('seosuite/catalog_category')
                                        ->setStoreId(Mage::app()->getStore()->getId())
                                        ->loadByAttribute('url_key', $productUrl->formatUrlKey($param[0]));
                        if (!$cat) {
                        	$name = str_replace('-', ' ', $productUrl->formatUrlKey($param[0]));
                        	$cat = Mage::getModel('seosuite/catalog_category')
                                        ->setStoreId(Mage::app()->getStore()->getId())
                                        ->loadByAttribute('name', $name);
                        }
                        if ($cat && $cat->getId()) {
                            $request->setQuery('cat', $cat->getName());
                            continue;
                        }
                    }
                    if (count($param) == 1) {
                        $_params[] = $param[0];
                    } else {
                        $request->setQuery($param[0], $param[1]);
                    }
                }
            }
            if (!empty($_params)) {
                Mage::register('_layer_params', $_params);
            }
            $urlRewrite->rewrite($request);
            return true;
        }
        return false;
    }

}