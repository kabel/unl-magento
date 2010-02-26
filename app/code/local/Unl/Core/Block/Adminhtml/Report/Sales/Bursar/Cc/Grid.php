<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc_Grid extends Mage_Adminhtml_Block_Report_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('gridBursarCc');
        $this->setStoreSwitcherVisibility(false);
        $this->setSubtotalVisibility(true);
        //$this->setExportVisibility(false);
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('unl_core/bursar_cc_collection');
    }

    protected function _prepareColumns()
    {
        $this->addColumn('group', array(
            'header'    =>Mage::helper('reports')->__('Merchant'),
            'index'     =>'merchant'
        ));

        $currency_code = $this->getCurrentCurrencyCode();

        $this->addColumn('subtotal', array(
            'header'    =>Mage::helper('reports')->__('Subtotal'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'subtotal',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('tax', array(
            'header'    =>Mage::helper('reports')->__('Tax'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'tax',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('shipping', array(
            'header'    =>Mage::helper('reports')->__('Shipping'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'shipping',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        /*$this->addColumn('discount', array(
            'header'    =>Mage::helper('reports')->__('Discounts'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'discount',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));*/

        $this->addColumn('total', array(
            'header'    =>Mage::helper('reports')->__('Total'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'total',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('invoiced', array(
            'header'    =>Mage::helper('reports')->__('Invoiced'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'invoiced',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addColumn('refunded', array(
            'header'    =>Mage::helper('reports')->__('Refunded'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'refunded',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addExportType('*/*/exportCcCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCcExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
    
    public function getReport($from, $to)
    {
        if ($from == '') {
            $from = $this->getFilter('report_from');
        }
        if ($to == '') {
            $to = $this->getFilter('report_to');
        }
        
        $totalObj = Mage::getModel('reports/totals');
        $totals = $totalObj->countTotals($this, $from, $to);
        
        $totalCollection = Mage::getResourceModel('unl_core/bursar_cc_collection');
        $totalCollection->setDateRange($from, $to)->initSelect(array(), true)->load();
        foreach ($totalCollection as $item) {
            $data = $item->getData();
            $totals['shipping'] += (isset($data['shipping']) ? $data['shipping'] : 0);
            $totals['refunded'] += (isset($data['refunded']) ? $data['refunded'] : 0);
        }
        
        $this->setTotals($totals);
        $this->addGrandTotals($this->getTotals());
        
        $report = $this->getCollection()->getReport($from, $to);
        if (count($report)) {
            $order = Mage::getModel('sales/order');
            $data = array(
                'entity_id' => 0,
                'merchant'  => 'Global Shipping/Refunds',
                'tax'       => 0,
                'subtotal'  => 0,
                'shipping'  => $totals['shipping'],
                'total'     => $totals['shipping'],
                'invoiced'  => $totals['invoiced'],
                'refunded'  => $totals['refunded']
            );
            $groupInvoiced = 0;
            foreach ($report as $row) {
                $groupInvoiced += $row->getInvoiced();
            }
            $data['invoiced'] -= $groupInvoiced;
            $order->setData($data);
            $report->addItem($order);
        }
        
        return $report;
    }
}