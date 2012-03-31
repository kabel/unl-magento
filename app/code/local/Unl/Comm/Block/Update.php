<?php

class Unl_Comm_Block_Update extends Mage_Adminhtml_Block_Abstract
{
    public function addParentCommTab()
    {
        /* @var $block Mage_Adminhtml_Block_Customer_Edit_Tabs */
        $block = $this->getParentBlock();

        if (Mage::registry('current_customer')->getId() && $this->_isAdminAllowed('customer/commqueue')) {
            $block->addTab('commqueue', array(
                'label'     => Mage::helper('unl_comm')->__('Communication'),
                'class'     => 'ajax',
                'url'       => $block->getUrl('*/customer_queue/customerGrid', array('_current'=>true)),
                'after'     => $this->_isAdminAllowed('newsletter/subscriber') ? 'newsletter' : 'wishlist',
            ));
        }

        return $this;
    }

    protected function _isAdminAllowed($resource)
    {
        return Mage::getSingleton('admin/session')->isAllowed($resource);
    }
}
