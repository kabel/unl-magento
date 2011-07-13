<?php

class Unl_Comm_QueueController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Queue list action
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Communication Queue'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();

        $this->_setActiveMenu('customer/commqueue');

        $this->_addContent(
            $this->getLayout()->createBlock('unl_comm/queue', 'queue')
        );

        $this->_addBreadcrumb(Mage::helper('unl_comm')->__('Communication Queue'), Mage::helper('newsletter')->__('Communicaion Queue'));

        $this->renderLayout();
    }

    /**
     * Preview Newsletter template
     */
    public function previewAction()
    {
        $this->loadLayout('comm_queue_preview');
        $this->renderLayout();
    }

    /**
     * Queue list Ajax action
     */
    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('unl_comm/queue_grid')->toHtml());
    }

    public function customerGridAction()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');
        $customer->load($customerId);
        Mage::register('current_customer', $customer);

        $this->getResponse()->setBody($this->getLayout()->createBlock('unl_comm/customer_edit_tab_queue')->toHtml());
    }

    public function startAction()
    {
        $queue = Mage::getModel('unl_comm/queue')
            ->load($this->getRequest()->getParam('id'));
        if ($queue->getId()) {
            if (!in_array($queue->getQueueStatus(), array(
                Unl_Comm_Model_Queue::STATUS_NEVER,
                Unl_Comm_Model_Queue::STATUS_PAUSE
            ))) {
                $this->_redirect('*/*');
                return;
            }

            $queue->setQueueStartAt(Mage::getSingleton('core/date')->gmtDate())
                ->setQueueStatus(Unl_Comm_Model_Queue::STATUS_SENDING)
                ->save();
        }

        $this->_redirect('*/*');
    }

    public function pauseAction()
    {
        $queue = Mage::getModel('unl_comm/queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(), array(Unl_Comm_Model_Queue::STATUS_SENDING))) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(Unl_Comm_Model_Queue::STATUS_PAUSE);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function resumeAction()
    {
        $queue = Mage::getModel('unl_comm/queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(), array(Unl_Comm_Model_Queue::STATUS_PAUSE))) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(Unl_Comm_Model_Queue::STATUS_SENDING);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function cancelAction()
    {
        $queue = Mage::getModel('unl_comm/queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(), array(Unl_Comm_Model_Queue::STATUS_SENDING))) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(Unl_Comm_Model_Queue::STATUS_CANCEL);
        $queue->save();

        $this->_redirect('*/*');
    }

    public function deleteAction()
    {
        $queue = Mage::getModel('unl_comm/queue')
            ->load($this->getRequest()->getParam('id'));

        if (!in_array($queue->getQueueStatus(), array(Unl_Comm_Model_Queue::STATUS_NEVER))) {
            $this->_redirect('*/*');
            return;
        }

        $queue->delete();
        $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('Successfully removed queued message.'));

        $this->_redirectUrl('*/*');
    }

    public function sendingAction()
    {
        $model = Mage::getModel('unl_comm/observer');
        $model->scheduledSend(null);

        $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('Successfully force started queue processing.'));
        $this->_redirect('*/*');
    }

    public function massCustomerAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        $session = Mage::getSingleton('adminhtml/session');
        if (!is_array($customersIds)) {
            $session->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
            $this->_redirect('adminhtml/customer/');
            return;
        }
        $session->setCommCustomerIds($customersIds);

        $this->_redirect('*/*/edit');
    }

    protected function _initQueue()
    {
        $queue = Mage::getModel('unl_comm/queue');
        Mage::register('current_queue', $queue);

        $id = $this->getRequest()->getParam('id');
        $customersIds = $this->_getSession()->getCommCustomerIds();
        if (is_string($customersIds)) {
            $customersIds = explode(',', $customersIds);
        }

        if ($id) {
            $this->_getSession()->unsetData('comm_customer_ids');
            $queue = $queue->load($id);
            if (!$queue->getId()) {
                $this->_getSession()->addError($this->__('This queue no longer exists.'));
                $this->_redirect('*/*/');
                return false;
            }
        } elseif (empty($customersIds)) {
            $this->_redirect('adminhtml/customer/');
            return false;
        } else {
            $queue->setCustomerIds($customersIds);
        }

        return $queue;
    }

    public function editAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Communication Queue'));

        $queue = $this->_initQueue();
        if (!$queue) {
            return;
        }

        $this->_title($this->__('Edit Queue'));

        $this->loadLayout();

        $this->_setActiveMenu('customer/commqueue');

        $this->_addBreadcrumb(
            Mage::helper('unl_comm')->__('Communication Queue'),
            Mage::helper('unl_comm')->__('Communication Queue'),
            $this->getUrl('*/*')
        );
        $this->_addBreadcrumb(Mage::helper('unl_comm')->__('Edit Queue'), Mage::helper('unl_comm')->__('Edit Queue'));

        $this->renderLayout();
    }

    public function recipientsAction()
    {
        $this->_initQueue();
        $this->getResponse()->setBody($this->getLayout()->createBlock('unl_comm/queue_edit_tabs_recipients')->toHtml());
    }

    public function saveAction()
    {
        try {
            /* @var $queue Unl_Comm_Model_Queue */
            $queue = Mage::getModel('unl_comm/queue');
            $sessionIds = $this->_getSession()->getCommCustomerIds(true);
            $customer = $this->getRequest()->getParam('customer');
            if ($customer) {
                $customerIds = explode(',', $customer);
                $queue->setQueueStatus(Unl_Comm_Model_Queue::STATUS_NEVER);
            } else {
                $queue->load($this->getRequest()->getParam('id'));
            }

            if (!in_array($queue->getQueueStatus(),
                   array(Unl_Comm_Model_Queue::STATUS_NEVER,
                         Unl_Comm_Model_Queue::STATUS_PAUSE))
            ) {
                $this->_redirect('*/*');
                return;
            }

            if ($queue->getQueueStatus() == Unl_Comm_Model_Queue::STATUS_NEVER) {
                $queue->setQueueStartAtByString($this->getRequest()->getParam('start_at'));
            }

            $queue->setMessageType($this->getRequest()->getParam('type'))
                ->setMessageSubject($this->getRequest()->getParam('subject'))
                ->setMessageSenderName($this->getRequest()->getParam('sender_name'))
                ->setMessageSenderEmail($this->getRequest()->getParam('sender_email'))
                ->setMessageText($this->getRequest()->getParam('text'))
                ->setMessageStyles($this->getRequest()->getParam('styles'));

            if ($queue->getQueueStatus() == Unl_Comm_Model_Queue::STATUS_PAUSE
                && $this->getRequest()->getParam('_resume', false)) {
                $queue->setQueueStatus(Unl_Comm_Model_Queue::STATUS_SENDING);
            }

            $queue->save();

            if ($queue->getQueueStatus() == Unl_Comm_Model_Queue::STATUS_NEVER && $customerIds) {
                $queue->addRecipientsToQueue($customerIds);
            }

            $this->_redirect('*/*');
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_redirect('*/*/edit', array('id' => $id));
            } else {
                $this->_redirect('adminhtml/customer');
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/commqueue');
    }
}
