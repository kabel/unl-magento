<?php

class Unl_Core_Block_Adminhtml_Sales_Picklist_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unl/picklist/grid.phtml');
        $this->setId('gridPicklist');
        //$this->setStoreSwitcherVisibility(false);
        //$this->setSubtotalVisibility(true);
        //$this->setExportVisibility(false);
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getChild('store_switcher')->setTemplate('unl/store/switcher.phtml');
    }
    
    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);
        
        if (is_string($filter)) {
            $data = array();
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
            $filter = $data;
        }
        
        if (!is_null($filter)) {
            if (isset($filter['report_from'])) {
                $filter['report_to'] = $filter['report_from'];
                $filter['report_period'] = 'day';
                $this->getRequest()->setParam($this->getVarNameFilter(), $filter);
            }
        }
        
        parent::_prepareCollection();
        $this->getCollection()->initReport('unl_core/picklist_collection');
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('order', array(
            'header'    =>Mage::helper('sales')->__('Order #'),
            'index'     =>'order_num'
        ));
        
        $this->addColumn('group', array(
            'header'    =>Mage::helper('sales')->__('Store'),
            'index'     =>'merchant'
        ));
        
        $this->addColumn('sku', array(
            'header'    =>Mage::helper('sales')->__('SKU'),
            'index'     =>'sku'
        ));
        
        $this->addColumn('qty', array(
            'header'    =>Mage::helper('sales')->__('Qty'),
            'index'     =>'qty',
            'type'      => 'number'
        ));
        
        $this->addColumn('product', array(
            'header'    =>Mage::helper('sales')->__('Product'),
            'index'     =>'name'
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$row->getOrderId()));
    }
}