<?php

class Unl_Core_Model_Adminhtml_Externalid_Save extends Varien_Object
{
    protected $_saved = false;
    
    public function save()
    {
        $order = Mage::getModel('sales/order');
        $order->load($this->getOrderId());
        
        $extId = $this->getExternalId();
        if ($extId) {
            $order->setExternalId($extId)
                ->save();
            $this->_saved = true;
        } elseif ($order->getExternalId()) {
            $order->setExternalId('')
                ->save();
        }
        
        return $this;
    }
    
    public function getSaved()
    {
        return $this->_saved;
    }
}