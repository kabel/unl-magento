<?php

class Unl_Core_Model_Shipping_Carrier_Pickup extends Mage_Shipping_Model_Carrier_Pickup
{    
    /**
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $result = Mage::getModel('shipping/rate_result');
        
        $sourceStore = $this->_getSingleStoreFromItems($request->getAllItems());
        if (!$sourceStore) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('pickup');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage(Mage::helper('shipping')->__('All items must be from the same store to use this method'));
            $result->append($error);
            return $result;
        }
        
        // Don't show it if the store has no address to display
        $pickup = Mage::getStoreConfig('carriers/'.$this->_code.'/pickupaddress', $sourceStore);
        if (empty($pickup)) {
            return false;
        }
        
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier('pickup');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('store');
        $method->setMethodTitle($this->getConfigData('name'));
        
        $method->setPrice('0.00');
        $method->setCost('0.00');
        
        $method->setMethodDescription($pickup);

        $result->append($method);

        return $result;
    }
    
    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('pickup'=>$this->getConfigData('name'));
    }
    
    public function isAvailable($items)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $sourceStore = $this->_getSingleStoreFromItems($items);
        if (!$sourceStore) {
            return false;
        }
        
        $pickup = Mage::getStoreConfig('carriers/'.$this->_code.'/pickupaddress', $sourceStore);
        if (empty($pickup)) {
            return false;
        }
        
        return true;
    }
    
    protected function _getSingleStoreFromItems($items)
    {
        $sourceStore = null;
        $c = count($items);
        $i = 0;
        while ($i < $c) {
            ++$i;
            if ($items[$i-1]->getProduct()->isVirtual() || $items[$i-1]->getParentItem()) {
                continue;
            } else {
                $sourceStore = $items[$i-1]->getSourceStoreView();
                break;
            }
        }
        
        for ($i; $i < $c; $i++) {
            if ($items[$i]->getProduct()->isVirtual() || $items[$i]->getParentItem()) {
                continue;
            }
            if ($items[$i]->getSourceStoreView() != $sourceStore) {
                return false;
            }
        }
        
        return $sourceStore;
    }
}