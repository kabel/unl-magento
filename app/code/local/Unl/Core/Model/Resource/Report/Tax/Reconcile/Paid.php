<?php

class Unl_Core_Model_Resource_Report_Tax_Reconcile_Paid extends Mage_Tax_Model_Resource_Sales_Order_Tax_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $codeCases = Mage::helper('unl_core')->getTaxCodeCases();

        $this
            ->addFilterToMap('ordernum', 'o.increment_id')
            ->addFilterToMap('period', 'i.paid_at')
            ->addFilterToMap('method', 'p.method')
            ->addFilterToMap('city', 'pf.name')
            ->addFilterToMap('county', 'cf.name')
            ->addFilterToMap('real_code', $this->getConnection()->getCaseSql('', $codeCases, 'code'));

        $cityFipsCases = Mage::helper('unl_core')->getCityFipsCases();
        $countyFipsCases = Mage::helper('unl_core')->getCountyFipsCases();
        $innerSelect = $this->_getInnerSelect();

        $this->getSelect()
            ->columns(array('real_code' => $this->getConnection()->getCaseSql('', $codeCases, 'code')))
            ->join(array('o' => $this->getTable('sales/order')), 'o.entity_id = main_table.order_id', array('ordernum' => 'increment_id'))
            ->join(array('i' => $innerSelect), 'i.order_id = o.entity_id', array('period' => 'paid_at'))
            ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array('method'))
            ->joinLeft(array('pf' => $this->getTable('unl_core/tax_places')),
                $this->getConnection()->getCaseSql('', $cityFipsCases), array('city' => 'name'))
            ->joinLeft(array('cf' => $this->getTable('unl_core/tax_counties')),
                $this->getConnection()->getCaseSql('', $countyFipsCases), array('county' => 'name'));

        return $this;
    }

    protected function _getInnerMainTable()
    {
        return $this->getTable('sales/invoice');
    }

    protected function _getInnerDateColumn()
    {
        return 'paid_at';
    }

    protected function _getInnerSelect()
    {
        $innerSelect = $this->getConnection()->select()
            ->from($this->_getInnerMainTable(),
                array('paid_at' => sprintf('MIN(%s)', $this->_getInnerDateColumn()), 'order_id'))
            ->group('order_id');

        return $innerSelect;
    }
}
