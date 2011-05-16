<?php

class Unl_Core_Block_Adminhtml_Notification_Maintenance extends Mage_Adminhtml_Block_Template
{
    /**
     * Check if the maintenance file exists (from index.php)
     *
     * @return boolean
     */
    protected function _canShowNotification()
    {
        global $maintenanceFile;
        return !empty($maintenanceFile) && file_exists($maintenanceFile);
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
