<?php

class Unl_AdminLog_Block_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
    * Modify header & button labels
    *
    */
    public function __construct()
    {
        $this->_blockGroup = 'unl_adminlog';
        $this->_controller = 'log';
        $this->_headerText = Mage::helper('unl_customertag')->__('View Admin Log');
        parent::__construct();
        $this->_removeButton('add');
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass() {
        return 'icon-head head-admin-log';
    }
}
