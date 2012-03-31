<?php

class Unl_Core_Adminhtml_Report_Sales_ReconcileController extends Unl_Core_Controller_Report_Sales
{
    protected function _construct()
    {
        $this->_controllerGroup = 'reconcile';
        $this->_controllerTitle = 'Reconcile';
    }
}
