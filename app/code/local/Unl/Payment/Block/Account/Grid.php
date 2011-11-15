<?php

class Unl_Payment_Block_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('paymentAccountGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('unl_payment/account')->getCollection();

        $collection->addScopeFilter(Mage::helper('unl_core')->getAdminUserScope(true));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('account_id', array(
            'header' => Mage::helper('unl_payment')->__('ID'),
            'width' => '50px',
            'align' => 'right',
            'index' => 'account_id',
        ));

        $this->addColumn('merchant', array(
            'header' => Mage::helper('unl_payment')->__('Merchant'),
            'type' => 'options',
            'options' => Mage::getModel('unl_core/store_source_filter_group')->toOptionArray(),
            'index' => 'group_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('unl_payment')->__('Name'),
            'index' => 'name',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id' => $row->getId()
        ));
    }
}
