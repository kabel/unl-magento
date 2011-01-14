<?php

class Unl_Comm_Block_Queue_Preview extends Mage_Adminhtml_Block_Widget
{
    protected function _toHtml()
    {
        $queue = Mage::getModel('unl_comm/queue');

        if ($id = (int)$this->getRequest()->getParam('id')) {
            $queue->load($id);
        } else {
            $queue->setMessageType($this->getRequest()->getParam('type'));
            $queue->setMessageText($this->getRequest()->getParam('text'));
            $queue->setMessageStyles($this->getRequest()->getParam('styles'));
        }

        if ($storeId = (int)$this->getRequest()->getParam('store_id')) {
            $queue->setStoreId($storeId);
        }

        Varien_Profiler::start("comm_queue_proccessing");

        $templateProcessed = $queue->getProcessedTemplate(array());

        if ($queue->isPlain()) {
            $templateProcessed = '<pre>' . htmlspecialchars($templateProcessed) . '</pre>';
        }

        Varien_Profiler::stop("comm_queue_proccessing");

        return $templateProcessed;
    }
}