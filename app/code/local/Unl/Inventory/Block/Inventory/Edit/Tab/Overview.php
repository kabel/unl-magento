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
        if (Mage::helper('unl_inventory')->getIsAuditInventory($this->getProduct())) {
            return Mage::helper('unl_inventory')->getQtyOnHand($this->getProduct()->getId()) * 1;
        }

        return $this->getProduct()->getStockItem()->getQty() * 1;
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