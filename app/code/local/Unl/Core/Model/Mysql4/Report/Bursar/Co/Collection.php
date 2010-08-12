<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Co_Collection extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
{
    protected $_paymentMethodCodes = array('purchaseorder');
    
    protected function _getNonTotalColumns($fromItems = true)
    {
        $columns = parent::_getNonTotalColumns($fromItems);
        $columns += array(
            'po_number' => 'p.po_number',
            'order_num' => 'o.increment_id'
        );
        
        return $columns;
    }
    
    protected function _initSelect()
    {
        if ($this->_inited) {
            return $this;
        }

        $mainTable = $this->getResource()->getMainTable();
        
        $select = $this->getSelect();

        if ($this->isTotals() || $this->isSubTotals()) {
            $selectOrderItem = $this->getConnection()->select()
                ->from($this->getTable('sales/order_item'), array(
                    'order_id'           => 'order_id',
                    'total_qty_ordered'  => 'SUM(qty_ordered - IFNULL(qty_canceled, 0))',
                    'total_qty_invoiced' => 'SUM(qty_invoiced)',
                ))
                ->group('order_id');
            
            $select->from(array('o' => $mainTable), $this->_getTotalColumns($this->isSubTotals()))
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = o.entity_id', array())
                ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array())
                ->where('p.method IN (?)', $this->_paymentMethodCodes)
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED
                ));
                
            $this->_applyOrderStatusFilter();
            
            if ($this->isSubTotals()) {
                $select->group($this->_periodFormat);
            }
            
            if ($this->_to !== null) {
                $select->where("DATE(o.{$this->getRecordType()}) <= DATE(?)", $this->_to);
            }
    
            if ($this->_from !== null) {
                $select->where("DATE(o.{$this->getRecordType()}) >= DATE(?)", $this->_from);
            }
        } else {
            $sql1 = clone $this->getSelect();
            $sql1->from(array('o' => $mainTable), $this->_getNonTotalColumns(true))
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = o.entity_id AND oi.parent_item_id IS NULL', array())
                ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array())
                ->joinLeft(array('s' => $this->getTable('core/store')), 'oi.source_store_view = s.store_id', array())
                ->joinLeft(array('sg' => $this->getTable('core/store_group')), 's.group_id = sg.group_id', array())
                ->where('p.method IN (?)', $this->_paymentMethodCodes)
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED
                ))
                ->group(array($this->_periodFormat, 'sg.group_id', 'o.entity_id'));
            $this->_applyOrderStatusFilter($sql1);
            
            $sql2 = clone $this->getSelect();
            $sql2->from(array('o' => $mainTable), $this->_getNonTotalColumns(false))
                ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array())
                ->where('p.method IN (?)', $this->_paymentMethodCodes)
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED
                ))
                ->group(array($this->_periodFormat, 'o.entity_id'));
            $this->_applyOrderStatusFilter($sql2);
            
            if ($this->_to !== null) {
                $sql1->where("DATE(o.{$this->getRecordType()}) <= DATE(?)", $this->_to);
                $sql2->where("DATE(o.{$this->getRecordType()}) <= DATE(?)", $this->_to);
            }
    
            if ($this->_from !== null) {
                $sql1->where("DATE(o.{$this->getRecordType()}) >= DATE(?)", $this->_from);
                $sql2->where("DATE(o.{$this->getRecordType()}) >= DATE(?)", $this->_from);
            }
            
            $select->union(array('(' . $sql1 . ')', '(' . $sql2 . ')'));
        }

        $this->_inited = true;
        return $this;
    }
}