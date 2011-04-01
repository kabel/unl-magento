<?php

class Unl_Inventory_Block_Products extends Mage_Adminhtml_Block_Widget_Container
{
	/**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unl/inventory/products.phtml');
    }

    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('unl_inventory/products_grid', 'product.grid'));
        return parent::_prepareLayout();
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}