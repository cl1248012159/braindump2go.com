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

class MageWorx_SeoSuite_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getRssGenerator()
    {
        return base64_decode('TWFnZVdvcnggU0VPIFN1aXRlIChodHRwOi8vd3d3Lm1hZ2V3b3J4LmNvbS8p');
    }

    public function getLayerFilterUrl($params)
    {
        if (!Mage::getStoreConfigFlag('mageworx_seo/seosuite/layered_friendly_urls')){
            return Mage::getUrl('*/*/*', $params);
        }
        $hideAttributes = Mage::getStoreConfigFlag('mageworx_seo/seosuite/layered_hide_attributes');

        $urlModel = Mage::getModel('core/url');

        $queryParams = $urlModel->getRequest()->getQuery();

        foreach ($params['_query'] as $param => $value){
            $queryParams[$param] = $value;
        }
        $queryParams = array_filter($queryParams);
        $attr = Mage::registry('_layer_filterable_attributes');
        $layerParams = array();
        foreach ($queryParams as $param => $value){
            if ($param == 'cat' || isset($attr[$param])){
                switch ($hideAttributes){
                    case true:
                        $layerParams[$param == 'cat' ? 0 : $param] = ($param == 'cat' ? $this->formatUrlKey($value) : ($attr[$param]['type'] == 'decimal' ? $this->formatUrlKey($param) . '-' . $value : $this->formatUrlKey($value)));
                        break;
                    default:
                        $layerParams[$param == 'cat' ? 0 : $param] = ($param == 'cat' ? $this->formatUrlKey($value) : $this->formatUrlKey($param) . '-' . ($attr[$param]['type'] == 'decimal' ? $value : $this->formatUrlKey($value)));
                        break;
                }
                $params['_query'][$param] = null;
            }
        }
        $layer = null;
        if (!empty($layerParams)){
            uksort($layerParams, 'strcmp');
            $layer = implode('/', $layerParams);
        }
        $url = Mage::getUrl('*/*/*', $params);
        if (!$layer){
            return $url;
        }
        $urlParts = explode('?', $url, 2);
        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        $url = str_replace($suffix, '', $urlParts[0]);

        return $url . '/l/' . $layer . $suffix . (isset($urlParts[1]) ? '?' . $urlParts[1] : '');
    }

    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '_', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }
}