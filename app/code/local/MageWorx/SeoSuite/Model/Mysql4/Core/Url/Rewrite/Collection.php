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
class MageWorx_SeoSuite_Model_Mysql4_Core_Url_Rewrite_Collection extends Mage_Core_Model_Mysql4_Url_Rewrite_Collection {

    protected function _initSelect() {
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()), array('*', new Zend_Db_Expr('LENGTH(request_path)')));
        return $this;
    }

    public function sortByLength($spec = 'DESC') {
        $this->getSelect()->order(new Zend_Db_Expr('LENGTH(request_path) ' . $spec));
        return $this;
    }

    public function filterAllByProductId($productId, $useCategories = false) {
        if ($productId != null) {
            if ($useCategories == 1) {
                $this->getSelect()->where('product_id = ? AND category_id is not null', $productId, Zend_Db::INT_TYPE);
            } else if ($useCategories == 2) {
                $this->getSelect()->where('product_id = ? AND category_id is null', $productId, Zend_Db::INT_TYPE);
            } else {
                $this->getSelect()->where('product_id = ?', $productId, Zend_Db::INT_TYPE);
            }
        }

        return $this;
    }

    public function filterByIdPath($idPath) {
        $this->getSelect()
                ->where('id_path = ?', $idPath);
        return $this;
    }

}