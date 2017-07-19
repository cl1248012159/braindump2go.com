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
class AW_Productrelater_Block_List extends Mage_Catalog_Block_Product_Abstract {
    public function __construct() {
        if(!$this->getTemplate()) {
            switch(Mage::helper('productrelater')->getMagentoVersionCode()) {
                case AW_Productrelater_Helper_Data::MAGENTO_VERSION_CE_1_4:
                    $this->setTemplate("productrelater/productrelater_block_1.4.phtml");
                break;
                default:
                    $this->setTemplate("productrelater/productrelater_block.phtml");
                break;
            }
        }
    }
	
    public function getProduct() {
        if(Mage::registry('product'))
            return Mage::registry('product');
        return isset($this->_data['product']) ? $this->_data['product'] : null;
    }

    /**
     * Escape string for using it in SQL Query
     * @param string $str string for escaping
     * @return string escaped string
     */
    public function escape($str) {
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
	
    public function _toHtml() {
        //Loading extension configuration.
        //If some options are setted from block call they has higher priority than default values
        $myConf=Mage::getStoreConfig("productrelater");
        if(!$this->getItemscount())             $this->setItemscount($myConf["mainoptions"]["itemscount"]);
        if(!$this->getSource())                 $this->setSource($myConf["mainoptions"]["source"]);
        if(!$this->getSelectproducts())         $this->setSelectproducts($myConf["mainoptions"]["selectproducts"]);
        if(is_null($this->getFetchinstock()))   $this->setFetchinstock($myConf["mainoptions"]["fetchinstock"]);
        if(!$this->getPricecondition())         $this->setPricecondition($myConf["mainoptions"]["pricecondition"]);
        if(!$this->getApplyPriceConditionFor()) $this->setApplyPriceConditionFor($myConf['mainoptions']['applypriceconditionfor']);
        if(is_null($this->getPrice()))          $this->setPrice($myConf["mainoptions"]["price"]);
        if(!$this->getBlockName())              $this->setBlockName($myConf["mainoptions"]["blockname"]);
        $this->setReplaceoriginalrp($myConf["mainoptions"]["replaceoriginalrp"]);

        //don`t display anything when rewriting standard related products is enabled
        if($this->getNameInLayout()==="productrelater_list"&&$this->getReplaceoriginalrp()) return;

        if(!(int)$this->getItemscount()||(int)$this->getItemscount()<1)     $this->setItemscount(3);
        if((int)$this->getSource()!=1&&(int)$this->getSource()!=2)          $this->setSource(1);

        $categories = $this->getCategories();
        if(empty($categories)) $this->setSource(1);

        if((int)$this->getSelectproducts()<1||(int)$this->getSelectproducts()>3)    $this->setSelectproducts(2);
        if((int)$this->getFetchinstock()<0||(int)$this->getFetchinstock()>1)        $this->setFetchinstock(0);
        if((int)$this->getPricecondition()<1||(int)$this->getPricecondition()>5)    $this->setPricecondition(1);
        if(!$this->getPrice()) $this->setPrice(0);
            else $this->setPrice((float)$this->getPrice());

        //get all products and finding related
        if(!$this->getProduct()&&$this->getSelectproducts()==3)	$this->setCount(0);
        else {
        $visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        );
        $collection = Mage::getModel('productrelater/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', $visibility)
            ->addAttributeToFilter('status',array("in"=>Mage::getSingleton("catalog/product_status")->getVisibleStatusIds()));
            //->addFinalPrice();
        //removing itself from collection
        if($this->getProduct())
            $collection->addAttributeToFilter("entity_id",array("neq"=>$this->getProduct()->getId()));

        //Source filter
        $collection->addStoreFilter(Mage::app()->getStore()->getCode());
        if($this->getSource()==2&&!empty($categories)) $collection->addCategoriesFilter($categories);

        //Fetch only in stock filter
        if($this->getFetchinstock())
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        //Filtering by price
        if($this->getPricecondition()!=1)
            switch($this->getApplyPriceConditionFor()) {
                case 2://special price
                    $_removeIds = array();
                    $collection->load();
                    foreach($collection as $_cItem) {
                        $_cItem->setSpecialPrice($_cItem->getPriceModel()->calculateSpecialPrice($_cItem->getPrice(),$_cItem->getSpecialPrice(),$_cItem->getSpecialFromDate(),$_cItem->getSpecialToDate()));

                        switch($this->getPricecondition()) {
                            case 2:
                                if($_cItem->getSpecialPrice()>=$this->getPrice()) $_removeIds[] = $_cItem->getId();
                                break;
                            case 3:
                                if($_cItem->getSpecialPrice()<$this->getPrice()) $_removeIds[] = $_cItem->getId();
                                break;
                            case 4:
                                if($_cItem->getSpecialPrice()<=$this->getPrice()) $_removeIds[] = $_cItem->getId();
                                break;
                            case 5:
                                if($_cItem->getSpecialPrice()>$this->getPrice()) $_removeIds[] = $_cItem->getId();
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
                        switch($this->getPricecondition()) {
                            case 2:
                                if($_cItem->getFinalPrice()>=$this->getPrice()) $_removeIds[] = $_cItem->getId();
                                break;
                            case 3:
                                if($_cItem->getFinalPrice()<$this->getPrice()) $_removeIds[] = $_cItem->getId();
                                break;
                            case 4:
                                if($_cItem->getFinalPrice()<=$this->getPrice()) $_removeIds[] = $_cItem->getId();
                                break;
                            case 5:
                                if($_cItem->getFinalPrice()>$this->getPrice()) $_removeIds[] = $_cItem->getId();
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
                    switch($this->getPricecondition()) {
                        case 2://Less than
                            $collection->addAttributeToFilter("price",array("lt"=>$this->getPrice()));
                            break;
                        case 3://Not less than
                            $collection->addAttributeToFilter("price",array("moreq"=>$this->getPrice()));
                            break;
                        case 4://More than
                            $collection->addAttributeToFilter("price",array("gt"=>$this->getPrice()));
                            break;
                        case 5://Not more than
                            $collection->addAttributeToFilter("price",array("lteq"=>$this->getPrice()));
                            break;
                    }
                break;
            }

            //Select products filter
            switch($this->getSelectproducts()) {
                case 1://random selection of products
                    $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                    break;
                case 2://last added products
                    $collection->addAttributeToSort('created_at','desc');
                    break;
                case 3://lexically similar
                    if(!$this->getProduct()) break;
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
            $collection->setPageSize($this->getItemscount());
            $collection->setCurPage(1);
            foreach ($collection as $product) {
                $product->setDoNotUseCategoryId(true);
            }
            $this->setItems($collection);
            $this->setCount(count($collection));
        }
        return parent::_toHtml();
    }
}
