<?php

class Unl_Comm_Model_Observer
{
    public function scheduledSend($schedule)
    {
        $countOfQueue  = (int)Mage::getStoreConfig('communication/queue/count');
        $countOfSubscritions = (int)Mage::getStoreConfig('communication/queue/message_count');

        $collection = Mage::getModel('unl_comm/queue')->getCollection()
            ->setPageSize($countOfQueue)
            ->setCurPage(1)
            ->addOnlyForSendingFilter()
            ->load();

         $collection->walk('sendPerRecipient', array($countOfSubscritions));
    }

    public function correctAdminBlocks($observer)
    {
        $block = $observer->getEvent()->getBlock();
        //Do Actions Based on Block Type

        if ($block instanceof Mage_Adminhtml_Block_Customer_Grid) {
            if (Mage::getSingleton('admin/session')->isAllowed('customer/commqueue')) {
                $block->getMassactionBlock()->addItem('comm_queue', array(
                    'label'    => Mage::helper('customer')->__('Queue Message'),
                 	'url'      => $block->getUrl('unl_comm/queue/massCustomer')
                ));
            }
            return;
        }
    }

    public function onBlockBeforeToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();
        //Do Actions Based on Block Type

        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs) {
            if (Mage::getSingleton('admin/session')->isAllowed('customer/commqueue')) {
                $block->addTab('commqueue', array(
                    'label'     => Mage::helper('unl_comm')->__('Communication'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('unl_comm/queue/customerGrid', array('_current'=>true)),
                    'after'     => Mage::getSingleton('admin/session')->isAllowed('newsletter/subscriber') ? 'newsletter' : 'wishlist',
                ));
            }
            return;
        }
    }
}