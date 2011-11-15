<?php

class Unl_Payment_Model_Mysql4_Account_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_payment/account');
    }

    public function addScopeFilter($scope)
    {
        if (!empty($scope)) {
            $this->addFieldToFilter('group_id', array('in' => $scope));
        }

        return $this;
    }
}
