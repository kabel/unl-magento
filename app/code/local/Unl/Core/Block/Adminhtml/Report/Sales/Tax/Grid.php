<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Grid extends Mage_Adminhtml_Block_Report_Sales_Tax_Grid
{
    /* Extends
     * @see Mage_Adminhtml_Block_Report_Sales_Tax_Grid::_prepareColumns()
     * by adding extra columns
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('city', array(
            'header'    => Mage::helper('sales')->__('City'),
            'index'     => 'city',
            'type'      => 'string',
            'sortable'  => false
        ), 'code');

        $this->addColumnAfter('county', array(
            'header'    => Mage::helper('sales')->__('County'),
            'index'     => 'county',
            'type'      => 'string',
            'sortable'  => false
        ), 'city');

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $this->addColumnAfter('base_sales_amount_sum', array(
            'header'        => Mage::helper('sales')->__('Sales Amount'),
            'index'         => 'base_sales_amount_sum',
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'sortable'      => false
        ), 'county');

        parent::_prepareColumns();

        $this->getColumn('orders_count')->setTotal(false);

        return $this;
    }
}
