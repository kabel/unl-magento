<?php

class Unl_Inventory_Model_Admin_Observer
{
    public function controllerActionPredispatch($observer)
    {
        $storeIdActions = array(
            'unl_inventory_report_valuation',
        );

        $controller = $observer->getEvent()->getControllerAction();
        if (in_array($controller->getFullActionName(), $storeIdActions)) {
            $this->_setStoreParamFromUser('store');
            return;
        }
    }

    protected function _setStoreParamFromUser($param) {
        $user  = Mage::getSingleton('admin/session')->getUser();
        $request = Mage::app()->getRequest();

        if (!is_null($user->getScope())) {
            $scope = explode(',', $user->getScope());
            if ($store = $request->getParam($param)) {
                if (!in_array($store, $scope)) {
                    $request->setParam($param, current($scope));
                }
            } else {
                $request->setParam($param, current($scope));
            }
        }
    }
}