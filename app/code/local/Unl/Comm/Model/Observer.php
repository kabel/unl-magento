<?php

class Unl_Comm_Model_Observer
{
    /**
     * CRON method to send queued messages
     *
     * @param Mage_Cron_Model_Schedule $schedule The saved schedule instance
     */
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

    /**
     * An <i>adminhtml</i> event handler for the <code>adminhtml_block_html_before</code> event
     *
     * @param Varien_Event_Observer $observer
     */
    public function correctAdminBlocks($observer)
    {
        $block = $observer->getEvent()->getBlock();
        //Do Actions Based on Block Type

        $type = 'Mage_Adminhtml_Block_Customer_Grid';
        if ($block instanceof $type) {
            if (Mage::getSingleton('admin/session')->isAllowed('customer/commqueue')) {
                $block->getMassactionBlock()->addItem('comm_queue', array(
                    'label'    => Mage::helper('unl_comm')->__('Queue Message'),
                 	'url'      => $block->getUrl('*/customer_queue/massCustomer')
                ));
            }
            return;
        }
    }
}
