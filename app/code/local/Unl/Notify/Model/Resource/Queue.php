<?php

class Unl_Notify_Model_Resource_Queue extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_notify/order_queue', 'queue_id');
    }
}
