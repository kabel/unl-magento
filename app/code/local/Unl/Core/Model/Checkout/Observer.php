<?php

class Unl_Core_Model_Checkout_Observer
{
    /**
     * A <i>frontend</i> event observer for the <code>controller_action_predispatch_checkout_onepage_saveBilling</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onPredispatchOnepageSaveBilling($observer)
    {
        /* @var $action Mage_Checkout_OnepageController */
        $action = $observer->getEvent()->getControllerAction();
        $session = Mage::getSingleton('checkout/session');
        $checkoutSessionQuote = $session->getQuote();

        if ($action->getRequest()->isPost()) {
            $data = $action->getRequest()->getPost('billing', array());

            if (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 2) {
                $data['use_for_shipping'] = 1;
                $action->getRequest()->setPost('billing', $data);
                $session->setIsPickupFlow(true);
            } else {
                $session->setIsPickupFlow(false);
            }
        }
    }
}
