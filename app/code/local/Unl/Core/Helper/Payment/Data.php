<?php

class Unl_Core_Helper_Payment_Data extends Mage_Payment_Helper_Data
{
    /* Overrides
     * @see Mage_Payment_Helper_Data::getAllBillingAgreementMethods()
     * by checking to see that the method model class is valid
     */
    public function getAllBillingAgreementMethods()
    {
        $result = array();
        $interface = 'Mage_Payment_Model_Billing_Agreement_MethodInterface';
        foreach ($this->getPaymentMethods() as $code => $data) {
            if (!isset($data['model'])) {
                continue;
            }
            $method = Mage::app()->getConfig()->getModelClassName($data['model']);
            if ($method && in_array($interface, class_implements($method))) {
                $result[$code] = $data['title'];
            }
        }
        return $result;
    }
}