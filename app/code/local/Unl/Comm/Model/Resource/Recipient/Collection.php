<?php

class Unl_Comm_Model_Resource_Recipient_Collection extends Mage_Customer_Model_Resource_Customer_Collection
{
    protected function _construct()
    {
        parent::_construct();
        $this->addAttributeToSelect('*');
    }

	/**
     * Queue joined flag
     *
     * @var boolean
     */
    protected $_queueJoinedFlag = false;

    public function useQueue(Unl_Comm_Model_Queue $queue)
    {
        $this->getSelect()->join(array('link' => $this->getTable('unl_comm/queue_link')),
                'link.customer_id = e.entity_id', array('queue_sent_at' => 'sent_at')
            )
            ->where($this->_getConditionSql('link.queue_id', $queue->getId()));
        $this->_queueJoinedFlag = true;
        return $this;
    }

    /**
     * Set using of links to only unsendet letter subscribers.
     */
    public function useOnlyUnsent()
    {
        if($this->_queueJoinedFlag) {
            $this->getSelect()->where($this->_getConditionSql('link.sent_at', array('null' => true)));
        }

        return $this;
    }
}
