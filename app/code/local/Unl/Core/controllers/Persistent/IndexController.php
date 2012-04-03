<?php

require_once 'Mage/Persistent/controllers/IndexController.php';

class Unl_Core_Persistent_IndexController extends Mage_Persistent_IndexController
{
    /* Overrides
     * @see Mage_Persistent_IndexController::saveMethodAction()
     * by using AJAX logic
     */
    public function saveMethodAction()
    {
        if ($this->getRequest()->isPost()) {
            if ($this->_getHelper()->isPersistent()) {
                $this->_getHelper()->getSession()->removePersistentCookie();
                /** @var $customerSession Mage_Customer_Model_Session */
                $customerSession = Mage::getSingleton('customer/session');
                if (!$customerSession->isLoggedIn()) {
                    $customerSession->setCustomerId(null)
                        ->setCustomerGroupId(null);
                }

                Mage::getSingleton('persistent/observer')->setQuoteGuest();

            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array()));
        }
    }
}
