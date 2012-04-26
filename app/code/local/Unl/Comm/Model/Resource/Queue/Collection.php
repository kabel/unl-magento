<?php

class Unl_Comm_Model_Resource_Queue_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected $_addRecipientsFlag = false;
	protected $_recipientsFilters = array();

	/**
     * Initializes collection
     */
    protected function _construct()
    {
        $this->_map['fields']['queue_id'] = 'main_table.queue_id';
        $this->_init('unl_comm/queue');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('queue_id', 'message_subject');
    }

    public function load($printQuery=false, $logQuery=false)
    {
        if($this->_addRecipientsFlag && !$this->isLoaded()) {
            $this->_addRecipientInfoToSelect();
        }
        return parent::load($printQuery, $logQuery);
    }

    /**
     * Set filter for queue by customer.
     *
     * @param     int        $customerId
     * @return    Unl_Comm_Model_Resource_Queue_Collection
     */
    public function addRecipientFilter($customerId)
    {
        $this->getSelect()
            ->join(array('link' => $this->getTable('queue_link')),
                'main_table.queue_id=link.queue_id',
                array('sent_at')
            )
            ->where($this->_getConditionSql('link.customer_id', $customerId));

        return $this;
    }

    public function addRecipientInfo()
    {
        $this->_addRecipientsFlag = true;

        return $this;
    }

    protected function _addRecipientInfoToSelect()
    {
        $this->_addRecipientsFlag = true;
        $select = $this->getConnection()->select()
            ->from(array('link' => $this->getTable('queue_link')), array(
            	'queue_id',
            	'recipients_total' => 'COUNT(queue_link_id)',
            	'recipients_sent'  => 'COUNT(sent_at)'
            ))
            ->group('queue_id');

        foreach (array('recipients_total', 'recipients_sent') as $field) {
            if (isset($this->_recipientsFilters[$field])) {
                $this->getSelect()->where($this->_getConditionSql('lt.'.$field, $this->_recipientsFilters[$field]));
            }
        }

        $this->getSelect()->joinLeft(array('lt' => $select), 'main_table.queue_id = lt.queue_id',
            array('recipients_total', 'recipients_sent'));

        return $this;
    }

    public function addFieldToFilter($field, $condition=null)
    {
        if (in_array($field, array('recipients_total', 'recipients_sent'))) {
            $this->_recipientsFilters[$field] = $condition;
            return $this;
        } else {
            return parent::addFieldToFilter($field, $condition);
        }
    }

    /**
     * Add filter by only ready fot sending item
     *
     * @return Unl_Comm_Model_Resource_Queue_Collection
     */
    public function addOnlyForSendingFilter()
    {
        $this->getSelect()
            ->where($this->_getConditionSql('main_table.queue_status', array('in' => array(
                Unl_Comm_Model_Queue::STATUS_SENDING,
                Unl_Comm_Model_Queue::STATUS_NEVER
            ))))
            ->where($this->_getConditionSql('main_table.queue_start_at', array('to' => Mage::getSingleton('core/date')->gmtdate())))
            ->where($this->_getConditionSql('main_table.queue_start_at', array('notnull' => true)));

        return $this;
    }
}
