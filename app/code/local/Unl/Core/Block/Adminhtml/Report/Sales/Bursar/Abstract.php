<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Abstract
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

        $this->addGrid('products_paid', "{$this->_blockGroup}/{$this->_controller}_grid_products_paid", array(
        	'grid_header' => Mage::helper('reports')->__('Products Paid')
        ));

        $this->addGrid('products_refunded', "{$this->_blockGroup}/{$this->_controller}_grid_products_refunded", array(
        	'grid_header' => Mage::helper('reports')->__('Products Refunded')
        ));

        $this->addGrid('shipping_paid', "{$this->_blockGroup}/{$this->_controller}_grid_shipping_paid", array(
        	'grid_header' => Mage::helper('reports')->__('Shipping Paid')
        ));

        $this->addGrid('shipping_refunded', "{$this->_blockGroup}/{$this->_controller}_grid_shipping_refunded", array(
        	'grid_header' => Mage::helper('reports')->__('Shipping Refunded')
        ));
    }
}
