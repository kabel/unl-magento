<?php

class Unl_Core_Model_Resource_Report_Bursar_Collection_Paid extends Unl_Core_Model_Resource_Report_Bursar_Collection_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/invoice', 'entity_id');
        $this->setConnection($this->getResource()->getReadConnection());
        $this->_mainItemTable = $this->getTable('sales/invoice_item');
        $this->_periodColumn = 'paid_at';
    }
}
