<?php

class Unl_Ship_Model_System_Config_Backend_Endicia_Observer
{
    public function afterEndiciaChanges($observer)
    {
        $data = Mage::registry('endicia_old_passphrase');
        if (!is_null($data)) {
            $this->_changePassPhrase($data);
        }

        $data = Mage::registry('endicia_force_purchase');
        if (!is_null($data)) {
            $this->_purchasePostage();
        }
    }

    protected function _changePassPhrase($oldPassPhrase)
    {
        $endicia = Mage::getSingleton('unl_ship/shipping_carrier_usps_endicia');

        try {
            $endicia->requestChangePassPhrase($oldPassPhrase, $endicia->getCarrier()->getConfigData('endicia_passphrase'));

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('unl_ship')->__('The Endicia pass phrase has been changed.'));
        } catch (Exception $e) {
            throw new Exception(Mage::helper('unl_ship')->__('Unable to change pass phrase with Endicia: ' . $e->getMessage()));
        }
    }

    protected function _purchasePostage()
    {
        $endicia = Mage::getSingleton('unl_ship/shipping_carrier_usps_endicia');

        try {
            $result = $endicia->requestBuyPostage(true);

            if ($result) {
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('unl_ship')->__('Successfully purchased postage from Endicia.'));
            }
        } catch (Exception $e) {
            throw new Exception(Mage::helper('unl_ship')->__('Unable to by postage from Endicia: ' . $e->getMessage()));
        }
    }
}
