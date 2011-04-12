<?php

class Unl_Inventory_Block_Report_Valuation extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize Grid Container
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'unl_inventory';
        $this->_controller = 'report_valuation';
        $this->_headerText = Mage::helper('unl_inventory')->__('Inventory Valuation');
        parent::__construct();
        $this->setTemplate('unl/inventory/valuation.phtml');
        $this->_removeButton('add');
    }

    protected function _prepareLayout()
    {
        $this->setChild('store_switcher',
            $this->getLayout()->createBlock('adminhtml/store_switcher')
                ->setUseConfirm(false)
                ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
                ->setTemplate('unl/report/store/switcher.phtml')
        );

        return parent::_prepareLayout();
    }

    /**
     * Return store switcher html
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }
}
