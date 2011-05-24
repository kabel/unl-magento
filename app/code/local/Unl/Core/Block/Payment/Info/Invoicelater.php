<?php

class Unl_Core_Block_Payment_Info_Invoicelater extends Mage_Payment_Block_Info
{
    protected $_remitTo;

    public function getRemitTo()
    {
        if (is_null($this->_remitTo)) {
            $this->_convertAdditionalData();
        }
        return $this->_remitTo;
    }

    protected function _convertAdditionalData()
    {
        $details = @unserialize($this->getInfo()->getAdditionalData());
        if (is_array($details)) {
            $this->_remitTo = isset($details['remit_to']) ? (string) $details['remit_to'] : '';
        } else {
            $this->_remitTo = '';
        }
        return $this;
    }

    public function toPdf()
    {
        $this->setTemplate('payment/info/pdf/invoicelater.phtml');
        return $this->toHtml();
    }
}
