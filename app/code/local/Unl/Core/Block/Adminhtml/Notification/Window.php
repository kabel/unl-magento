<?php

class Unl_Core_Block_Adminhtml_Notification_Window extends Mage_Adminhtml_Block_Notification_Window
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/adminnotification/show_toolbar');
    }
}
