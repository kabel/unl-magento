<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Co_Grid extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Abstract
{
    public function getResourceCollectionName()
    {
        return ($this->getFilterData()->getData('report_type') == 'updated_at_order')
            ? 'unl_core/report_bursar_co_updatedat_collection'
            : 'unl_core/report_bursar_co_collection';
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'        => Mage::helper('sales')->__('Period'),
            'index'         => 'period',
            'width'         => 100,
            'sortable'      => false,
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'  => Mage::helper('adminhtml')->__('Total')
        ));
        
        $this->addColumn('merchant', array(
            'header'    => Mage::helper('sales')->__('Merchant'),
            'index'     => 'merchant',
            'sortable'  => false
        ));
        
        $this->addColumn('po_number', array(
            'header'    => Mage::helper('sales')->__('Cost Object'),
            'index'     => 'po_number',
            'sortable'  => false
        ));
        
        /* Space Wasters
        $this->addColumn('orders_count', array(
            'header'    => Mage::helper('sales')->__('# of Orders'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'sortable'  => false
        ));

        $this->addColumn('total_qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Items Ordered'),
            'index'     => 'total_qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));
        */

        $currency_code = $this->getCurrentCurrencyCode();

        /* Broken Total?
        $this->addColumn('base_profit_amount', array(
            'header'        => Mage::helper('sales')->__('Profit'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_profit_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));
        */

        $this->addColumn('base_subtotal_amount', array(
            'header'        => Mage::helper('sales')->__('Subtotal'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_subtotal_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('base_tax_amount', array(
            'header'        => Mage::helper('sales')->__('Tax'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_tax_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('base_shipping_amount', array(
            'header'        => Mage::helper('sales')->__('Shipping'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_shipping_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('base_discount_amount', array(
            'header'        => Mage::helper('sales')->__('Discounts'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_discount_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('base_grand_total_amount', array(
            'header'        => Mage::helper('sales')->__('Total'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_grand_total_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('base_invoiced_amount', array(
            'header'        => Mage::helper('sales')->__('Invoiced'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_invoiced_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));
        
        $this->addColumn('base_payout_amount', array(
            'header'        => Mage::helper('sales')->__('Payout'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_payout_amount',
            'sortable'      => false
        ));

        $this->addColumn('base_refunded_tax_amount', array(
            'header'        => Mage::helper('sales')->__('Refunded Tax'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_refunded_tax_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('base_refunded_amount', array(
            'header'        => Mage::helper('sales')->__('Refunded'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_refunded_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));
        
        $this->addColumn('base_canceled_tax_amount', array(
            'header'        => Mage::helper('sales')->__('Canceled Tax'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_canceled_tax_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('base_canceled_amount', array(
            'header'        => Mage::helper('sales')->__('Canceled'),
            'type'          => 'currency',
            'currency_code' => $currency_code,
            'index'         => 'base_canceled_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addExportType('*/*/exportCoCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCoExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}