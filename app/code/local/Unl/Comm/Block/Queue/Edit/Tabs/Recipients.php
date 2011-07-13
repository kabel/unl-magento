<?php

class Unl_Comm_Block_Queue_Edit_Tabs_Recipients
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('queue_recipients');
        $this->setUseAjax(true);

        $this->setEmptyText(Mage::helper('unl_comm')->__('No Recipients Found'));
    }

    /**
     * Retrieve current Comm Queue Object
     *
     * @return Unl_Comm_Model_Queue
     */
    public function getQueue()
    {
        return Mage::registry('current_queue');
    }

    public function getCustomerIds()
    {
        return $this->getQueue()->getCustomerIds();
    }

    protected function _prepareCollection()
    {
        if ($this->getQueue()->getId()) {
            $collection = Mage::getResourceModel('unl_comm/recipient_collection')
                ->useQueue($this->getQueue());
        } else {
            $collection = Mage::getResourceModel('customer/customer_collection')
                ->addFieldToFilter('entity_id', array('in' => $this->getCustomerIds()));
        }

        $collection->addNameToSelect();
        $collection->addAttributeToSelect('email');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        if ($this->getQueue()->getId()) {
            $this->addColumn('letter_sent_at', array(
                'header'    =>  Mage::helper('unl_comm')->__('Message Received'),
                'type'      =>  'datetime',
                'align'     =>  'center',
                'index'     =>  'queue_sent_at',
                'gmtoffset' => true,
                'default'   =>  ' ---- '
            ));
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/recipients', array('_current' => true));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('unl_comm')->__('Recipients');
    }

    public function getTabTitle()
    {
        return Mage::helper('unl_comm')->__('Queue Recipients');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
