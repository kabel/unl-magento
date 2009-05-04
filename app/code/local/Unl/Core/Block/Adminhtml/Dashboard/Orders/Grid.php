<?php

class Unl_Core_Block_Adminhtml_Dashboard_Orders_Grid extends Mage_Adminhtml_Block_Dashboard_Orders_Grid
{
    protected function _prepareCollection()
    {
        //TODO: add full name logic
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addItemCountExpr()
            ->addExpressionAttributeToSelect('customer',
                "IFNULL(CONCAT({{customer_firstname}},' ',{{customer_lastname}}), '{$this->__('Guest')}')",
                array('customer_firstname','customer_lastname')
            )
            ->setOrder('created_at');

        if($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
            if ($this->getParam('website')){
                $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
                $collection->addExpressionAttributeToSelect('revenue',
                    '({{base_grand_total}})',
                    array('base_grand_total'));
            } else {
                if ($this->getParam('store')) {
                    $storeIds = array($this->getParam('store'));
                } else if ($this->getParam('group')){
                    $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
                }
                $product = Mage::getResourceSingleton('catalog/product');
                $expr = "(items.base_row_total-IFNULL(items.base_discount_amount,0))";
                $collection->getSelect()
                    ->joinInner(
                        array('product_int' => $collection->getTable('catalog_product_entity_int')),
                        "items.product_id = product_int.entity_id AND product_int.entity_type_id = {$product->getTypeId()}",
                        array())
                    ->joinInner(
                        array('eav' => $collection->getTable('eav_attribute')),
                        "eav.attribute_id = product_int.attribute_id AND eav.attribute_code = 'source_store_view'",
                        array())
                    ->where("product_int.value IN (?)", (array)$storeIds)
                    ->from("", array(
                        'revenue' => "SUM({$expr})"
                    ));
                
            }
        } else {
            $collection->addExpressionAttributeToSelect('revenue',
                '({{base_grand_total}}/{{store_to_base_rate}})',
                array('base_grand_total', 'store_to_base_rate'));
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }
}