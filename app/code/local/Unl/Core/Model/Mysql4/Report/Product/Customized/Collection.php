<?php

class Unl_Core_Model_Mysql4_Report_Product_Customized_Collection extends Mage_Sales_Model_Mysql4_Order_Item_Collection
{
    protected $_isReady = false;
    
    /**
     * Sets the collection up to filter on a time span
     * 
     * @param Zend_Date $from
     * @param Zend_Date $to
     */
    public function setInterval($from, $to)
    {
        if ($from->compare($to) <= 0) {
            $to->setTime('23:59:59');
            $dateFilter = " AND order.created_at BETWEEN '{$this->timeShift($from)}' AND '{$this->timeShift($to)}'";
            
            $order = Mage::getResourceSingleton('sales/order');
            /* @var $order Mage_Sales_Model_Entity_Order */
            $stateAttr = $order->getAttribute('state');
            if ($stateAttr->getBackend()->isStatic()) {
    
                $_joinCondition = $this->getConnection()->quoteInto(
                    'order.entity_id = main_table.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
                );
                $_joinCondition .= $dateFilter;
    
                $this->getSelect()->joinInner(
                    array('order' => $this->getTable('sales/order')),
                    $_joinCondition,
                    array()
                );
            } else {
    
                $_joinCondition = 'order.entity_id = order_state.entity_id';
                $_joinCondition .= $this->getConnection()->quoteInto(' AND order_state.attribute_id=? ', $stateAttr->getId());
                $_joinCondition .= $this->getConnection()->quoteInto(' AND order_state.value<>? ', Mage_Sales_Model_Order::STATE_CANCELED);
    
                $this->getSelect()
                    ->joinInner(
                        array('order' => $this->getTable('sales/order')),
                        'order.entity_id = main_table.order_id' . $dateFilter,
                        array())
                    ->joinInner(
                        array('order_state' => $stateAttr->getBackend()->getTable()),
                        $_joinCondition,
                        array());
            }
            
            $this->_isReady = true;
        }
    }
    
    /**
     * Returns a datetime formatted in GMT
     * 
     * @param Zend_Date $datetime
     */
    public function timeShift($datetime)
    {
        return $datetime->subSecond($datetime->getGmtOffset())->toString('YYYY-MM-dd HH:mm:ss');
    }
    
    public function isReady()
    {
        return $this->_isReady;
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $key => $item) {
            $options = $item->getProductOptionByCode('options');
            if (empty($options)) {
                $this->removeItemByKey($key);
            }
        }
    }
}