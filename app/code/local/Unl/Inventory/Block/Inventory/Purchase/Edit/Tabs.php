<?php

class Unl_Inventory_Block_Inventory_Purchase_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('inventory_purchase_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('unl_inventory')->__('Purchase Details'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('info', array(
            'label'     => Mage::helper('unl_inventory')->__('Overview'),
            'content'   => $this->getLayout()->createBlock('unl_inventory/inventory_purchase_edit_tab_info')->initForm()->toHtml(),
        ));

        $this->addTab('audit', array(
            'label'     => Mage::helper('unl_inventory')->__('Associated Audits'),
            'class'     => 'ajax',
            'url'       => $this->getUrl('*/*/auditGrid', array('_current' => true)),
        ));

        return parent::_beforeToHtml();
    }
}
