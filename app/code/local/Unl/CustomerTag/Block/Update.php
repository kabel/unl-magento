<?php

class Unl_CustomerTag_Block_Update extends Mage_Adminhtml_Block_Abstract
{
    public function addParentTagsTab()
    {
        /* @var $block Mage_Adminhtml_Block_Customer_Edit_Tabs */
        $block = $this->getParentBlock();

        if (Mage::registry('current_customer')->getId()) {
            $block->addTab('customertag', array(
                'label'     => Mage::helper('unl_customertag')->__('Customer Tags'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('*/customerTag_customer/grid', array('_current'=>true)),
                'after'     => 'account'
            ));
        }

        return $this;
    }

    public function addParentProductTagsTab()
    {
        /* @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs */
        $block = $this->getParentBlock();

        if (!($setId = $block->getProduct()->getAttributeSetId())) {
            $setId = Mage::app()->getRequest()->getParam('set', null);
        }

        if ($setId) {
            $block->addTab('customertag', array(
                'label'     => Mage::helper('unl_customertag')->__('Access Tags'),
                'class'     => 'ajax',
                'url'       => $block->getUrl('*/customerTag_product/grid', array('_current'=>true)),
                'after'     => 'inventory'
            ));
        }

        return $this;
    }
}
