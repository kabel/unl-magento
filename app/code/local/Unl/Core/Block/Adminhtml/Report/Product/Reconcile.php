<?php

class Unl_Core_Block_Adminhtml_Report_Product_Reconcile extends Unl_Core_Block_Adminhtml_Widget_Grid_Multicontainer
{
    protected $_blockGroup = 'unl_core';

    public function __construct()
    {
        $this->_controller = 'adminhtml_report_product_reconcile';
        $this->_headerText = Mage::helper('reports')->__('Product Reconciliation');
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
