<?php

class Unl_Comm_Block_Queue_Preview extends Mage_Adminhtml_Block_Widget
{
    protected function _toHtml()
    {
        /* @var $queue Unl_Comm_Model_Queue */
        $queue = Mage::getModel('unl_comm/queue');

        if ($id = (int)$this->getRequest()->getParam('id')) {
            $queue->load($id);
        } else {
            $queue->setMessageType($this->getRequest()->getParam('type'));
            $queue->setMessageText($this->getRequest()->getParam('text'));
            $queue->setMessageStyles($this->getRequest()->getParam('styles'));
        }

        $template = $queue->getEmailTemplate();

        $storeId = (int)$this->getRequest()->getParam('store_id');
        if (!$storeId) {
            $storeId = Mage::app()->getDefaultStoreView()->getId();
        }

        Varien_Profiler::start("comm_queue_proccessing");
        $vars = array();

        $vars['customer'] = Mage::getModel('customer/customer');
        if ($id = (int)$this->getRequest()->getParam('customer')) {
            $vars['customer']->load($id);
        }

        $template->emulateDesign($storeId);
        $templateProcessed = $template->getProcessedTemplate($vars);
        $template->revertDesign();

        if ($queue->isPlain()) {
            $templateProcessed = '<pre>' . htmlspecialchars($templateProcessed) . '</pre>';
        }

        Varien_Profiler::stop("comm_queue_proccessing");

        return $templateProcessed;
    }
}
