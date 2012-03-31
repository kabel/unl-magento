<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Abstract
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Abstract
{
    public function __construct()
    {
        $this->_controllerGroup = 'reconcile';
        $this->_controllerTitle = 'Reconcile Report';
        parent::__construct();
    }

    protected function _isAllowedShipping()
    {
        $scope = Mage::helper('unl_core')->getAdminUserScope();
        $storeIds = $this->getRequest()->getParam('store_ids');

        return empty($scope) && empty($storeIds);
    }
}
