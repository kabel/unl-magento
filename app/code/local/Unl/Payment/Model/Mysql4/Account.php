<?php

class Unl_Payment_Model_Mysql4_Account extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_payment/account', 'account_id');
    }
}
