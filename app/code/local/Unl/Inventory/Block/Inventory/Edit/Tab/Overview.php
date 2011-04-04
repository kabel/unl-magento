<?php

class Unl_Inventory_Block_Inventory_Edit_Tab_Overview
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    public function getQtyOnHand()
    {
        $qty = $this->getProduct()->getStockItem()->getQty() * 1;
        $collection = Mage::getResourceModel('unl_inventory/index_collection');
        $collection->getQtyOnHandSelect();
        $collection->load();

        $row = current($collection->getItems());

        if ($this->getProduct()->getAuditInventory()) {
            if ($row) {
                return $row->getQty();
            }

            return 0;
        }

        return $qty;
    }

    public function getIsInStock()
    {
        $options = Mage::getSingleton('cataloginventory/source_stock')->toOptionArray();
        $value = $this->getProduct()->getStockItem()->getIsInStock();

        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return $value;
    }

    public function getTabLabel()
    {
        return Mage::helper('unl_inventory')->__('Product Overview');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}