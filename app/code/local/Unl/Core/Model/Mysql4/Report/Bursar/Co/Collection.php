<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Co_Collection extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
{
    protected $_paymentMethodCodes = array('purchaseorder');
    
    protected function _getNonTotalColumns($fromItems = true)
    {
        $columns = parent::_getNonTotalColumns($fromItems);
        $columns += array('po_number' => 'pmno.value');
        
        return $columns;
    }
    
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
        $poNumberAttr = $paymentModel->getAttribute('po_number');
        
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
                ->join(array('pmno' => $poNumberAttr->getBackendTable()), 'p.entity_id = pmno.entity_id AND pmno.attribute_id = ' . $poNumberAttr->getId(), array())
                ->joinLeft(array('s' => $this->getTable('core/store')), 'oi.source_store_view = s.store_id', array())
                ->joinLeft(array('sg' => $this->getTable('core/store_group')), 's.group_id = sg.group_id', array())
                ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ))
                ->group(array($this->_periodFormat, 'sg.group_id', 'pmno.value'));
            $this->_applyOrderStatusFilter($sql1);
            
            $sql2 = clone $this->getSelect();
            $sql2->from(array('e' => $mainTable), $this->_getNonTotalColumns(false))
                ->join(array('p' => $paymentModel->getEntityTable()), 'p.entity_type_id = ' . $paymentModel->getEntityType()->getId() . ' AND p.parent_id = e.entity_id', array())
                ->join(array('pm' => $methodAttr->getBackendTable()), 'p.entity_id = pm.entity_id AND pm.attribute_id = ' . $methodAttr->getId() . ' AND ' . $this->_getConditionSql('pm.value', array('in' => $this->_paymentMethodCodes)), array())
                ->join(array('pmno' => $poNumberAttr->getBackendTable()), 'p.entity_id = pmno.entity_id AND pmno.attribute_id = ' . $poNumberAttr->getId(), array())
                ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ))
                ->group(array($this->_periodFormat, 'pmno.value'));
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
}