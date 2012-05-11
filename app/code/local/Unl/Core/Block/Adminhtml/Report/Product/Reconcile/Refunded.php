<?php

class Unl_Core_Block_Adminhtml_Report_Product_Reconcile_Refunded extends Unl_Core_Block_Adminhtml_Report_Product_Reconcile_Paid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productReconcileRefundedGrid');
        $this->_collectionClassName = 'unl_core/report_product_reconcile_refunded';
    }

    public function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->getColumn('paid_date')->setData('header', Mage::helper('sales')->__('Refunded On'));
        return $this;
    }

    protected function _getCsvUrl()
    {
        return '*/*/exportReconcileRefundedCsv';
    }

    protected function _getExcelUrl()
    {
        return '*/*/exportReconcileRefundedExcel';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/reconcileRefundedGrid', array('_current'=>true));
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/sales_creditmemo/view', array('creditmemo_id' => $item->getParentId()));
    }
}
