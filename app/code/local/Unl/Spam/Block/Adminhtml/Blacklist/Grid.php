<?php

class Unl_Spam_Block_Adminhtml_Blacklist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('spamQuarantineGrid');
        $this->setDefaultSort('last_seen');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /* @var $collection Unl_Spam_Model_Resource_Blacklist_Collection */
        $collection = Mage::getModel('unl_spam/blacklist')->getCollection();

        $collection->addExpressionFieldToSelect('mask_length', 'NULLIF(BIT_COUNT({{cidr_mask}}), 0)', 'cidr_mask');

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

        $this->addColumn('cidr_mask', array(
            'header' => Mage::helper('unl_spam')->__('CIDR Mask Bits'),
            'type'   => 'number',
            'index'  => 'mask_length',
        ));

        $responses = Mage::getSingleton('unl_spam/source_responsetype')->toOptionHash();
        $this->addColumn('response_type', array(
            'header' => Mage::helper('unl_spam')->__('Response'),
            'type'   => 'options',
            'index'  => 'response_type',
            'options' => $responses,
            'width' => '80'
        ));

        $this->addColumn('created_at', array(
            'type' => 'datetime',
            'filter_time' => true,
            'header' => Mage::helper('unl_spam')->__('Created At'),
            'index' => 'created_at',
        ));

        $this->addColumn('last_seen', array(
            'type' => 'datetime',
            'filter_time' => true,
            'header' => Mage::helper('unl_spam')->__('Last Seen'),
            'index' => 'last_seen',
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
            'label'=> Mage::helper('unl_spam')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
        ));

        $responses = Mage::getSingleton('unl_spam/source_responsetype')->toOptionArray();
        array_unshift($responses, array('label'=>'', 'value'=>''));

        $this->getMassactionBlock()->addItem('response', array(
            'label'=> Mage::helper('unl_spam')->__('Change response'),
            'url'  => $this->getUrl('*/*/massRespUpdate', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'response',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('unl_spam')->__('Response'),
                    'values' => $responses
                )
            )
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
