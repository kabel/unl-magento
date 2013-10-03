<?php

class Unl_Core_Block_Adminhtml_Notification_Baseurl extends Mage_Adminhtml_Block_Notification_Baseurl
{
    /**
     * Returns is the admin user is allowed to edit the Base URL config
     * section.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/config/web');
    }

     /* Extends
      * @see Mage_Adminhtml_Block_Notification_Baseurl::getConfigUrl()
      * by adding an ACL check
      */
     public function getConfigUrl()
     {
         if (!$this->_isAllowed()) {
             return false;
         }

         return parent::getConfigUrl();
     }
}
