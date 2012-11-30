<?php

class Unl_Spam_Block_Adminhtml_Quarantine_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('spamQuarantineGrid');
        $this->setDefaultSort('expires_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('unl_spam/quarantine')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('remote_addr', array(
            'header' => Mage::helper('unl_spam')->__('IP Address'),
            'filter' => 'unl_spam/adminhtml_widget_grid_column_filter_cidr',
            'index' => 'remote_addr',
        ));

        $this->addColumn('expires_at', array(
            'type' => 'datetime',
            'filter_time' => true,
            'header' => Mage::helper('unl_spam')->__('Expires At'),
            'index' => 'expires_at',
        ));

        $this->addColumn('strikes', array(
            'header' => Mage::helper('unl_spam')->__('Strikes'),
            'index' => 'strikes',
            'type'  => 'number',
            'width' => '200px'
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('quarantine_id');
        $this->getMassactionBlock()->setFormFieldName('quarantine_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('expire', array(
            'label'=> Mage::helper('unl_spam')->__('Expire Now'),
            'url'  => $this->getUrl('*/*/massexpire'),
        ));

        $this->getMassactionBlock()->addItem('blacklist', array(
            'label'=> Mage::helper('unl_spam')->__('Add to Blacklist'),
            'url'  => $this->getUrl('*/*/massblacklist'),
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }
}
