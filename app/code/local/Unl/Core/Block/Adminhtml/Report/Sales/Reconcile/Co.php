<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_sales_reconcile_co';
        $this->_headerText = Mage::helper('reports')->__('Reconcile Report: Cost Object');
        parent::__construct();
        $this->setTemplate('report/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('reports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }
    
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/co', array('_current' => true));
    }
}