<?php

class Unl_Payment_Model_Observer
{
    public function onPrepareGrid($observer)
    {
        $grid = $observer->getEvent()->getGrid();
        //Do Actions Based on Block Type

        $type = 'Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid';
        if ($grid instanceof $type) {
            $grid->addColumnAfter('payment_account', array(
                'header'        => Mage::helper('unl_payment')->__('Payment Account'),
                'type'          => 'options',
                'options'       => Mage::getModel('unl_payment/account_source')->toOptionHash(),
                'index'         => 'unl_payment_account',
            ), 'sku');

            return $this;
        }
    }
}
