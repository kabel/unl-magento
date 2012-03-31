<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Shipping
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping
{
    protected function _prepareColumns()
    {
        Mage::helper('unl_core/report_bursar')->addReconcileColumns($this, true);

        parent::_prepareColumns();

        $this->removeColumn('orders_count');

        return $this;
    }
}
