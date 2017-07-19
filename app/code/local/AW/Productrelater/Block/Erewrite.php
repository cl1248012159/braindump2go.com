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
class AW_Productrelater_Block_Erewrite	extends Enterprise_TargetRule_Block_Catalog_Product_List_Abstract {
    protected function _beforeToHtml() {
        $this->setBlockName(Mage::getStoreConfig("productrelater/mainoptions/blockname"));
        switch(Mage::helper('productrelater')->getMagentoVersionCode()) {
            case AW_Productrelater_Helper_Data::MAGENTO_VERSION_EE_1_9:
            case AW_Productrelater_Helper_Data::MAGENTO_VERSION_EE_1_8:
                $_template = 'productrelater/targetrule_related18.phtml';
                break;
        }
        if(isset($_template) && Mage::getStoreConfig('productrelater/mainoptions/replaceoriginalrp')) $this->setTemplate($_template);
        return parent::_beforeToHtml();
    }

    /**
     * Escape string for using it in SQL Query
     * @param string $str string for escaping
     * @return string escaped string
     */
    function escape($str)	{
        $str=Mage::getSingleton('core/resource')->getConnection('core_read')->quote($str);
        return substr($str,1,-1);
    }

    /**
     * Returns id of current category if it is possible, otherwise returns list
     * of all categories of the current product.
     * @return string list of categories separated by commas
     */
    protected function getCategories() {
        $categories = array();
        if (Mage::registry("current_category"))
            $categories[] = Mage::registry("current_category")->getId();
        elseif ($this->getProduct()&&$this->getProduct()->getCategoryIds())
            $categories = $this->getProduct()->getCategoryIds();
        $_categories = '';
        for($i = 0;$i<count($categories);$i++)
            $_categories .= ($i==0?'':',').$categories[$i];
        return $_categories;
    }
	
    public function getItemCollection()	{
        if (is_null($this->_items)) {
            $myConf=Mage::getStoreConfig("productrelater");
            $__itemscount = $myConf["mainoptions"]["itemscount"];
            $__source = $myConf["mainoptions"]["source"];
            $__selectproducts = $myConf["mainoptions"]["selectproducts"];
            $__fetchinstock = $myConf["mainoptions"]["fetchinstock"];
            $__pricecondition = $myConf["mainoptions"]["pricecondition"];
            $__price = $myConf["mainoptions"]["price"];
            $__replaceoriginalrp = $myConf["mainoptions"]["replaceoriginalrp"];
            $__applypriceconditionfor = $myConf['mainoptions']['replaceoriginalrp'];

            if(!$__replaceoriginalrp) return parent::getItemCollection();

            if(!(int)$__itemscount||(int)$__itemscount<1)               $__itemscount = 3;
            if(($__source!=='1'&&$__source!=='2')
                ||!$this->getProduct())                                 $__source = 1;
            if((int)$__selectproducts<1||(int)$__selectproducts>3)      $__selectproducts = 2;
            if(!$this->getProduct()&&$__selectproducts==3)              $__selectproducts = 2;
            if((int)$__fetchinstock<0||(int)$__fetchinstock>1||!$__fetchinstock)    $__fetchinstock = 0;
            if((int)$__pricecondition<1||(int)$__pricecondition>5)      $__pricecondition = 1;
            if(!$__price) $__price = 0;
                else $__price = ((int)$__price);

            $visibility = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
            );
            //get all products and finding related
            $collection = Mage::getModel('productrelater/product_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('visibility', $visibility)
                ->addAttributeToFilter('status',array("in"=>Mage::getSingleton("catalog/product_status")->getVisibleStatusIds()));
            //removing itself from collection

            if($this->getProduct())
                $collection->addAttributeToFilter("entity_id",array("neq"=>$this->getProduct()->getId()));

            //Source filter
            $categories = $this->getCategories();
            $collection->addStoreFilter(Mage::app()->getStore()->getCode());
            if($__source==2) $collection->addCategoriesFilter($categories);

            //Fetch only in stock filter
            if($__fetchinstock)
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

            //Filtering by price
            if($__pricecondition!=1)
                switch($__applypriceconditionfor) {
                    case 2://special price
                        $_removeIds = array();
                        $collection->load();
                        foreach($collection as $_cItem) {
                            $_cItem->setSpecialPrice($_cItem->getPriceModel()->calculateSpecialPrice($_cItem->getPrice(),$_cItem->getSpecialPrice(),$_cItem->getSpecialFromDate(),$_cItem->getSpecialToDate()));
                            switch($__pricecondition) {
                                case 2:
                                    if($_cItem->getSpecialPrice()>=$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                                case 3:
                                    if($_cItem->getSpecialPrice()<$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                                case 4:
                                    if($_cItem->getSpecialPrice()<=$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                                case 5:
                                    if($_cItem->getSpecialPrice()>$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                            }
                        }
                        $_validIds = array_values(array_diff($collection->getAllIds(),$_removeIds));
                        $validIds = array();
                        for($i = 0;$i<count($_validIds);$i++)
                            $validIds[] = array('attribute' => 'entity_id', 'eq' => $_validIds[$i]);
                        unset($_validIds);
                        unset($_removeIds);
                        unset($collection);
                        if (empty($validIds)) $validIds=0;
                        $collection = Mage::getModel('productrelater/product_collection')
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id', $validIds);
                    break;
                    case 3://final price
                        $_removeIds = array();
                        $collection->load();
                        foreach($collection as $_cItem) {
                            switch($__pricecondition) {
                                case 2:
                                    if($_cItem->getFinalPrice()>=$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                                case 3:
                                    if($_cItem->getFinalPrice()<$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                                case 4:
                                    if($_cItem->getFinalPrice()<=$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                                case 5:
                                    if($_cItem->getFinalPrice()>$__price) $_removeIds[] = $_cItem->getId();
                                    break;
                            }
                        }
                        $_validIds = array_values(array_diff($collection->getAllIds(),$_removeIds));
                        $validIds = array();
                        for($i = 0;$i<count($_validIds);$i++)
                            $validIds[] = array('attribute' => 'entity_id', 'eq' => $_validIds[$i]);
                        unset($_validIds);
                        unset($_removeIds);
                        unset($collection);
                        if (empty($validIds)) $validIds=0;
                        $collection = Mage::getModel('productrelater/product_collection')
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('entity_id', $validIds);
                    break;
                    default://regular price
                        switch($__pricecondition) {
                            case 2://Less than
                                $collection->addAttributeToFilter("price",array("lt"=>$__price));
                                break;
                            case 3://Not less than
                                $collection->addAttributeToFilter("price",array("moreq"=>$__price));
                                break;
                            case 4://More than
                                $collection->addAttributeToFilter("price",array("gt"=>$__price));
                                break;
                            case 5://Not more than
                                $collection->addAttributeToFilter("price",array("lteq"=>$__price));
                                break;
                        }
                    break;
                }

                //Select products filter
                switch($__selectproducts)	{
                    case 1://random selection of products
                        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                    break;
                    case 2://last added products
                        $collection->addAttributeToSort('created_at','desc');
                    break;
                    case 3://lexically similar
                        $allWords=explode(" ",$this->getProduct()->getName());
                        $exprArray=array();
                        for($i=0;$i<count($allWords);$i++)
                            if(strlen($allWords[$i])>=3)
                                $exprArray[]=array('attribute'=>'name','like'=>$this->escape('%'.$allWords[$i].'%'));
                        unset($allWords);
                        $collection->addAttributeToFilter("name",array('or'=>$exprArray));
                    break;
                }

                //Limiting count to number of items
                $collection->setPageSize($__itemscount);
                $collection->setCurPage(1);

                foreach ($collection as $item) {
                    $this->_items[$item->getEntityId()] = $item;
                }
        }
        return $this->_items;
    }
	
    public function getType() {
        return Enterprise_TargetRule_Model_Rule::RELATED_PRODUCTS;
    }

    public function getExcludeProductIds() {
        if (is_null($this->_excludeProductIds)) {
            $cartProductIds = Mage::getSingleton('checkout/cart')->getProductIds();
            $this->_excludeProductIds = array_merge($cartProductIds, array($this->getProduct()->getEntityId()));
        }
        return $this->_excludeProductIds;
    }
}
