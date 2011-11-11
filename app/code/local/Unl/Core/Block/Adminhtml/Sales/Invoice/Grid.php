<?php

class Unl_Core_Block_Adminhtml_Sales_Invoice_Grid extends Mage_Adminhtml_Block_Sales_Invoice_Grid
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Sales_Invoice_Grid::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Sales_Model_Mysql4_Order_Invoice_Grid_Collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        Mage::helper('unl_core')->addAdminScopeFilters($collection);

        $collection->join('sales/order_payment', 'main_table.order_id = `sales/order_payment`.parent_id', array('method'));

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

        $this->addColumnAfter('payment_method', array(
            'header'    => Mage::helper('sales')->__('Payment Method'),
            'index'     => 'method',
            'type'      => 'options',
            'options'   => Mage::helper('unl_core')->getActivePaymentMethodOptions(false),
        ), 'billing_name');

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
