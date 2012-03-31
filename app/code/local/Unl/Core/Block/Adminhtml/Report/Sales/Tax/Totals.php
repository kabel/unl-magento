<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Totals extends Unl_Core_Block_Adminhtml_Widget_Grid_Multicontainer
{
    protected $_blockGroup = 'unl_core';

    public function __construct()
    {
        $this->_controller = 'adminhtml_report_sales_tax_totals';
        $this->_headerText = Mage::helper('reports')->__('Tax Report: Totals');
        parent::__construct();

        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('reports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));

        $this->addGrid('paid', "{$this->_blockGroup}/{$this->_controller}_paid", array(
        	'grid_header' => Mage::helper('reports')->__('Paid')
        ));

        $this->addGrid('refunded', "{$this->_blockGroup}/{$this->_controller}_refunded", array(
        	'grid_header' => Mage::helper('reports')->__('Refunded')
        ));
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/totals', array('_current' => true));
    }
}
