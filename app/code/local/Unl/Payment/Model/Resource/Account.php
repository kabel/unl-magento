<?php

class Unl_Payment_Model_Resource_Account extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_payment/account', 'account_id');
    }
}
