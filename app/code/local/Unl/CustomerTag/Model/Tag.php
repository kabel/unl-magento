<?php

class Unl_CustomerTag_Model_Tag extends Mage_Core_Model_Abstract
{
    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'customertag';

    protected function _construct()
	{
		$this->_init('unl_customertag/tag');
	}

	/**
     * Retrieve Linked Customer Ids
     *
     * @return array
     */
    public function getLinkedCustomerIds()
    {
        $ids = $this->getData('customer_ids');
        if (is_null($ids)) {
            $ids = $this->_getResource()->getCustomerIds($this);
            $this->setCustomerIds($ids);
        }
        return $ids;
    }

	public function addCustomerLinks($customerIds)
	{
	    $this->setAddedCustomerIds($customerIds);
	    $this->_getResource()->addCustomerLinks($this);
	}

	protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }
}
