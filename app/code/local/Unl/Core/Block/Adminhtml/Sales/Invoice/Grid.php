<?php

class Unl_Core_Block_Adminhtml_Sales_Invoice_Grid extends Mage_Adminhtml_Block_Sales_Invoice_Grid
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Sales_Invoice_Grid::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        Mage::helper('unl_core')->addAdminScopeFilters($collection);

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /* Extends
     * @see Mage_Adminhtml_Block_Sales_Invoice_Grid::_prepareColumns()
     * by adding an additional column
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('paid_at', array(
            'header'    => Mage::helper('sales')->__('Paid Date'),
            'index'     => 'paid_at',
            'type'      => 'datetime',
        ), 'created_at');

        return parent::_prepareColumns();
    }

    /* Overrides
     * @see Mage_Adminhtml_Block_Sales_Invoice_Grid::getRowUrl()
     * to fix an ACL error
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/invoice')) {
            return false;
        }

        return $this->getUrl('*/sales_invoice/view',
            array(
                'invoice_id'=> $row->getId(),
            )
        );
    }
}
