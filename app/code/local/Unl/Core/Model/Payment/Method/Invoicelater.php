<?php

class Unl_Core_Model_Payment_Method_Invoicelater extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Payment code name
     *
     * @var string
     */
    protected $_code = 'invoicelater';

    protected $_infoBlockType = 'unl_core/payment_info_invoicelater';

    public function getAllowForcePay()
    {
        return true;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Unl_Core_Model_Payment_Method_Invoicelater
     */
    public function assignData($data)
    {
        $details = array();
        if ($this->getRemitTo()) {
            $details['remit_to'] = $this->getRemitTo();
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    public function getRemitTo()
    {
        return $this->getConfigData('remit_to');
    }
}
