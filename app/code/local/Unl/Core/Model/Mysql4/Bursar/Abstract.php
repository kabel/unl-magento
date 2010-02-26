<?php

class Unl_Core_Model_Mysql4_Bursar_Abstract extends Mage_Reports_Model_Mysql4_Order_Collection
{
    protected $_paymentMethodCodes = array();
    
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to));
        
        return $this;
    }
    
    public function setStoreIds($storeIds)
    {
        $vals = array_values($storeIds);
        
        $this->initSelect($storeIds);
                
        return $this;
    }
    
    public function initSelect($storeIds, $forTotals = false)
    {
        $this->joinOtherTables();
        
        if ($forTotals) {
            parent::setStoreIds($storeIds);
        } else {
            $this->getSelect()
                ->from("", array("subtotal" => "SUM(order_item.base_row_total)"))
                ->from("", array("tax" => "SUM(order_item.base_tax_amount)"))
                ->from("", array("shipping" => "0"))
                ->from("", array("total" => "SUM(order_item.base_row_total - order_item.base_discount_amount + order_item.base_tax_amount)"))
                ->from("", array("invoiced" => "SUM(order_item.base_row_invoiced - order_item.base_discount_invoiced + order_item.base_tax_invoiced)"))
                ->from("", array("refunded" => "SUM(order_item.base_amount_refunded)"))
                ->joinInner(
                    array('order_item' => $this->getTable('sales/order_item')), 
                    "order_item.order_id = e.entity_id AND order_item.parent_item_id IS NULL", 
                    array('entity_id' => 'order_item.item_id'))
                ->joinLeft(
                    array('store' => $this->getTable('core_store')),
                    "order_item.source_store_view = store.store_id",
                    array())
                ->joinLeft(
                    array('stgroup' => $this->getTable('core_store_group')),
                    "store.group_id = stgroup.group_id",
                    array("merchant" => "stgroup.name"))
                ->group("stgroup.group_id");
        }
        
        $this->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));
        
        return $this;
    }
    
    protected function joinOtherTables()
    {
        /* @var $paymentModel Mage_Sales_Model_Mysql4_Order_Payment */
        $paymentModel = Mage::getResourceSingleton('sales/order_payment');
        $methodAttr = $paymentModel->getAttribute('method');
        
        $this->getSelect()
            ->joinInner(
                array('payment' => $paymentModel->getEntityTable()),
                'payment.entity_type_id = ' . $paymentModel->getEntityType()->getId() . ' AND payment.parent_id = e.entity_id',
                array())
            ->joinInner(
                array('payment_method' => $methodAttr->getBackendTable()),
                'payment.entity_id = payment_method.entity_id AND payment_method.attribute_id = ' . $methodAttr->getId() . ' AND ' . $this->_getConditionSql('payment_method.value', array('in' => $this->_paymentMethodCodes)),
                array());
    }
}