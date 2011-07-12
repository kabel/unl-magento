<?php

class Unl_AdminLog_Block_Archive extends Unl_AdminLog_Block_Log
{
    /**
    * Modify header & button labels
    *
    */
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'archive';
        $this->_headerText = Mage::helper('unl_customertag')->__('View Admin Log Archive');
    }
}
