<?php

class Unl_Comm_Model_Mysql4_Recipient_Collection extends Mage_Customer_Model_Entity_Customer_Collection
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
        $this->getSelect()->join(array('link' => $this->getTable('unl_comm/queue_link')), 'link.customer_id = e.entity_id', array('queue_sent_at' => 'sent_at'))
            ->where("link.queue_id = ?", $queue->getId());
        $this->_queueJoinedFlag = true;
        return $this;
    }

    /**
     * Set using of links to only unsendet letter subscribers.
     */
    public function useOnlyUnsent()
    {
        if($this->_queueJoinedFlag) {
            $this->getSelect()->where("link.sent_at IS NULL");
        }

        return $this;
    }
}
