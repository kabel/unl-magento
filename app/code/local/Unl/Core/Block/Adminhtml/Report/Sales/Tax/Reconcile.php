<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Reconcile extends Unl_Core_Block_Adminhtml_Widget_Grid_Multicontainer
{
    protected $_blockGroup = 'unl_core';

    public function __construct()
    {
        $this->_controller = 'adminhtml_report_sales_tax_reconcile';
        $this->_headerText = Mage::helper('reports')->__('Tax Report: Reconcile');
        parent::__construct();

        $this->setTemplate('widget/grid/container.phtml');

        $this->addGrid('paid', "{$this->_blockGroup}/{$this->_controller}_paid", array(
        	'grid_header' => Mage::helper('reports')->__('Paid')
        ));

        $this->addGrid('refunded', "{$this->_blockGroup}/{$this->_controller}_refunded", array(
        	'grid_header' => Mage::helper('reports')->__('Refunded')
        ));
    }
}
