<?php

class Unl_Comm_Block_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('queueGrid');
        $this->setDefaultSort('start_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_comm/queue_collection')
            ->orderByStartAtNullFirst()
            ->addRecipientInfo();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('queue_id', array(
            'header'    =>  Mage::helper('unl_comm')->__('ID'),
            'index'     =>	'queue_id',
            'width'		=>	10
        ));

        $this->addColumn('start_at', array(
            'header'    =>  Mage::helper('unl_comm')->__('Queue Start'),
            'type'      =>	'datetime',
            'index'     =>	'queue_start_at',
            'gmtoffset' => true,
            'default'	=> 	' ---- '
        ));

        $this->addColumn('finish_at', array(
            'header'    =>  Mage::helper('unl_comm')->__('Queue Finish'),
            'type'      => 	'datetime',
            'index'     =>	'queue_finish_at',
            'gmtoffset' => true,
            'default'	=> 	' ---- '
        ));

        $this->addColumn('message_subject', array(
            'header'    =>  Mage::helper('unl_comm')->__('Subject'),
            'index'     =>  'message_subject'
        ));

         $this->addColumn('status', array(
            'header'    => Mage::helper('unl_comm')->__('Status'),
            'index'		=> 'queue_status',
            'type'      => 'options',
            'options'   => array(
                Unl_Comm_Model_Queue::STATUS_SENT 	=> Mage::helper('unl_comm')->__('Sent'),
                Unl_Comm_Model_Queue::STATUS_CANCEL	=> Mage::helper('unl_comm')->__('Cancelled'),
                Unl_Comm_Model_Queue::STATUS_NEVER 	=> Mage::helper('unl_comm')->__('Not Sent'),
                Unl_Comm_Model_Queue::STATUS_SENDING => Mage::helper('unl_comm')->__('Sending'),
                Unl_Comm_Model_Queue::STATUS_PAUSE 	=> Mage::helper('unl_comm')->__('Paused'),
            ),
            'width'     => '100px',
        ));

        $this->addColumn('recipients_sent', array(
            'header'    =>  Mage::helper('unl_comm')->__('Processed'),
            'type'		=> 'number',
            'index'		=> 'recipients_sent'
        ));

        $this->addColumn('recipients_total', array(
            'header'    =>  Mage::helper('unl_comm')->__('Recipients'),
            'type'		=> 'number',
            'index'		=> 'recipients_total'
        ));

        $this->addColumn('action', array(
            'header'    =>  Mage::helper('unl_comm')->__('Action'),
            'filter'	=>	false,
            'sortable'	=>	false,
            'no_link'   => true,
            'width'		=> '100px',
            'renderer'	=>	'unl_comm/queue_grid_renderer_action'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
