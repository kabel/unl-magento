<?php

class Unl_Core_Block_Adminhtml_Notification_Maintenance extends Mage_Adminhtml_Block_Template
{
    /**
     * Check if the maintenance file exists
     *
     * @return boolean
     */
    protected function _canShowNotification()
    {
        return Mage::helper('backup')->isMaintenanceOn();
    }

	/**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_canShowNotification()) {
            return '';
        }
        return parent::_toHtml();
    }
}
