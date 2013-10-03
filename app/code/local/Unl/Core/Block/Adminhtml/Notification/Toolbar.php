<?php

class Unl_Core_Block_Adminhtml_Notification_Toolbar extends Mage_Adminhtml_Block_Notification_Toolbar
{
    public function isShow()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('admin/system/adminnotification/show_toolbar')) {
            return false;
        }

        return parent::isShow();
    }
}
