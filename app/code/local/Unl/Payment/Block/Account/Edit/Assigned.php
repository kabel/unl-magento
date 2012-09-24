<?php

class Unl_Payment_Block_Account_Edit_Assigned extends Mage_Adminhtml_Block_Widget_Accordion
{
    /**
     * Add Assigned products accordion to layout
     *
     */
    protected function _prepareLayout()
    {
        $model = Mage::registry('current_account');

        if (is_null($model->getId())) {
            return $this;
        }

        $this->setId('paymentaccount_assigned_grid');

        $this->addItem('account_assign', array(
            'title'         => Mage::helper('tag')->__('Products Assigned'),
            'ajax'          => true,
            'content_url'   => $this->getUrl('*/*/assigned', array('id'=>$model->getId())),
        ));
        return parent::_prepareLayout();
    }
}
