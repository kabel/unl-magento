<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Abstract
    extends Unl_Core_Block_Adminhtml_Widget_Grid_Multicontainer
{
    protected $_blockGroup = 'unl_core';

    public function __construct()
    {
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
}
