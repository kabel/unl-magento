<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co_Grid extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Abstract
{
    public function getResourceCollectionName()
    {
        return ($this->getFilterData()->getData('report_type') == 'updated_at_order')
            ? 'unl_core/report_reconcile_co_updatedat_collection'
            : 'unl_core/report_reconcile_co_collection';
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
            'totals_label'  => Mage::helper('sales')->__('Total'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'increment_id',
            'sortable'  => false
        ));
        
        $this->addColumn('po_number', array(
            'header'    => Mage::helper('sales')->__('Cost Object'),
            'index'     => 'po_number',
            'sortable'  => false
        ));

        $this->addColumn('total_qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Sales Items'),
            'index'     => 'total_qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));

        $this->addColumn('total_qty_invoiced', array(
            'header'    => Mage::helper('sales')->__('Items'),
            'index'     => 'total_qty_invoiced',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false,
            'visibility_filter' => array('show_actual_columns')
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('total_income_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Total'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_income_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_revenue_amount', array(
            'header'        => Mage::helper('sales')->__('Revenue'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_revenue_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_profit_amount', array(
            'header'        => Mage::helper('sales')->__('Profit'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_profit_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_invoiced_amount', array(
            'header'        => Mage::helper('sales')->__('Invoiced'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_invoiced_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_paid_amount', array(
            'header'        => Mage::helper('sales')->__('Paid'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_paid_amount',
            'total'         => 'sum',
            'sortable'      => false,
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_refunded_amount', array(
            'header'        => Mage::helper('sales')->__('Refunded'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_refunded_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_tax_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Tax'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_tax_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_tax_amount_actual', array(
            'header'        => Mage::helper('sales')->__('Tax'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_tax_amount_actual',
            'total'         => 'sum',
            'sortable'      => false,
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_discount_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Discount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_discount_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_discount_amount_actual', array(
            'header'        => Mage::helper('sales')->__('Discount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_discount_amount_actual',
            'total'         => 'sum',
            'sortable'      => false,
            'visibility_filter' => array('show_actual_columns')
        ));

        $this->addColumn('total_canceled_amount', array(
            'header'        => Mage::helper('sales')->__('Canceled'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_canceled_amount',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addExportType('*/*/exportCoCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCoExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}