<?php

class Unl_Core_Block_Adminhtml_Sales_Creditmemo_Grid extends Mage_Adminhtml_Block_Sales_Creditmemo_Grid
{
    /* Extends
     * @see Mage_Adminhtml_Block_Sales_Creditmemo_Grid::setCollection()
     * by adding a scope filter
     */
    public function setCollection($collection)
    {
        Mage::helper('unl_core')->addAdminScopeFilters($collection);

        return parent::setCollection($collection);
    }

    /* Overrides
     * @see Mage_Adminhtml_Block_Sales_Creditmemo_Grid::getRowUrl()
     * to fix an ACL error
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/creditmemo')) {
            return false;
        }

        return $this->getUrl('*/sales_creditmemo/view',
            array(
                'creditmemo_id'=> $row->getId(),
            )
        );
    }
}
