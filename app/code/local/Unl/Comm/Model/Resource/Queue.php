<?php

class Unl_Comm_Model_Resource_Queue extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_comm/queue', 'queue_id');
    }

    /**
     * Add customers to queue
     *
     * @param Unl_Comm_Model_Queue $queue
     * @param array $cusotmerIds
     */
    public function addRecipients(Unl_Comm_Model_Queue $queue, array $customerIds)
    {
        if (count($customerIds) == 0) {
            Mage::throwException(Mage::helper('unl_comm')->__('No recipients selected.'));
        }

        if (!$queue->getId() && $queue->getQueueStatus() != Unl_Comm_Model_Queue::STATUS_NEVER) {
            Mage::throwException(Mage::helper('unl_comm')->__('Invalid queue selected.'));
        }

        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select();
        $select->from($this->getTable('queue_link'), 'customer_id')
            ->where($readAdapter->prepareSqlCondition('queue_id', $queue->getId()))
            ->where($readAdapter->prepareSqlCondition('customer_id', array('in' => $customerIds)));

        $usedIds = $readAdapter->fetchCol($select);

        $this->beginTransaction();
        try {
            foreach ($customerIds as $customerId) {
                if (in_array($customerId, $usedIds)) {
                    continue;
                }
                $data = array(
                    'queue_id' => $queue->getId(),
                    'customer_id' => $customerId
                );
                $this->_getWriteAdapter()->insert($this->getTable('queue_link'), $data);
            }

            $this->commit();
        }
        catch (Exception $e) {
            $this->rollBack();
        }
    }

    public function removeRecipientsFromQueue($queue)
    {
        $writeAdapter = $this->_getWriteAdapter();

        $this->beginTransaction();
        try {
            $writeAdapter->delete($this->getTable('queue_link'), array(
                $writeAdapter->prepareSqlCondition('queue_id', $queue->getId()),
                $writeAdapter->prepareSqlCondition('sent', array('null' => true))
            ));

            $this->commit();
        }
        catch (Exception $e) {
            $this->rollBack();
        }
    }

    public function markReceived(Unl_Comm_Model_Queue $queue, Mage_Customer_Model_Customer $customer)
    {
        $writeAdapter = $this->_getWriteAdapter();

        $this->beginTransaction();
        try {
            $data['sent_at'] = Mage::getSingleton('core/date')->gmtDate();
            $writeAdapter->update($this->getTable('queue_link'), $data, array(
                $writeAdapter->prepareSqlCondition('customer_id', $customer->getId()),
                $writeAdapter->prepareSqlCondition('queue_id', $queue->getId())
            ));

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            Mage::throwException(Mage::helper('unl_comm')->__('Cannot mark as received subscriber.'));
        }
    }
}
