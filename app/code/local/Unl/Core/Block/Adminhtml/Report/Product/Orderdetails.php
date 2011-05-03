<?php

class Unl_Core_Block_Adminhtml_Report_Product_Orderdetails extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_product_orderdetails';
        $this->_headerText = Mage::helper('reports')->__('Order Details');
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
        return $this->getUrl('*/*/orderdetails', array('_current' => true));
    }
}
