<?php

class Unl_Payment_Model_Observer
{
    public function setQuoteItemPaymentAccount($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $item = $observer->getEvent()->getItem();

        $item->setUnlPaymentAccount($product->getUnlPaymentAccount());
    }

    public function correctAdminBlocks($observer)
    {
        $block = $observer->getEvent()->getBlock();
        //Do Actions Based on Block Type

        $type = 'Unl_Core_Block_Adminhtml_Report_Filter_Form_Products';
        if ($block instanceof $type) {
            $filters = $block->getFilterData();
            $fieldset = $block->getForm()->getElement('base_fieldset');

            if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
                $fieldset->addField('show_account', 'select', array(
                    'name'      => 'show_account',
                    'options'   => array(
                        '1' => Mage::helper('reports')->__('Yes'),
                        '0' => Mage::helper('reports')->__('No')
                    ),
                    'label'     => Mage::helper('unl_payment')->__('Show Payment Account'),
                    'value'     => isset($filters['show_account']) ? 1 : 0,
                ));
            }

            return $this;
        }
    }

    public function onBlockBeforeToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();
        //Do Actions Based on Block Type

        $type = 'Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid';
        if ($block instanceof $type) {
            $block->addColumnAfter('payment_account', array(
                'header'        => Mage::helper('unl_payment')->__('Payment Account'),
                'type'          => 'options',
                'options'       => Mage::getModel('unl_payment/account_source')->toOptionHash(),
                'index'         => 'unl_payment_account',
                'sortable'      => false,
                'visibility_filter' => array('show_account')
            ), 'sku');

            return $this;
        }
    }
}
