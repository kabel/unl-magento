<?php

class Unl_Comm_Block_Queue extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('unl/comm/queue/list.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('unl_comm/queue_grid', 'unl_comm.queue.grid'));
        return parent::_beforeToHtml();
    }
}