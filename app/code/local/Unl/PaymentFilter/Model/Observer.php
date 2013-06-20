<?php

class Unl_PaymentFilter_Model_Observer
{
    /**
     * An event observer for the <code>payment_method_is_active</code> event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_PaymentFilter_Model_Observer
     */
    public function isPaymentMethodActive($observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        $quote  = $observer->getEvent()->getQuote();

        if (!$result->isAvailable || !$quote) {
            return $this;
        }

        /* @var $method Mage_Payment_Model_Method_Abstract */
        /* @var $quote Mage_Sales_Model_Quote */
        /* @var $item Mage_Sales_Model_Quote_Item */

        foreach ($quote->getAllItems() as $item) {
            $filter = $item->getProduct()->getPaymentFilter();
            if ($filter) {
                if (!is_array($filter)) {
                    $filter = explode(',', $filter);
                }

                if (in_array($method->getCode(), $filter)) {
                    $result->isAvailable = false;
                    break;
                }
            }
        }

        return $this;
    }
}
