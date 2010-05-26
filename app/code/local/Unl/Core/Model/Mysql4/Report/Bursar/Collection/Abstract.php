<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_inited = false;
    protected $_selectedColumns = array();
    protected $_recordType;
    protected $_paymentMethodCodes = array();
    
    /**
     * Initialize custom resource model
     *
     * @param array $parameters
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/order', 'entity_id');
        $this->setConnection($this->getResource()->getReadConnection());
    }
    
    /**
     * Apply order status filter
     *
     * @return Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
     */
    protected function _applyOrderStatusFilter($select = null)
    {
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
        
        if (null === $select) {
            $select = $this->getSelect();
        }
        $select->where('status IN(?)', $orderStatus);
        return $this;
    }
    
    protected function _getTotalColumns($isSubtotal = false)
    {
        $this->_selectedColumns = array(
            'total_qty_ordered'         => 'SUM(e.total_qty_ordered)',
            'base_profit_amount'        => 'SUM(IFNULL(e.base_subtotal_invoiced, 0) * e.base_to_global_rate) + SUM(IFNULL(e.base_discount_refunded, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_subtotal_refunded, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_discount_invoiced, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_total_invoiced_cost, 0) * e.base_to_global_rate)',
            'base_subtotal_amount'      => 'SUM(e.base_subtotal * e.base_to_global_rate)',
            'base_tax_amount'           => 'SUM(e.base_tax_amount * e.base_to_global_rate)',
            'base_shipping_amount'      => 'SUM(e.base_shipping_amount * e.base_to_global_rate)',
            'base_discount_amount'      => 'SUM(e.base_discount_amount * e.base_to_global_rate)',
            'base_grand_total_amount'   => 'SUM(e.base_grand_total * e.base_to_global_rate)',
            'base_invoiced_amount'      => 'SUM(e.base_total_paid * e.base_to_global_rate)',
            'base_refunded_amount'      => 'SUM(e.base_total_refunded * e.base_to_global_rate)',
            'base_canceled_amount'      => 'SUM(IFNULL(e.subtotal_canceled, 0) * e.base_to_global_rate)'
        );
        
        if ($isSubtotal) {
            if ('month' == $this->_period) {
                $this->_periodFormat = "DATE_FORMAT(e.{$this->getRecordType()}, '%Y-%m')";
            } elseif ('year' == $this->_period) {
                $this->_periodFormat = "EXTRACT(YEAR FROM e.{$this->getRecordType()})";
            } else {
                $this->_periodFormat = "DATE(e.{$this->getRecordType()})";
            }
            $this->_selectedColumns += array('period' => $this->_periodFormat);
        }
        
        return $this->_selectedColumns;
    }
    
    protected function _getNonTotalColumns($fromItems = true)
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = "DATE_FORMAT(e.{$this->getRecordType()}, '%Y-%m')";
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = "EXTRACT(YEAR FROM e.{$this->getRecordType()})";
        } else {
            $this->_periodFormat = "DATE(e.{$this->getRecordType()})";
        }
        $columns = array('period' => $this->_periodFormat);
        
        if ($fromItems) {
            $columns += array(
                'merchant'                  => 'sg.name',
                'orders_count'              => 'COUNT(DISTINCT(e.entity_id))',
                'total_qty_ordered'         => 'SUM(oi.qty_ordered)',
                'base_profit_amount'        => 'SUM((oi.base_row_invoiced - oi.base_amount_refunded - (oi.base_cost * oi.qty_invoiced)) * e.base_to_global_rate)',
                'base_subtotal_amount'      => 'SUM(oi.base_row_total * e.base_to_global_rate)',
                'base_tax_amount'           => 'SUM(oi.base_tax_amount * e.base_to_global_rate)',
                'base_shipping_amount'      => new Zend_Db_Expr('0'),
                'base_discount_amount'      => 'SUM(oi.base_discount_amount * e.base_to_global_rate)',
                'base_grand_total_amount'   => 'SUM((oi.base_row_total + oi.base_tax_amount - oi.base_discount_amount) * e.base_to_global_rate)',
                'base_invoiced_amount'      => 'SUM((oi.base_row_invoiced + oi.base_tax_invoiced - oi.base_discount_invoiced) * e.base_to_global_rate)',
                'base_payout_amount'        => 'SUM(((oi.base_row_invoiced - oi.base_discount_invoiced) * (1 - (s.unl_rate / 100))) * e.base_to_global_rate)',
                'base_refunded_amount'      => 'SUM(oi.base_amount_refunded * e.base_to_global_rate)',
                'base_canceled_amount'      => 'SUM(oi.base_price * oi.qty_canceled * e.base_to_global_rate)'
            );
        } else {
            $columns += array(
                'merchant'                  => new Zend_Db_Expr("'CENTRALIZED ACCOUNT'"),
                'orders_count'              => 'COUNT(DISTINCT(e.entity_id))',
                'total_qty_ordered'         => new Zend_Db_Expr('0'),
                'base_profit_amount'        => new Zend_Db_Expr('0'),
                'base_subtotal_amount'      => new Zend_Db_Expr('0'),
                'base_tax_amount'           => 'SUM(IFNULL(e.base_shipping_tax_amount, 0) * e.base_to_global_rate)',
                'base_shipping_amount'      => 'SUM(e.base_shipping_amount * e.base_to_global_rate)',
                'base_discount_amount'      => new Zend_Db_Expr('0'),
                'base_grand_total_amount'   => 'SUM((e.base_shipping_amount + IFNULL(e.base_shipping_tax_amount, 0)) * e.base_to_global_rate)',
                'base_invoiced_amount'      => 'SUM(IFNULL(e.base_shipping_invoiced + e.base_shipping_tax_amount, 0) * e.base_to_global_rate)',
                'base_payout_amount'        => 'SUM(IFNULL(e.base_shipping_invoiced, 0) * e.base_to_global_rate)',
                'base_refunded_amount'      => 'SUM((IFNULL(e.base_subtotal_refunded, 0) - IFNULL(e.base_discount_refunded, 0) + IFNULL(e.base_tax_refunded, 0) + IFNULL(e.base_shipping_refunded, 0)) * e.base_to_global_rate)',
                'base_canceled_amount'      => 'SUM(IFNULL(e.base_shipping_canceled, 0))'
            );
        }
        
        return $columns;
    }
    
    /**
     * Add selected data
     *
     * @return Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
     */
    protected function _initSelect()
    {
        if ($this->_inited) {
            return $this;
        }

        $mainTable = $this->getResource()->getMainTable();

        if (!is_null($this->_from) || !is_null($this->_to)) {
            $where = (!is_null($this->_from)) ? "so.{$this->getRecordType()} >= '{$this->_from}'" : '';
            if (!is_null($this->_to)) {
                $where .= (!empty($where)) ? " AND so.{$this->getRecordType()} <= '{$this->_to}'" : "so.{$this->getRecordType()} <= '{$this->_to}'";
            }

            $subQuery = clone $this->getSelect();
            $subQuery->from(array('so' => $mainTable), array("DISTINCT DATE(so.{$this->getRecordType()})"))
                ->where($where);
        }

        $select = $this->getSelect();
        $paymentModel = Mage::getResourceSingleton('sales/order_payment');
        $methodAttr = $paymentModel->getAttribute('method');
        
        if ($this->isTotals() || $this->isSubTotals()) {
            $select->from(array('e' => $mainTable), $this->_getTotalColumns($this->isSubTotals()))
                ->join(array('p' => $paymentModel->getEntityTable()), 'p.entity_type_id = ' . $paymentModel->getEntityType()->getId() . ' AND p.parent_id = e.entity_id', array())
                ->join(array('pm' => $methodAttr->getBackendTable()), 'p.entity_id = pm.entity_id AND pm.attribute_id = ' . $methodAttr->getId() . ' AND ' . $this->_getConditionSql('pm.value', array('in' => $this->_paymentMethodCodes)), array())
                ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ));
                
            $this->_applyOrderStatusFilter();
            
            if ($this->isSubTotals()) {
                $select->group($this->_periodFormat);
            }
            
            if (!is_null($this->_from) || !is_null($this->_to)) {
                $select->where("DATE(e.{$this->getRecordType()}) IN(?)", new Zend_Db_Expr($subQuery));
            }
        } else {
            $sql1 = clone $this->getSelect();
            $sql1->from(array('e' => $mainTable), $this->_getNonTotalColumns(true))
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = e.entity_id AND oi.parent_item_id IS NULL', array())
                ->join(array('p' => $paymentModel->getEntityTable()), 'p.entity_type_id = ' . $paymentModel->getEntityType()->getId() . ' AND p.parent_id = e.entity_id', array())
                ->join(array('pm' => $methodAttr->getBackendTable()), 'p.entity_id = pm.entity_id AND pm.attribute_id = ' . $methodAttr->getId() . ' AND ' . $this->_getConditionSql('pm.value', array('in' => $this->_paymentMethodCodes)), array())
                ->joinLeft(array('s' => $this->getTable('core/store')), 'oi.source_store_view = s.store_id', array())
                ->joinLeft(array('sg' => $this->getTable('core/store_group')), 's.group_id = sg.group_id', array())
                ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ))
                ->group(array($this->_periodFormat, 'sg.group_id'));
            $this->_applyOrderStatusFilter($sql1);
            
            $sql2 = clone $this->getSelect();
            $sql2->from(array('e' => $mainTable), $this->_getNonTotalColumns(false))
                ->join(array('p' => $paymentModel->getEntityTable()), 'p.entity_type_id = ' . $paymentModel->getEntityType()->getId() . ' AND p.parent_id = e.entity_id', array())
                ->join(array('pm' => $methodAttr->getBackendTable()), 'p.entity_id = pm.entity_id AND pm.attribute_id = ' . $methodAttr->getId() . ' AND ' . $this->_getConditionSql('pm.value', array('in' => $this->_paymentMethodCodes)), array())
                ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ))
                ->group($this->_periodFormat);
            $this->_applyOrderStatusFilter($sql2);
            
            if (!is_null($this->_from) || !is_null($this->_to)) {
                $sql1->where("DATE(e.{$this->getRecordType()}) IN(?)", new Zend_Db_Expr($subQuery));
                $sql2->where("DATE(e.{$this->getRecordType()}) IN(?)", new Zend_Db_Expr($subQuery));
            }
            
            $select->union(array('(' . $sql1 . ')', '(' . $sql2 . ')'));
        }

        $this->_inited = true;
        return $this;
    }

    /**
     * Load
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_initSelect();
        $this->setApplyFilters(false);
        return parent::load($printQuery, $logQuery);
    }
    
    public function setRecordType($type = 'created_at')
    {
        $this->_recordType = $type;
        
        return $this;
    }
    
    public function getRecordType()
    {
        if (null === $this->_recordType) {
            $this->setRecordType();
        }
        
        return $this->_recordType;
    }
}