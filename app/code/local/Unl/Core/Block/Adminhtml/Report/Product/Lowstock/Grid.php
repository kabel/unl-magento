<?php

class Unl_Core_Block_Adminhtml_Report_Product_Lowstock_Grid extends Mage_Adminhtml_Block_Report_Product_Lowstock_Grid
{
    /* Extends
     * @see Mage_Adminhtml_Block_Report_Product_Lowstock_Grid::setCollection()
     * by adding scope filter
     */
    public function setCollection($collection)
    {
        $storeIds = array();
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } else if ($this->getRequest()->getParam('store')) {
            $storeIds = (array)$this->getRequest()->getParam('store');
        }

        Mage::helper('unl_core')->addProductAdminScopeFilters($collection, $storeIds);

        return parent::setCollection($collection);
    }

    /* Extends
     * @see Mage_Adminhtml_Block_Report_Product_Lowstock_Grid::_prepareColumns()
     * by adding an additional column
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('status', array(
            'header'=> Mage::helper('catalog')->__('Status'),
            'width' => '70px',
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ), 'qty');

        parent::_prepareColumns();
    }
}
