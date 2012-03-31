<?php

class Unl_Core_Model_Payment_Method_Checkmo extends Mage_Payment_Model_Method_Checkmo
{
    public function getAllowForcePay()
    {
        return true;
    }

    /* Extends
     * @see Mage_Payment_Model_Method_Abstract::isAvailable()
     * by checking the mailing address of all stores
     */
    public function isAvailable($quote = null)
    {
        $this->setQuote($quote);

        if (parent::isAvailable($quote)) {
            if (!empty($quote)) {
                return $this->_validateMailingAddress($quote);
            }

            return false;
        }
    }

    /**
     * Get the quote from data storage or attempt to pull from session
     *
     * @return Mage_Sales_Model_Quote
     */
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

    /**
     * Checks if all stores in the quote use the same mailing address
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    protected function _validateMailingAddress($quote)
    {
        $stores = Mage::helper('unl_core')->getStoresFromQuote($quote);

        if (empty($stores)) {
            return false;
        }

        $store = array_shift($stores);
        $address = $this->getConfigData('mailing_address', $store);

        foreach ($stores as $store) {
            if ($this->getConfigData('mailing_address', $store) != $address) {
                return false;
            }
        }

        return true;
    }

    public function getPayableTo()
    {
        $stores = Mage::helper('unl_core')->getStoresFromQuote($this->_getQuote());
        $store = empty($stores) ? null : current($stores);

        return $this->getConfigData('payable_to', $store);
    }

    public function getMailingAddress()
    {
        $stores = Mage::helper('unl_core')->getStoresFromQuote($this->_getQuote());
        $store = empty($stores) ? null : current($stores);

        return $this->getConfigData('mailing_address', $store);
    }
}
