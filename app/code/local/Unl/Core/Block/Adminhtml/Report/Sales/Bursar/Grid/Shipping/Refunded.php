<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping
{
    protected function _prepareColumns()
    {
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumnAfter('total_adjustments', array(
            'header'        => Mage::helper('sales')->__('Adjustments'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_adjustments',
            'total'         => 'sum',
            'sortable'      => false
        ), 'total_revenue');

        return parent::_prepareColumns();
    }
}
