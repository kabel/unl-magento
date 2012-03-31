<?php

class Unl_Core_Block_Adminhtml_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
    /* Extends
     * @see Mage_Adminhtml_Block_Sales_Shipment_Grid::setCollection()
     * by adding scope filter
     */
    public function setCollection($collection)
    {
        Mage::helper('unl_core')->addAdminScopeFilters($collection);

        return parent::setCollection($collection);
    }

    /* Extends
     * @see Mage_Adminhtml_Block_Sales_Shipment_Grid::_prepareColumns()
     * by adding additional columns
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('shipping_description', array(
            'header' => Mage::helper('sales')->__('Shipping Carrier/Method'),
            'index' => 'shipping_description',
            'type'  => 'text',
        ), 'increment_id');

        $this->addColumnAfter('base_shipping_amount', array(
            'header' => Mage::helper('sales')->__('Shipping Amount'),
            'index' => 'base_shipping_amount',
            'type'  => 'currency',
            'currency_code' => Mage::app()->getStore()->getBaseCurrencyCode(),
        ), 'shipping_name');

        return parent::_prepareColumns();
    }

    /* Overrides
     * @see Mage_Adminhtml_Block_Sales_Shipment_Grid::getRowUrl()
     * to fix an ACL error
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/shipment')) {
            return false;
        }

        return $this->getUrl('*/sales_shipment/view',
            array(
                'shipment_id'=> $row->getId(),
            )
        );
    }
}
