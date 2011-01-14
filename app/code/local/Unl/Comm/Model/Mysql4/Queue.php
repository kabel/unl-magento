<?php

class Unl_Comm_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_comm/queue', 'queue_id');
    }

    /**
     * Add subscribers to queue
     *
     * @param Unl_Comm_Model_Queue $queue
     * @param array $cusotmerIds
     */
    public function addRecipients(Unl_Comm_Model_Queue $queue, array $customerIds)
    {
        if (count($customerIds)==0) {
            Mage::throwException(Mage::helper('unl_comm')->__('No subscribers selected.'));
        }

        if (!$queue->getId() && $queue->getQueueStatus()!=Unl_Comm_Model_Queue::STATUS_NEVER) {
            Mage::throwException(Mage::helper('unl_comm')->__('Invalid queue selected.'));
        }

        $select = $this->_getWriteAdapter()->select();
        $select->from($this->getTable('queue_link'),'customer_id')
            ->where('queue_id = ?', $queue->getId())
            ->where('customer_id in (?)', $customerIds);

        $usedIds = $this->_getWriteAdapter()->fetchCol($select);
        $this->_getWriteAdapter()->beginTransaction();
        try {
            foreach($customerIds as $customerId) {
                if(in_array($customerId, $usedIds)) {
                    continue;
                }
                $data = array();
                $data['queue_id'] = $queue->getId();
                $data['customer_id'] = $customerId;
                $this->_getWriteAdapter()->insert($this->getTable('queue_link'), $data);
            }
            $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
        }

    }

    public function removeRecipientsFromQueue($queue)
    {
        try {
            $this->_getWriteAdapter()->delete(
                $this->getTable('queue_link'),
                array(
                    $this->_getWriteAdapter()->quoteInto('queue_id = ?', $queue->getId()),
                    'sent_at IS NULL'
                )
            );

            $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
        }
    }

    public function markReceived(Unl_Comm_Model_Queue $queue, Mage_Customer_Model_Customer $customer)
    {
        $this->_getWriteAdapter()->beginTransaction();
        try {
            $data['sent_at'] = now();
            $this->_getWriteAdapter()->update($this->getTable('queue_link'),
                $data,
                array($this->_getWriteAdapter()->quoteInto('customer_id = ?', $customer->getId()),
                    $this->_getWriteAdapter()->quoteInto('queue_id = ?', $queue->getId())
                )
            );
            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollback();
            Mage::throwException(Mage::helper('unl_comm')->__('Cannot mark as received subscriber.'));
        }
    }
}