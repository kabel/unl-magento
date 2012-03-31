<?php

require_once 'Mage/Connect/controllers/Adminhtml/Extension/CustomController.php';

class Unl_Core_Adminhtml_Extension_CustomController extends Mage_Connect_Adminhtml_Extension_CustomController
{
    /* Overrides
     * @see Mage_Connect_Adminhtml_Extension_CustomController::indexAction()
     * by removing the maditory load of store w/ id=1
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Magento Connect'))
             ->_title($this->__('Package Extensions'));

        $this->_forward('edit');
    }
}
