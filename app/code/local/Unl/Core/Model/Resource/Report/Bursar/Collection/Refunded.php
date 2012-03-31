<?php

class Unl_Core_Model_Resource_Report_Bursar_Collection_Refunded extends Unl_Core_Model_Resource_Report_Bursar_Collection_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/creditmemo', 'entity_id');
        $this->setConnection($this->getResource()->getReadConnection());
        $this->_mainItemTable = $this->getTable('sales/creditmemo_item');
        $this->_periodColumn = 'refunded_at';
    }
}
