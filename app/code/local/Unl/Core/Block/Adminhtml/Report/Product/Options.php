<?php

class Unl_Core_Block_Adminhtml_Report_Product_Options extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $product = Mage::registry('current_product');

        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_product_options';
        $this->_headerText = Mage::helper('reports')->__('Ordered "%s" Items with Options', $product->getName());
        parent::__construct();
        $this->setTemplate('unl/report/product/options/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('params_form_submit', array(
            'label'     => Mage::helper('reports')->__('Update Report'),
            'onclick'   => 'paramsFormSubmit()'
        ));
    }

    public function getParamsUrl()
    {
        $this->getRequest()->setParam('params', null);
        return $this->getUrl('*/*/options', array('_current' => true));
    }
}
