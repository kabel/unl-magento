<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridBursar');
        $this->setStoreSwitcherVisibility(false);
        $this->setSubtotalVisibility(true);
        //$this->setExportVisibility(false);
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('unl_core/bursar_collection');
    }

    protected function _prepareColumns()
    {
        $this->addColumn('group', array(
            'header'    =>Mage::helper('reports')->__('Merchant'),
            'index'     =>'merchant'
        ));
        
        $this->addColumn('orders', array(
            'header'    =>Mage::helper('reports')->__('Number of Orders'),
            'index'     =>'orders',
            'total'     =>'sum',
            'type'      =>'number'
        ));

        $currency_code = $this->getCurrentCurrencyCode();

        /*$this->addColumn('subtotal', array(
            'header'    =>Mage::helper('reports')->__('Subtotal'),
            'type'      =>'currency',
            'currency_code' => $currency_code,
            'index'     =>'subtotal',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));*/

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

        $this->addExportType('*/*/exportBursarCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportBursarExcel', Mage::helper('reports')->__('Excel'));

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
        //$totalObj = new Mage_Reports_Model_Totals();
        //Replaces the original totals counter
        $columns = array();
        foreach ($this->getColumns() as $col) {
            $columns[$col->getIndex()] = array("total" => $col->getTotal(), "value" => 0);
        }
        
        $count = 0;
        $totalCollection = Mage::getResourceModel('reports/order_collection');
        $totalCollection->setDateRange($from, $to)
            ->setStoreIds(array())
            ->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
            ->load();
        foreach ($totalCollection as $item) {
            $data = $item->getData();
            foreach ($columns as $field=>$a){
                if ($field !== '') {
                    $columns[$field]['value'] = $columns[$field]['value'] + (isset($data[$field]) ? $data[$field] : 0);
                }
            }
            $count++;
        }
        
        $data = array();
        foreach ($columns as $field=>$a)
        {
            if ($a['total'] == 'avg') {
                if ($field !== '') {
                    if ($count != 0) {
                        $data[$field] = $a['value']/$count;
                    } else {
                        $data[$field] = 0;
                    }
                }
            } else if ($a['total'] == 'sum') {
                if ($field !== '') {
                    $data[$field] = $a['value'];
                }
            } else if (strpos($a['total'], '/') !== FALSE) {
                if ($field !== '') {
                    $data[$field] = 0;
                }
            }
        }

        $totals = new Varien_Object();
        $totals->setData($data);
        //end totals
        $this->setTotals($totals);
        $this->addGrandTotals($this->getTotals());
        $report = $this->getCollection()->getReport($from, $to);
        if (count($report)) {
            $groupInvoiced = 0;
            foreach ($report as $row) {
                $groupInvoiced += $row->getInvoiced();
            }
            $data['invoiced'] -= $groupInvoiced;
            $order = Mage::getModel('sales/order');
            $data['merchant'] = 'Global Shipping/Refunds';
            $data['tax'] = 0;
            $data['total'] = $data['shipping'];
            $order->setData($data);
            $report->addItem($order);
        }
        return $report;
    }
    
    /*public function addGrandTotals($total)
    {
        
    }*/
}