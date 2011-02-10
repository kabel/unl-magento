<?php

class Unl_Core_Model_Payment_Method_Checkmo extends Mage_Payment_Model_Method_Checkmo
{
    public function getAllowForcePay()
    {
        return true;
    }

    /**
     * Check whether method is available
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $this->setQuote($quote);

        if (parent::isAvailable($quote)) {
            if (!empty($quote)) {
                if ($store = Mage::helper('unl_core')->getSingleStoreFromQuote($quote)) {
                    return true;
                }
            }

            return false;
        }
    }

    protected function _getQuote()
    {
        $quote = $this->getQuote();
        if (!$quote) {
            if (Mage::app()->getStore()->isAdmin()) {
                $session = Mage::getSingleton('adminhtml/session_quote');
            } else {
                $session = Mage::getSingleton('checkout/session');
            }

            $quote = $session->getQuote();
            $this->setQuote($quote);
        }

        return $quote;
    }

    public function getPayableTo()
    {
        $store = Mage::helper('unl_core')->getSingleStoreFromQuote($this->_getQuote());

        return $this->getConfigData('payable_to', (!$store) ? null : $store);
    }

    public function getMailingAddress()
    {
        $store = Mage::helper('unl_core')->getSingleStoreFromQuote($this->_getQuote());

        return $this->getConfigData('mailing_address', (!$store) ? null : $store);
    }
}
