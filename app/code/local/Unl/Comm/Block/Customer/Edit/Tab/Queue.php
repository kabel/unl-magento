<?php

class Unl_Comm_Block_Customer_Edit_Tab_Queue extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('commqueueGrid');
        $this->setDefaultSort('start_at');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(Mage::helper('unl_comm')->__('No Messages Found'));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/customer_queue/customerGrid', array('_current'=>true));
    }

    /**
     * Retrieve current customer object
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        return Mage::registry('current_customer');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_comm/queue_collection')
            ->addRecipientFilter($this->_getCustomer()->getId());

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
            'align'     =>  'center',
            'index'     =>	'queue_start_at',
            'gmtoffset' => true,
            'default'	=> 	' ---- '
        ));

        $this->addColumn('finish_at', array(
            'header'    =>  Mage::helper('unl_comm')->__('Queue Finish'),
            'type'      => 	'datetime',
        	'align'     =>  'center',
            'index'     =>	'queue_finish_at',
            'gmtoffset' => true,
            'default'	=> 	' ---- '
        ));

        $this->addColumn('message_subject', array(
            'header'    =>  Mage::helper('unl_comm')->__('Subject'),
            'index'     =>  'message_subject'
        ));

        $this->addColumn('letter_sent_at', array(
            'header'    =>  Mage::helper('unl_comm')->__('Message Received'),
            'type'      =>  'datetime',
            'align'     =>  'center',
            'index'     =>  'sent_at',
            'gmtoffset' => true,
            'default'   =>  ' ---- '
        ));

        $this->addColumn('message_subject', array(
            'header'    =>  Mage::helper('unl_comm')->__('Subject'),
            'align'     =>  'center',
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

        $this->addColumn('action', array(
            'header'    =>  Mage::helper('unl_comm')->__('Action'),
            'align'     =>  'center',
            'filter'    =>  false,
            'sortable'  =>  false,
            'getter'    => 'getId',
            'type'      => 'action',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('unl_comm')->__('View'),
                    'popup'   => true,
                    'url'     => array(
                    	'base' => 'unl_comm/queue/preview',
                    	'params'  => array('customer' => $this->_getCustomer()->getId())
                    ),
                    'field'   => 'id'
                )
            )
        ));

        return parent::_prepareColumns();
    }
}
