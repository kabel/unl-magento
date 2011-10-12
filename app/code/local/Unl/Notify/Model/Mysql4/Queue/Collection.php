<?php

class Unl_Notify_Model_Mysql4_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_notify/queue');
    }
}
