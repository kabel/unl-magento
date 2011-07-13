<?php

class Unl_Comm_Block_Queue_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('comm_queue_tabs');
        $this->setDestElementId('queue_edit_form');
        $this->setTitle(Mage::helper('unl_comm')->__('Message Information'));
    }

    public function getQueue()
    {
        return Mage::registry('current_queue');
    }
}
