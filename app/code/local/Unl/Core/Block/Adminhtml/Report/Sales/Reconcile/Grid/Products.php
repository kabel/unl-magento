<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Products
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products
{
    protected function _prepareColumns()
    {
        Mage::helper('unl_core/report_bursar')->addReconcileColumns($this);

        return parent::_prepareColumns();
    }
}
