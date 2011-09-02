<?php

class Unl_Core_Block_Adminhtml_Report_Customer_Orderaddress extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_customer_orderaddress';
        $this->_headerText = Mage::helper('reports')->__('Order Address');
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
        return $this->getUrl('*/*/orderaddress', array('_current' => true));
    }
}
