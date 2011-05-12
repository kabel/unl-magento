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

        if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
            $order_items = Mage::getModel('sales/order_item')->getCollection();
            /* @var $order_items Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $select = $order_items->getSelect()->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'))
                ->where('source_store_view IN (?)', $scope)
                ->group('order_id');

            $collection->getSelect()
                ->joinInner(array('scope' => $select), 'main_table.order_id = scope.order_id', array());
        }

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
