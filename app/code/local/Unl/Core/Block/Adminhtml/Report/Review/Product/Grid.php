<?php

class Unl_Core_Block_Adminhtml_Report_Review_Product_Grid extends Mage_Adminhtml_Block_Report_Review_Product_Grid
{
    /* Extends
     * @see Mage_Adminhtml_Block_Report_Review_Product_Grid::setCollection()
     * by adding scope filter
     */
    public function setCollection($collection)
    {
        Mage::helper('unl_core')->addProductAdminScopeFilters($collection);

        return parent::setCollection($collection);
    }
}
